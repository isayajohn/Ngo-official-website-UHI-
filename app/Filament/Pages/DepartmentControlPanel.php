<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\DepartmentTask;
use App\Models\Project;
use App\Models\TeamMember;
use App\Services\DepartmentAccessService;
use Filament\Pages\Page;

class DepartmentControlPanel extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static ?string $navigationLabel = 'Department Control Panel';
    protected static ?string $title = 'Department Control Panel';
    protected string $view = 'filament.pages.department-control-panel';

    public function mount(): void
    {
        $department = request()->query('department');
        if ($department) abort_unless(app(DepartmentAccessService::class)->canAccessDepartment(auth()->user(), Department::findOrFail($department)), 403);
        elseif (! auth()->user()->can('organization.full_access')) abort_unless(
            auth()->user()->departments()->wherePivot('active', true)->exists()
                || Department::whereHas('activeLeadership.teamMember', fn ($q) => $q->where('user_id', auth()->id()))->exists()
                || Department::whereHas('head', fn ($q) => $q->where('user_id', auth()->id()))->exists(),
            403,
        );
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $departments = Department::query()->where('active', true)
            ->when(! $user->can('organization.full_access'), fn ($q) => $q->where(fn ($inner) => $inner
                ->whereIn('id', $user->departments()->wherePivot('active', true)->select('departments.id'))
                ->orWhereHas('activeLeadership.teamMember', fn ($member) => $member->where('user_id', $user->id))
                ->orWhereHas('head', fn ($head) => $head->where('user_id', $user->id))))
            ->orderBy('name')->get();
        $id = request()->query('department') ?: $departments->first()?->id;
        $department = $id ? Department::with([
            'head.position',
            'activeLeadership' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('start_date'),
            'activeLeadership.teamMember.position',
        ])->findOrFail($id) : null;
        if ($department) abort_unless(app(DepartmentAccessService::class)->canAccessDepartment($user, $department), 403);
        $tasks = fn () => $department?->tasks();
        $projects = fn () => $department?->projects();
        $staff = $department?->teamMembers()->where('active', true)->with('position')->orderBy('display_order')->orderBy('name')->limit(8)->get() ?? collect();
        $taskWorkload = $staff->isEmpty() ? collect() : DepartmentTask::query()
            ->where('department_id', $department->id)
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->where(fn ($query) => $query->whereIn('assigned_to_team_member_id', $staff->modelKeys())->orWhereIn('assigned_to_user_id', $staff->pluck('user_id')->filter()->unique()))
            ->get(['assigned_to_team_member_id', 'assigned_to_user_id']);
        $staff->each(fn (TeamMember $member) => $member->setAttribute('open_task_count', $taskWorkload->filter(fn ($task) =>
            (int) $task->assigned_to_team_member_id === (int) $member->id
            || ($member->user_id && (int) $task->assigned_to_user_id === (int) $member->user_id)
        )->count()));

        $taskRecords = $tasks()?->get(['status', 'due_date', 'completed_at']) ?? collect();
        $tasksByStatus = $taskRecords->groupBy(fn (DepartmentTask $task) => $task->effective_status)->map->count();

        $now = now();
        $monthlyTaskCompletion = collect(range(5, 0))->mapWithKeys(function (int $monthsAgo) use ($now, $tasks): array {
            $month = $now->copy()->startOfMonth()->subMonths($monthsAgo);
            $count = $tasks()
                ?->whereNotNull('completed_at')
                ->whereBetween('completed_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count() ?? 0;

            return [$month->format('M Y') => $count];
        });

        $pendingApprovals = $department?->approvalRequests()
            ->with(['workflow', 'requestedBy'])
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])
            ->latest('submitted_at')
            ->limit(8)
            ->get() ?? collect();
        $pendingApprovals->each(fn ($approval) => $approval->setAttribute('display_title', $this->approvalTitle($approval->approvable, $approval->workflow?->name)));

        $deadlines = collect();
        if ($department) {
            $tasks()->whereNotNull('due_date')->whereDate('due_date', '>=', today())
                ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                ->get(['id', 'title', 'due_date', 'status'])
                ->each(fn (DepartmentTask $task) => $deadlines->push((object) [
                    'title' => $task->title,
                    'type' => 'Task',
                    'due_at' => $task->due_date,
                    'status' => $task->effective_status,
                ]));

            $department->projects()->with('milestones')
                ->whereHas('milestones', fn ($query) => $query->whereNotNull('due_date')->whereDate('due_date', '>=', today())->whereNotIn('status', ['COMPLETED', 'CANCELLED']))
                ->get()
                ->each(function (Project $project) use ($deadlines): void {
                    foreach ($project->milestones->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->where('due_date', '>=', today()) as $milestone) {
                        $deadlines->push((object) [
                            'title' => $milestone->title.' · '.$project->translated('title'),
                            'type' => 'Milestone',
                            'due_at' => $milestone->due_date,
                            'status' => $milestone->status,
                        ]);
                    }
                });

            $department->projects()->whereNotNull('end_date')->whereDate('end_date', '>=', today())
                ->whereIn('status', ['PLANNED', 'ACTIVE', 'ON_HOLD'])
                ->get(['title', 'end_date', 'status'])
                ->each(fn (Project $project) => $deadlines->push((object) [
                    'title' => $project->translated('title'),
                    'type' => 'Project end date',
                    'due_at' => $project->end_date,
                    'status' => $project->status->getLabel(),
                ]));
        }

        $deadlines = $deadlines->sortBy('due_at')->take(10)->values();
        $head = $department?->activeLeadership->firstWhere('leadership_role', 'HEAD')?->teamMember
            ?? $department?->activeLeadership->firstWhere('leadership_role', 'ACTING_HEAD')?->teamMember
            ?? $department?->head;

        $staffCount = $department?->teamMembers()->where('active', true)->count() ?? 0;
        $activeProjectCount = $projects()?->where('status', 'ACTIVE')->count() ?? 0;
        $openTaskCount = $tasks()?->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->count() ?? 0;
        $overdueTaskCount = $tasks()?->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->whereDate('due_date', '<', today())->count() ?? 0;
        $pendingApprovalCount = $pendingApprovals->count();

        // Reuse the same dashboard KPI component while keeping every number
        // tied to the currently authorized department.
        $departmentStats = [
            ['label' => 'Active staff', 'value' => number_format($staffCount), 'description' => 'Active team members in this department', 'icon' => 'heroicon-o-user-group', 'tone' => 'blue'],
            ['label' => 'Active projects', 'value' => number_format($activeProjectCount), 'description' => 'Lead and supporting department projects', 'icon' => 'heroicon-o-briefcase', 'tone' => 'mint'],
            ['label' => 'Open tasks', 'value' => number_format($openTaskCount), 'description' => 'Tasks still in progress or awaiting work', 'icon' => 'heroicon-o-clipboard-document-list', 'tone' => 'violet'],
            ['label' => 'Overdue tasks', 'value' => number_format($overdueTaskCount), 'description' => 'Open tasks past their due date', 'icon' => 'heroicon-o-clock', 'tone' => 'amber'],
            ['label' => 'Pending approvals', 'value' => number_format($pendingApprovalCount), 'description' => 'Requests routed to this department', 'icon' => 'heroicon-o-inbox', 'tone' => 'blue'],
        ];

        return [
            'department' => $department, 'departments' => $departments, 'head' => $head,
            'staffCount' => $staffCount,
            'activeProjects' => $activeProjectCount,
            'openTasks' => $openTaskCount,
            'overdueTasks' => $overdueTaskCount,
            'pendingApprovals' => $pendingApprovalCount,
            'departmentStats' => $departmentStats,
            'pendingApprovalItems' => $pendingApprovals,
            'upcomingDeadlines' => $deadlines,
            'staff' => $staff,
            'projects' => $projects()?->with('programme')->latest('updated_at')->limit(8)->get() ?? collect(),
            'activity' => $department?->activity()->with('user')->limit(10)->get() ?? collect(),
            'tasksByStatus' => $tasksByStatus,
            'projectsByStatus' => $projects()?->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status') ?? collect(),
            'monthlyTaskCompletion' => $monthlyTaskCompletion,
        ];
    }

    private function approvalTitle(?object $subject, ?string $fallback): string
    {
        if (! $subject) return $fallback ?: 'Approval request';
        if (method_exists($subject, 'translated')) return $subject->translated('title') ?: ($fallback ?: class_basename($subject));

        $title = data_get($subject, 'title');
        if (is_array($title)) return $title[app()->getLocale()] ?? $title['en'] ?? ($fallback ?: class_basename($subject));
        if (is_string($title) && filled($title)) return $title;

        return $fallback ?: class_basename($subject);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        if ($user->can('organization.full_access')) return true;
        if (! $user->can('department.view')) return false;

        return $user->departments()->wherePivot('active', true)->exists()
            || Department::whereHas('activeLeadership.teamMember', fn ($query) => $query->where('user_id', $user->id))->exists()
            || Department::whereHas('head', fn ($query) => $query->where('user_id', $user->id))->exists();
    }
}

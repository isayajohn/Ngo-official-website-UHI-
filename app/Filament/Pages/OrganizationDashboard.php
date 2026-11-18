<?php

namespace App\Filament\Pages;

use App\Enums\ProjectStatus;
use App\Models\ApprovalRequest;
use App\Models\Department;
use App\Models\DepartmentActivityLog;
use App\Models\DepartmentTask;
use App\Models\Project;
use App\Models\TeamMember;
use Filament\Pages\Page;

class OrganizationDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static ?string $navigationLabel = 'Organization Overview';
    protected static ?string $title = 'Organization Overview';
    protected string $view = 'filament.pages.organization-dashboard';

    protected function getViewData(): array
    {
        $departmentId = request()->query('department');
        abort_if($departmentId && ! Department::where('active', true)->whereKey($departmentId)->exists(), 404);

        $departmentOptions = Department::query()->where('active', true)->orderBy('name')->get(['id', 'name']);
        $departments = Department::query()->where('active', true)
            ->when($departmentId, fn ($query) => $query->whereKey($departmentId))
            ->with(['head', 'activeLeadership.teamMember'])
            ->withCount([
                'teamMembers as team_members_count' => fn ($query) => $query->where('active', true),
                'projects',
                'projects as active_projects_count' => fn ($query) => $query->where('status', ProjectStatus::Active),
                'tasks as open_tasks_count' => fn ($query) => $query->whereNotIn('status', ['COMPLETED', 'CANCELLED']),
                'tasks as overdue_tasks_count' => fn ($query) => $query->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->whereDate('due_date', '<', today()),
            ])
            ->orderBy('name')->get();

        $departmentIds = $departments->modelKeys();
        $activeStaffCount = TeamMember::query()->where('active', true)->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))->count();
        $activeProjectCount = Project::query()->where('status', ProjectStatus::Active)->when($departmentId, fn ($query) => $query->whereHas('departments', fn ($departments) => $departments->whereKey($departmentId)))->count();
        $openTaskCount = DepartmentTask::query()->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->whereIn('department_id', $departmentIds)->count();
        $overdueTaskCount = DepartmentTask::query()->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->whereDate('due_date', '<', today())->whereIn('department_id', $departmentIds)->count();
        $pendingApprovalCount = ApprovalRequest::query()->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])->whereIn('department_id', $departmentIds)->count();

        $tasksByStatus = DepartmentTask::query()->whereIn('department_id', $departmentIds)
            ->where(fn ($query) => $query->whereIn('status', ['COMPLETED', 'CANCELLED'])->orWhereNull('due_date')->orWhereDate('due_date', '>=', today()))
            ->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        if ($overdueTaskCount > 0) $tasksByStatus->put('OVERDUE', $overdueTaskCount);
        $projectStatuses = collect(ProjectStatus::cases())->mapWithKeys(fn (ProjectStatus $status) => [$status->value => 0]);
        $projectStatusCounts = Project::query()->selectRaw('status, COUNT(*) as aggregate')
            ->whereHas('departments', fn ($query) => $query->whereIn('departments.id', $departmentIds))
            ->groupBy('status')->pluck('aggregate', 'status');
        $projectStatuses = $projectStatuses->merge($projectStatusCounts);

        $now = now();
        $monthlyTaskCompletion = collect(range(5, 0))->mapWithKeys(function (int $monthsAgo) use ($now, $departmentIds): array {
            $month = $now->copy()->startOfMonth()->subMonths($monthsAgo);
            $count = DepartmentTask::query()->whereIn('department_id', $departmentIds)->whereNotNull('completed_at')
                ->whereBetween('completed_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->count();

            return [$month->format('M Y') => $count];
        });

        $pendingApprovals = ApprovalRequest::query()->with(['workflow', 'department', 'requestedBy', 'approvable'])
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])->whereIn('department_id', $departmentIds)
            ->latest('submitted_at')->limit(8)->get();
        $pendingApprovals->each(fn (ApprovalRequest $approval) => $approval->setAttribute('display_title', $this->approvalTitle($approval)));

        $upcomingDeadlines = collect();
        DepartmentTask::query()->whereIn('department_id', $departmentIds)->whereNotNull('due_date')->whereDate('due_date', '>=', today())
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->with('department:id,name')->orderBy('due_date')->limit(10)->get()
            ->each(fn (DepartmentTask $task) => $upcomingDeadlines->push((object) [
                'title' => $task->title, 'department' => $task->department?->name, 'type' => 'Task', 'due_at' => $task->due_date,
            ]));
        Project::query()->where('status', ProjectStatus::Active)->whereNotNull('end_date')->whereDate('end_date', '>=', today())
            ->when($departmentId, fn ($query) => $query->whereHas('departments', fn ($departments) => $departments->whereKey($departmentId)))
            ->with('departments:id,name')->orderBy('end_date')->limit(10)->get(['id', 'title', 'end_date'])
            ->each(fn (Project $project) => $upcomingDeadlines->push((object) [
                'title' => $project->translated('title'), 'department' => $project->departments->first()?->name, 'type' => 'Project end date', 'due_at' => $project->end_date,
            ]));
        $upcomingDeadlines = $upcomingDeadlines->sortBy('due_at')->take(10)->values();

        $activity = DepartmentActivityLog::query()->with(['user:id,name', 'department:id,name'])
            ->whereIn('department_id', $departmentIds)->latest()->limit(12)->get();

        $scopeLabel = $departmentId ? $departments->first()?->name : 'Across all active departments';
        $stats = [
            ['label' => 'Active departments', 'value' => number_format($departments->count()), 'description' => $departmentId ? 'Selected department' : 'Departments currently operating', 'icon' => 'heroicon-o-building-office-2', 'tone' => 'blue'],
            ['label' => 'Active staff', 'value' => number_format($activeStaffCount), 'description' => 'Active team member records', 'icon' => 'heroicon-o-user-group', 'tone' => 'violet'],
            ['label' => 'Active projects', 'value' => number_format($activeProjectCount), 'description' => 'Projects with active status', 'icon' => 'heroicon-o-briefcase', 'tone' => 'mint'],
            ['label' => 'Open tasks', 'value' => number_format($openTaskCount), 'description' => 'Tasks not completed or cancelled', 'icon' => 'heroicon-o-clipboard-document-list', 'tone' => 'blue'],
            ['label' => 'Overdue tasks', 'value' => number_format($overdueTaskCount), 'description' => 'Open tasks past their due date', 'icon' => 'heroicon-o-clock', 'tone' => 'amber'],
            ['label' => 'Pending approvals', 'value' => number_format($pendingApprovalCount), 'description' => 'Requests awaiting a decision', 'icon' => 'heroicon-o-inbox', 'tone' => 'violet'],
        ];

        return [
            'departments' => $departments,
            'departmentOptions' => $departmentOptions,
            'departmentId' => $departmentId,
            'scopeLabel' => $scopeLabel,
            'staffCount' => $activeStaffCount,
            'projectCount' => $activeProjectCount,
            'openTasks' => $openTaskCount,
            'overdueTasks' => $overdueTaskCount,
            'pendingApprovals' => $pendingApprovalCount,
            'organizationStats' => $stats,
            'tasksByStatus' => $tasksByStatus,
            'projectsByStatus' => $projectStatuses,
            'monthlyTaskCompletion' => $monthlyTaskCompletion,
            'pendingApprovalItems' => $pendingApprovals,
            'upcomingDeadlines' => $upcomingDeadlines,
            'activity' => $activity,
        ];
    }

    private function approvalTitle(ApprovalRequest $approval): string
    {
        $subject = $approval->approvable;
        if (! $subject) return $approval->workflow?->name ?? 'Approval request';
        if (method_exists($subject, 'translated')) return $subject->translated('title') ?: ($approval->workflow?->name ?? class_basename($subject));

        $title = data_get($subject, 'title');
        if (is_array($title)) return $title[app()->getLocale()] ?? $title['en'] ?? ($approval->workflow?->name ?? class_basename($subject));
        if (is_string($title) && filled($title)) return $title;

        return $approval->workflow?->name ?? class_basename($subject);
    }

    public static function canAccess(): bool { return auth()->user()?->can('organization.full_access') ?? false; }
}

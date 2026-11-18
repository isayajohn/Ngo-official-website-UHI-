<?php

namespace App\Filament\Pages;

use App\Models\ApprovalRequest;
use App\Models\Department;
use App\Models\DepartmentActivityLog;
use App\Models\DepartmentTask;
use App\Models\ProjectMilestone;
use App\Models\TeamMember;
use App\Services\DepartmentAccessService;
use Filament\Pages\Page;

class MyWork extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static ?string $navigationLabel = 'My Work';
    protected static ?string $title = 'My Work';
    protected string $view = 'filament.pages.my-work';

    protected function getViewData(): array
    {
        $user = auth()->user();
        $teamMemberIds = TeamMember::where('user_id', $user->id)->select('id');
        $myTasks = DepartmentTask::with('department')->where(fn ($q) => $q->where('assigned_to_user_id', $user->id)->orWhereIn('assigned_to_team_member_id', $teamMemberIds))->orderByRaw('due_date IS NULL')->orderBy('due_date')->get();
        $assignedByMe = DepartmentTask::with('department', 'assignedToUser', 'assignedToTeamMember')->where('assigned_by_user_id', $user->id)->latest()->limit(10)->get();
        $access = app(DepartmentAccessService::class);
        $pendingApprovals = ApprovalRequest::with(['workflow.steps', 'department', 'requestedBy', 'approvable'])->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])->get()->filter(fn ($request) => $access->canApprove($user, $request))->values();
        $returnedItems = ApprovalRequest::with(['workflow', 'department', 'approvable'])->where('requested_by_user_id', $user->id)->where('status', 'RETURNED')->latest()->limit(10)->get();
        $upcoming = $myTasks->filter(fn ($task) => $task->due_date && $task->due_date->gte(today()) && ! in_array($task->status, ['COMPLETED', 'CANCELLED'], true))->take(10);
        $upcomingMilestones = ProjectMilestone::query()->with('project')->where('responsible_user_id', $user->id)
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->whereDate('due_date', '>=', today())->orderBy('due_date')->limit(10)->get();
        $overdue = $myTasks->filter(fn ($task) => $task->is_overdue)->take(10);
        $departmentIds = $user->departments()->wherePivot('active', true)->select('departments.id');
        $leadershipDepartmentIds = Department::query()->whereHas('activeLeadership.teamMember', fn ($q) => $q->where('user_id', $user->id))->select('id');
        $legacyHeadDepartmentIds = Department::query()->whereHas('head', fn ($q) => $q->where('user_id', $user->id))->select('id');
        $recentActivity = DepartmentActivityLog::with(['department', 'user'])
            ->where(fn ($q) => $q->whereIn('department_id', $departmentIds)->orWhereIn('department_id', $leadershipDepartmentIds)->orWhereIn('department_id', $legacyHeadDepartmentIds))
            ->latest()->limit(10)->get();
        return compact('myTasks', 'assignedByMe', 'pendingApprovals', 'returnedItems', 'upcoming', 'upcomingMilestones', 'overdue', 'recentActivity');
    }

    public static function canAccess(): bool { return auth()->check(); }
}

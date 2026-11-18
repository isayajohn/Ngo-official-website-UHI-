<?php

namespace App\Policies;

use App\Models\DepartmentTask;
use App\Models\User;
use App\Services\DepartmentAccessService;

class DepartmentTaskPolicy
{
    public function viewAny(User $user): bool { return $user->can('department.view') || $user->can('department.assign_task') || $user->can('organization.full_access'); }
    public function view(User $user, DepartmentTask $task): bool { return app(DepartmentAccessService::class)->canAccessDepartment($user, $task->department) || $task->assigned_to_user_id === $user->id || $task->assignedToTeamMember?->user_id === $user->id; }
    public function create(User $user): bool { return $user->can('department.assign_task') || $user->can('organization.full_access'); }
    public function update(User $user, DepartmentTask $task): bool { return app(DepartmentAccessService::class)->canAssignTask($user, $task->department); }
    public function delete(User $user, DepartmentTask $task): bool { return app(DepartmentAccessService::class)->canAssignTask($user, $task->department); }
}

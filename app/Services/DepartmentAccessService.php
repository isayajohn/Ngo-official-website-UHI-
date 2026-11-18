<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;

class DepartmentAccessService
{
    public function hasOrganizationAccess(User $user): bool
    {
        return $user->can('organization.full_access');
    }

    public function canAccessDepartment(User $user, Department $department): bool
    {
        if ($this->hasOrganizationAccess($user)) return true;

        return $user->departments()->whereKey($department->getKey())->wherePivot('active', true)->exists()
            || $department->activeLeadership()->whereHas('teamMember', fn ($q) => $q->where('user_id', $user->id))->exists()
            || $department->head()->where('user_id', $user->id)->exists();
    }

    public function canManageDepartment(User $user, Department $department, string $permission = 'department.manage'): bool
    {
        if (! $user->can($permission) && ! $user->can('department.update')) return false;
        return $this->hasOrganizationAccess($user) || $this->canAccessDepartment($user, $department);
    }

    public function canAssignTask(User $user, Department $department): bool
    {
        if (! $user->can('department.assign_task') && ! $user->can('department.manage_staff')) return false;
        if ($this->hasOrganizationAccess($user)) return true;
        if (! $this->canAccessDepartment($user, $department)) return false;

        return $department->activeLeadership()->whereHas('teamMember', fn ($q) => $q->where('user_id', $user->id))
            ->where('can_assign_tasks', true)->exists() || $user->can('department.assign_task');
    }

    public function canManageProject(User $user, Project $project): bool
    {
        if (! $user->can('project.update') && ! $user->can('department.manage_projects')) return false;
        if ($this->hasOrganizationAccess($user)) return true;
        $lead = $project->departments()->wherePivot('is_lead', true)->first();
        if (! $lead || ! $this->canAccessDepartment($user, $lead)) return false;
        return $user->can('department.manage_projects') || $lead->activeLeadership()->where('can_manage_projects', true)->whereHas('teamMember', fn ($q) => $q->where('user_id', $user->id))->exists();
    }

    public function canViewProject(User $user, Project $project): bool
    {
        if ($this->hasOrganizationAccess($user)) return true;
        return $project->departments()->get()->contains(fn (Department $department) => $this->canAccessDepartment($user, $department));
    }

    public function canApprove(User $user, ApprovalRequest $request): bool
    {
        if ($this->hasOrganizationAccess($user)) return true;

        $step = $request->workflow->steps()->where('step_order', $request->current_step)->first();
        if (! $step) return false;
        if ($step->required_permission && ! $user->can($step->required_permission)) return false;

        if ($step->department_scoped && (! $request->department || ! $this->canAccessDepartment($user, $request->department))) return false;

        if ($step->approver_type === 'PERMISSION') {
            return filled($step->required_permission) && $user->can($step->required_permission);
        }
        if ($step->approver_type === 'SPECIFIC_USER') return (int) $step->specific_user_id === (int) $user->id;
        if ($step->approver_type === 'EXECUTIVE_SECRETARY') return $user->can($step->required_permission ?: 'organization.full_access');

        $leadershipRole = $step->required_leadership_role ?: match ($step->approver_type) {
            'DEPARTMENT_HEAD' => 'HEAD',
            'DEPARTMENT_MANAGER' => 'MANAGER',
            default => null,
        };
        if (! $leadershipRole || ! $request->department || ! $user->can('department.approve')) return false;

        return $request->department->activeLeadership()->where('leadership_role', $leadershipRole)
            ->where('can_approve', true)->whereHas('teamMember', fn ($q) => $q->where('user_id', $user->id))->exists();
    }
}

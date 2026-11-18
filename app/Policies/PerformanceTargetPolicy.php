<?php

namespace App\Policies;

use App\Models\PerformanceTarget;
use App\Models\User;
use App\Services\DepartmentAccessService;
use App\Services\PerformanceScopeService;

class PerformanceTargetPolicy
{
    public function viewAny(User $user): bool { return $user->can('performance.view') || $user->can('performance.manage_target') || $user->can('performance.enter_result') || $user->can('mel.target.view') || $user->can('mel.target.manage') || $user->can('organization.full_access'); }
    public function view(User $user, PerformanceTarget $target): bool
    {
        if (! $this->viewAny($user)) return false;
        if ($user->can('organization.full_access')) return true;
        if ($target->department) return ($user->can('performance.view_department') || $user->can('performance.manage_target') || $user->can('mel.target.view') || $user->can('mel.target.manage')) && app(DepartmentAccessService::class)->canAccessDepartment($user, $target->department);
        if ($target->project) return ($user->can('performance.view_project') || $user->can('performance.manage_target') || $user->can('mel.target.view') || $user->can('mel.target.manage')) && app(DepartmentAccessService::class)->canViewProject($user, $target->project);
        if ($target->programme_id) return ($user->can('performance.view_programme') || $user->can('performance.manage_target') || $user->can('mel.target.view') || $user->can('mel.target.manage')) && in_array($target->programme_id, app(PerformanceScopeService::class)->programmeIds($user));
        if ($target->team_member_id) return $user->can('organization.full_access') || ($user->can('staff_performance.review') && $target->teamMember?->department_id && app(DepartmentAccessService::class)->canAccessDepartment($user, $target->teamMember->department));
        return $user->can('performance.view_organization') || $user->can('mel.target.view') || $user->can('mel.target.manage');
    }
    public function create(User $user): bool { return $user->can('performance.manage_target') || $user->can('mel.target.manage') || $user->can('organization.full_access'); }
    public function update(User $user, PerformanceTarget $target): bool { return $this->create($user) && $this->view($user, $target) && ! $target->results()->where('status', 'VERIFIED')->exists(); }
    public function delete(User $user, PerformanceTarget $target): bool { return $this->update($user, $target) && ! $target->results()->exists(); }
}

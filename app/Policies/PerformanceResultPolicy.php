<?php

namespace App\Policies;

use App\Models\PerformanceResult;
use App\Models\User;
use App\Services\DepartmentAccessService;

class PerformanceResultPolicy
{
    public function viewAny(User $user): bool { return $user->can('performance.view') || $user->can('performance.enter_result') || $user->can('performance.verify_result') || $user->can('organization.full_access'); }
    public function view(User $user, PerformanceResult $result): bool
    {
        if (! $this->viewAny($user)) return false;
        if ($user->can('organization.full_access')) return true;
        if ($result->department) return ($user->can('performance.view_department') || $user->can('performance.enter_result') || $user->can('performance.verify_result')) && app(DepartmentAccessService::class)->canAccessDepartment($user, $result->department);
        if ($result->project) return ($user->can('performance.view_project') || $user->can('performance.enter_result') || $user->can('performance.verify_result')) && app(DepartmentAccessService::class)->canViewProject($user, $result->project);
        if ($result->programme_id) return $user->can('performance.view_programme') || $user->can('performance.enter_result') || $user->can('performance.verify_result');
        return $user->can('performance.view_organization') || $user->can('performance.enter_result') || $user->can('performance.verify_result');
    }
    public function create(User $user): bool { return $user->can('performance.enter_result') || $user->can('organization.full_access'); }
    public function update(User $user, PerformanceResult $result): bool
    {
        if (! $this->create($user) || $result->status === 'VERIFIED') return false;
        if ($user->can('organization.full_access')) return true;
        if ($result->entered_by_user_id !== $user->id && ! $user->can('performance.manage_kpi')) return false;
        if ($result->department) return app(DepartmentAccessService::class)->canAccessDepartment($user, $result->department);
        if ($result->project) return app(DepartmentAccessService::class)->canViewProject($user, $result->project);
        return $user->can('performance.view_organization') || $user->can('performance.manage_kpi');
    }
    public function verify(User $user, PerformanceResult $result): bool
    {
        if (! $user->can('performance.verify_result') || $result->status !== 'SUBMITTED') return false;
        if ($user->can('organization.full_access')) return true;
        if ($result->department) return app(DepartmentAccessService::class)->canAccessDepartment($user, $result->department);
        if ($result->project) return app(DepartmentAccessService::class)->canViewProject($user, $result->project);
        return $user->can('performance.view_organization');
    }
    public function delete(User $user, PerformanceResult $result): bool { return $user->can('performance.manage_kpi') && $result->status === 'DRAFT'; }
}

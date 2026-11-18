<?php

namespace App\Policies;

use App\Models\PerformanceIndicator;
use App\Models\User;
use App\Services\DepartmentAccessService;
use App\Services\PerformanceScopeService;

class PerformanceIndicatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('performance.view') || $user->can('performance.view_organization') || $user->can('performance.view_department') || $user->can('performance.view_programme') || $user->can('performance.view_project') || $user->can('performance.manage_kpi') || $user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update') || $user->can('organization.full_access');
    }

    public function view(User $user, PerformanceIndicator $indicator): bool
    {
        if (! $this->viewAny($user)) return false;
        return match ($indicator->level) {
            'ORGANIZATION', 'FINANCE', 'MEL' => $user->can('performance.view_organization') || $user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update') || $user->can('organization.full_access'),
            'DEPARTMENT' => ($user->can('performance.view_department') || $user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update') || $user->can('organization.full_access')) && (! $indicator->department || app(DepartmentAccessService::class)->canAccessDepartment($user, $indicator->department)),
            'PROGRAMME' => ($user->can('performance.view_programme') || $user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update') || $user->can('organization.full_access')) && (! $indicator->programme_id || in_array($indicator->programme_id, app(PerformanceScopeService::class)->programmeIds($user))),
            'PROJECT' => ($user->can('performance.view_project') || $user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update') || $user->can('organization.full_access')) && (! $indicator->project || app(DepartmentAccessService::class)->canViewProject($user, $indicator->project)),
            'STAFF' => $user->can('staff_performance.view') || $user->can('organization.full_access'),
            default => false,
        };
    }

    public function create(User $user): bool { return $user->can('performance.manage_kpi') || $user->can('mel.indicator.create') || $user->can('organization.full_access'); }
    public function update(User $user, PerformanceIndicator $indicator): bool { return ($user->can('performance.manage_kpi') || $user->can('mel.indicator.update') || $user->can('organization.full_access')) && $this->inScope($user, $indicator); }
    public function delete(User $user, PerformanceIndicator $indicator): bool { return ($user->can('performance.manage_kpi') || $user->can('mel.indicator.delete') || $user->can('organization.full_access')) && $this->inScope($user, $indicator) && ! $indicator->targets()->exists() && ! $indicator->results()->exists(); }

    private function inScope(User $user, PerformanceIndicator $indicator): bool
    {
        if ($user->can('organization.full_access')) return true;
        return match ($indicator->level) {
            'ORGANIZATION', 'FINANCE', 'MEL' => $user->can('performance.view_organization') || $user->can('mel.indicator.update') || $user->can('mel.indicator.create'),
            'DEPARTMENT' => $indicator->department && app(DepartmentAccessService::class)->canManageDepartment($user, $indicator->department, $user->can('mel.indicator.update') ? 'mel.indicator.update' : ($user->can('mel.indicator.create') ? 'mel.indicator.create' : 'performance.manage_kpi')),
            'PROJECT' => $indicator->project && in_array($indicator->project_id, app(PerformanceScopeService::class)->projectIds($user)),
            'PROGRAMME' => $indicator->programme && ($user->can('programme.update') || $user->can('performance.manage_kpi') || $user->can('mel.indicator.update') || $user->can('mel.indicator.create')) && in_array($indicator->programme_id, app(PerformanceScopeService::class)->programmeIds($user)),
            'STAFF' => $user->can('staff_performance.review'),
            default => false,
        };
    }
}

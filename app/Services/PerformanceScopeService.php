<?php

namespace App\Services;

use App\Models\Department;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceResult;
use App\Models\PerformanceTarget;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PerformanceScopeService
{
    public function departmentIds(User $user): array
    {
        if ($user->can('organization.full_access') || $user->can('performance.view_organization')) return Department::query()->pluck('id')->all();
        $access = app(DepartmentAccessService::class);
        return Department::query()->where('active', true)->get()->filter(fn (Department $department) => $access->canAccessDepartment($user, $department))->modelKeys();
    }

    public function projectIds(User $user): array
    {
        if ($user->can('organization.full_access') || $user->can('performance.view_organization')) {
            return Project::query()->pluck('id')->all();
        }
        $departmentIds = $this->departmentIds($user);
        if (! $departmentIds) return [];
        return Project::query()->whereHas('departments', fn (Builder $query) => $query->whereIn('departments.id', $departmentIds))->pluck('id')->all();
    }

    public function programmeIds(User $user): array
    {
        if ($user->can('organization.full_access') || $user->can('performance.view_organization')) return Programme::query()->pluck('id')->all();
        $departmentIds = $this->departmentIds($user);
        if (! $departmentIds) return [];
        return Programme::query()->where(function (Builder $query) use ($departmentIds): void {
            $query->whereHas('departments', fn (Builder $departments) => $departments->whereIn('departments.id', $departmentIds))
                ->orWhereHas('projects.departments', fn (Builder $departments) => $departments->whereIn('departments.id', $departmentIds));
        })->pluck('id')->all();
    }

    public function scopeIndicators(Builder $query, User $user): Builder
    {
        if ($user->can('organization.full_access') || $user->can('performance.view_organization')) return $query;
        $departmentIds = $this->departmentIds($user);
        $programmeIds = $this->programmeIds($user);
        $projectIds = $this->projectIds($user);

        return $query->where(function (Builder $scope) use ($user, $departmentIds, $programmeIds, $projectIds): void {
            if ($user->can('performance.view_department') || $user->can('performance.manage_kpi') || $user->can('performance.enter_result') || $user->can('performance.verify_result') || $user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update')) {
                $scope->orWhere(fn (Builder $q) => $q->where('level', 'DEPARTMENT')->whereIn('department_id', $departmentIds));
            }
            if ($user->can('performance.view_programme') || $user->can('performance.manage_kpi') || $user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update')) {
                $scope->orWhere(fn (Builder $q) => $q->where('level', 'PROGRAMME')->whereIn('programme_id', $programmeIds));
            }
            if ($user->can('performance.view_project') || $user->can('performance.manage_kpi') || $user->can('performance.enter_result') || $user->can('performance.verify_result') || $user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update')) {
                $scope->orWhere(fn (Builder $q) => $q->where('level', 'PROJECT')->whereIn('project_id', $projectIds));
            }
            if ($user->can('staff_performance.view') || $user->can('staff_performance.review')) $scope->orWhere('level', 'STAFF');
            if ($user->can('mel.indicator.view') || $user->can('mel.indicator.create') || $user->can('mel.indicator.update')) $scope->orWhere('level', 'MEL');
        });
    }

    public function scopeTargets(Builder $query, User $user): Builder
    {
        if ($user->can('organization.full_access') || $user->can('performance.view_organization')) return $query;
        $departmentIds = $this->departmentIds($user);
        $programmeIds = $this->programmeIds($user);
        $projectIds = $this->projectIds($user);

        return $query->where(function (Builder $scope) use ($user, $departmentIds, $programmeIds, $projectIds): void {
            if ($user->can('performance.view_department') || $user->can('performance.manage_target') || $user->can('performance.enter_result') || $user->can('performance.verify_result') || $user->can('mel.target.view') || $user->can('mel.target.manage')) {
                $scope->orWhere(fn (Builder $q) => $q->whereIn('department_id', $departmentIds)->orWhereHas('indicator', fn (Builder $indicator) => $indicator->where('level', 'DEPARTMENT')->whereIn('department_id', $departmentIds)));
            }
            if ($user->can('performance.view_programme') || $user->can('performance.manage_target') || $user->can('mel.target.view') || $user->can('mel.target.manage')) {
                $scope->orWhere(fn (Builder $q) => $q->whereIn('programme_id', $programmeIds)->orWhereHas('indicator', fn (Builder $indicator) => $indicator->where('level', 'PROGRAMME')->whereIn('programme_id', $programmeIds)));
            }
            if ($user->can('performance.view_project') || $user->can('performance.manage_target') || $user->can('performance.enter_result') || $user->can('performance.verify_result') || $user->can('mel.target.view') || $user->can('mel.target.manage')) {
                $scope->orWhere(fn (Builder $q) => $q->whereIn('project_id', $projectIds)->orWhereHas('indicator', fn (Builder $indicator) => $indicator->where('level', 'PROJECT')->whereIn('project_id', $projectIds)));
            }
            if ($user->can('performance.view_organization') || $user->can('performance.manage_target') || $user->can('mel.target.view') || $user->can('mel.target.manage')) $scope->orWhereHas('indicator', fn (Builder $indicator) => $indicator->whereIn('level', ['ORGANIZATION', 'FINANCE', 'MEL']));
        });
    }

    public function scopeResults(Builder $query, User $user): Builder
    {
        if ($user->can('organization.full_access') || $user->can('performance.view_organization')) return $query;
        $departmentIds = $this->departmentIds($user);
        $programmeIds = $this->programmeIds($user);
        $projectIds = $this->projectIds($user);

        return $query->where(function (Builder $scope) use ($user, $departmentIds, $programmeIds, $projectIds): void {
            if ($user->can('performance.view_department') || $user->can('performance.enter_result') || $user->can('performance.verify_result')) {
                $scope->orWhere(fn (Builder $q) => $q->whereIn('department_id', $departmentIds)->orWhereHas('indicator', fn (Builder $i) => $i->where('level', 'DEPARTMENT')->whereIn('department_id', $departmentIds)));
            }
            if ($user->can('performance.view_programme') || $user->can('performance.enter_result') || $user->can('performance.verify_result')) {
                $scope->orWhere(fn (Builder $q) => $q->whereIn('programme_id', $programmeIds)->orWhereHas('indicator', fn (Builder $i) => $i->where('level', 'PROGRAMME')->whereIn('programme_id', $programmeIds)));
            }
            if ($user->can('performance.view_project') || $user->can('performance.enter_result') || $user->can('performance.verify_result')) {
                $scope->orWhere(fn (Builder $q) => $q->whereIn('project_id', $projectIds)->orWhereHas('indicator', fn (Builder $i) => $i->where('level', 'PROJECT')->whereIn('project_id', $projectIds)));
            }
            if ($user->can('performance.view_organization') || $user->can('performance.enter_result') || $user->can('performance.verify_result')) $scope->orWhereHas('indicator', fn (Builder $indicator) => $indicator->whereIn('level', ['ORGANIZATION', 'FINANCE', 'MEL']));
        });
    }
}

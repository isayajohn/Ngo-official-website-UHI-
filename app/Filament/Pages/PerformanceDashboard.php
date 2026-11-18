<?php

namespace App\Filament\Pages;

use App\Enums\ProjectStatus;
use App\Models\Department;
use App\Models\DepartmentTask;
use App\Models\PerformanceResult;
use App\Models\Project;
use App\Models\ReportingPeriod;
use App\Services\PerformanceCalculationService;
use App\Services\PerformanceScopeService;
use Filament\Pages\Page;

class PerformanceDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Performance';
    protected static ?string $navigationLabel = 'Executive Dashboard';
    protected static ?string $title = 'Organization Performance';
    protected string $view = 'filament.pages.performance-dashboard';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->can('organization.full_access') || $user->can('performance.view_organization') || $user->can('performance.view_department') || $user->can('performance.view_project') || $user->can('performance.view_programme'));
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        abort_unless(static::canAccess(), 403);
        $scope = app(PerformanceScopeService::class);
        $calculator = app(PerformanceCalculationService::class);
        $organizationView = $user->can('organization.full_access') || $user->can('performance.view_organization');
        $departmentIds = $scope->departmentIds($user);
        $projectIds = $scope->projectIds($user);

        $departmentOptions = Department::query()->whereIn('id', $departmentIds)->where('active', true)->orderBy('name')->get(['id', 'name']);
        $projectOptions = Project::query()->whereIn('id', $projectIds)->with('departments:id,name')->orderBy('id')->get();
        $periods = ReportingPeriod::query()->orderByDesc('start_date')->get();
        $periodId = request()->integer('period') ?: $periods->first()?->id;
        $period = $periodId ? ReportingPeriod::find($periodId) : null;
        abort_if($periodId && ! $period, 404);

        $departmentId = request()->integer('department') ?: null;
        if ($departmentId) abort_unless($organizationView || $user->can('performance.view_department'), 403);
        if ($departmentId && ! $departmentOptions->contains('id', $departmentId)) abort(403);
        if (! $organizationView && ! $departmentId && $departmentOptions->isNotEmpty() && ! request()->integer('project')) $departmentId = (int) $departmentOptions->first()->id;

        $projectId = request()->integer('project') ?: null;
        if ($projectId) abort_unless($organizationView || $user->can('performance.view_project'), 403);
        if ($projectId && ! $projectOptions->contains('id', $projectId)) abort(403);
        if ($departmentId && $projectId && ! $projectOptions->firstWhere('id', $projectId)?->departments->contains('id', $departmentId)) abort(403);

        $activeDepartment = $departmentId ? $departmentOptions->firstWhere('id', $departmentId) : null;
        $activeProject = $projectId ? $projectOptions->firstWhere('id', $projectId) : null;
        $rows = $period ? $calculator->targetResults($period, array_filter(['department_id' => $departmentId, 'project_id' => $projectId])) : collect();
        $score = null;
        if ($period) {
            $score = $activeProject ? $calculator->calculateProjectPerformance($activeProject, $period)
                : ($activeDepartment ? $calculator->calculateDepartmentPerformance($activeDepartment, $period) : $calculator->calculateOrganizationPerformance($period));
        }
        $rating = $calculator->ratingForScore($score);
        $onTrack = $rows->where('status', 'ON_TRACK')->count();
        $critical = $rows->where('status', 'CRITICAL')->count();
        $noData = $rows->where('status', 'NO_DATA')->count();

        $projectPerformance = $period && ($organizationView || $user->can('performance.view_project'))
            ? $projectOptions->take(8)->map(fn (Project $project) => [
                'project' => $project,
                'score' => $calculator->calculateProjectPerformance($project, $period),
                'rating' => $calculator->ratingForScore($calculator->calculateProjectPerformance($project, $period)),
            ])
            : collect();
        $departmentPerformance = $period && ($organizationView || $user->can('performance.view_department'))
            ? $departmentOptions->take(8)->map(fn (Department $department) => [
                'department' => $department,
                'score' => $calculator->calculateDepartmentPerformance($department, $period),
                'rating' => $calculator->ratingForScore($calculator->calculateDepartmentPerformance($department, $period)),
            ])
            : collect();

        $tasks = DepartmentTask::query()->when($activeDepartment, fn ($query) => $query->where('department_id', $activeDepartment->id))
            ->when(! $activeDepartment && ! $activeProject && ! $organizationView, fn ($query) => $query->whereIn('department_id', $departmentIds));
        if ($period) {
            $tasks->where(fn ($query) => $query->whereBetween('due_date', [$period->start_date, $period->end_date])
                ->orWhereBetween('completed_at', [$period->start_date->startOfDay(), $period->end_date->endOfDay()]));
        }
        $taskTotal = (clone $tasks)->whereNotIn('status', ['CANCELLED'])->count();
        $taskCompleted = (clone $tasks)->where('status', 'COMPLETED')->count();
        $taskCompletion = $taskTotal ? round($taskCompleted / $taskTotal * 100, 1) : null;

        return [
            'organizationView' => $organizationView,
            'scopeTitle' => $activeProject?->translated('title') ?? $activeDepartment?->name ?? 'Organization-wide',
            'departmentOptions' => $departmentOptions,
            'projectOptions' => $projectOptions,
            'periods' => $periods,
            'period' => $period,
            'departmentId' => $departmentId,
            'projectId' => $projectId,
            'score' => $score,
            'rating' => $rating,
            'rows' => $rows,
            'onTrack' => $onTrack,
            'critical' => $critical,
            'noData' => $noData,
            'projectPerformance' => $projectPerformance,
            'departmentPerformance' => $departmentPerformance,
            'taskCompletion' => $taskCompletion,
            'taskTotal' => $taskTotal,
            'activeProjects' => $activeDepartment ? $activeDepartment->projects()->where('status', ProjectStatus::Active)->count() : ($activeProject ? 1 : $projectOptions->where('status', ProjectStatus::Active)->count()),
            'resultCount' => $period ? PerformanceResult::query()->where('reporting_period_id', $period->id)->where('status', 'VERIFIED')->when($activeDepartment, fn ($query) => $query->where('department_id', $activeDepartment->id))->when($activeProject, fn ($query) => $query->where('project_id', $activeProject->id))->count() : 0,
        ];
    }
}

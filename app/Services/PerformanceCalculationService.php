<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DepartmentTask;
use App\Models\PerformanceRatingScale;
use App\Models\PerformanceResult;
use App\Models\PerformanceTarget;
use App\Models\PerformanceIndicator;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ReportingPeriod;
use App\Support\SiteSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PerformanceCalculationService
{
    public function calculateAchievement(?float $actual, ?float $target, string $direction, ?float $minimum = null, ?float $maximum = null): ?float
    {
        if ($actual === null || ! is_finite($actual)) return null;

        if ($direction === 'TARGET_RANGE') {
            if ($minimum === null || $maximum === null || ! is_finite($minimum) || ! is_finite($maximum) || $minimum > $maximum) return null;
            if ($actual >= $minimum && $actual <= $maximum) return 100.0;
            $range = $maximum - $minimum;
            if ($range == 0.0) return 0.0;
            $distance = $actual < $minimum ? $minimum - $actual : $actual - $maximum;
            return max(0.0, 100.0 * (1.0 - ($distance / $range)));
        }

        if ($target === null || ! is_finite($target)) return null;
        if ($target == 0.0) {
            if ($direction === 'LOWER_IS_BETTER') return $actual <= 0.0 ? 100.0 : 0.0;
            return $actual === 0.0 ? 100.0 : ($actual > 0.0 ? $this->scoreCap() : 0.0);
        }

        if ($direction === 'LOWER_IS_BETTER') {
            if ($actual <= $target) return 100.0;
            if ($actual <= 0.0 || $target < 0.0) return 0.0;
            return max(0.0, 100.0 * ($target / $actual));
        }

        return max(0.0, 100.0 * ($actual / $target));
    }

    public function calculateScore(?float $actual, ?float $target, string $direction, ?float $weight = null, ?float $minimum = null, ?float $maximum = null): ?float
    {
        $achievement = $this->calculateAchievement($actual, $target, $direction, $minimum, $maximum);
        if ($achievement === null) return null;
        $score = min($achievement, $this->scoreCap());
        return $weight === null ? round($score, 2) : round($score * max(0.0, $weight) / 100.0, 2);
    }

    /** @param iterable<array{achievement_percentage?: mixed, weight?: mixed}> $measurements */
    public function calculateWeightedScore(iterable $measurements): ?float
    {
        $weighted = 0.0;
        $weights = 0.0;
        foreach ($measurements as $measurement) {
            $achievement = data_get($measurement, 'achievement_percentage');
            $weight = (float) data_get($measurement, 'weight', 0);
            if ($achievement === null || ! is_numeric($achievement) || $weight <= 0) continue;
            $weighted += min((float) $achievement, $this->scoreCap()) * $weight;
            $weights += $weight;
        }
        return $weights > 0 ? round($weighted / $weights, 2) : null;
    }

    public function ratingForScore(?float $score): ?PerformanceRatingScale
    {
        if ($score === null) return null;
        return PerformanceRatingScale::query()->where('active', true)
            ->where('minimum_score', '<=', $score)->where('maximum_score', '>=', $score)
            ->orderBy('display_order')->first();
    }

    public function calculateDepartmentPerformance(Department|int $department, ReportingPeriod $period): ?float
    {
        $departmentId = $department instanceof Department ? $department->getKey() : $department;
        return $this->calculateForTargets($this->targetsForPeriod($period)->where(function (Builder $query) use ($departmentId): void {
            $query->where('department_id', $departmentId)->orWhereHas('indicator', fn (Builder $indicator) => $indicator->where('department_id', $departmentId));
        })->get());
    }

    public function calculateProgrammePerformance(Programme|int $programme, ReportingPeriod $period): ?float
    {
        $programmeId = $programme instanceof Programme ? $programme->getKey() : $programme;
        return $this->calculateForTargets($this->targetsForPeriod($period)->where(function (Builder $query) use ($programmeId): void {
            $query->where('programme_id', $programmeId)->orWhereHas('indicator', fn (Builder $indicator) => $indicator->where('programme_id', $programmeId));
        })->get());
    }

    public function calculateProjectPerformance(Project|int $project, ReportingPeriod $period): ?float
    {
        $projectId = $project instanceof Project ? $project->getKey() : $project;
        return $this->calculateForTargets($this->targetsForPeriod($period)->where(function (Builder $query) use ($projectId): void {
            $query->where('project_id', $projectId)->orWhereHas('indicator', fn (Builder $indicator) => $indicator->where('project_id', $projectId));
        })->get());
    }

    public function calculateOrganizationPerformance(ReportingPeriod $period): ?float
    {
        return $this->calculateForTargets($this->targetsForPeriod($period)->get());
    }

    /** @return Collection<int, array<string, mixed>> */
    public function targetResults(ReportingPeriod $period, array $scope = []): Collection
    {
        $targets = $this->targetsForPeriod($period)
            ->when(isset($scope['department_id']), fn (Builder $query) => $query->where(fn (Builder $q) => $q->where('department_id', $scope['department_id'])->orWhereHas('indicator', fn (Builder $i) => $i->where('department_id', $scope['department_id']))))
            ->when(isset($scope['programme_id']), fn (Builder $query) => $query->where(fn (Builder $q) => $q->where('programme_id', $scope['programme_id'])->orWhereHas('indicator', fn (Builder $i) => $i->where('programme_id', $scope['programme_id']))))
            ->when(isset($scope['project_id']), fn (Builder $query) => $query->where(fn (Builder $q) => $q->where('project_id', $scope['project_id'])->orWhereHas('indicator', fn (Builder $i) => $i->where('project_id', $scope['project_id']))))
            ->get();

        return $targets->map(fn (PerformanceTarget $target): array => $this->evaluateTarget($target))->values();
    }

    public function evaluateTarget(PerformanceTarget $target): array
    {
        $indicator = $target->indicator;
        $result = null;
        $actual = match ($indicator->data_source) {
            'PROJECT_ACTIVITY' => $this->activityActual($target),
            'TASK' => $this->taskActual($target),
            default => null,
        };

        if ($actual === null && in_array($indicator->data_source, ['MANUAL', 'CUSTOM', 'MEL_INDICATOR', 'FINANCE', 'REPORT_SUBMISSION', 'HR', 'PROCUREMENT'], true)) {
            $result = $target->results()->where('status', 'VERIFIED')->latest('verified_at')->first();
            $actual = $result ? (float) $result->actual_value : null;
        }

        $goal = (float) $target->target_value;
        $weight = $target->weight_override !== null ? (float) $target->weight_override : (float) $indicator->weight;
        $achievement = $this->calculateAchievement($actual, $goal, $indicator->direction, $indicator->minimum_value === null ? null : (float) $indicator->minimum_value, $indicator->maximum_value === null ? null : (float) $indicator->maximum_value);
        $score = $achievement === null ? null : round(min($achievement, $this->scoreCap()), 2);

        return [
            'target' => $target,
            'indicator' => $indicator,
            'target_value' => $goal,
            'actual_value' => $actual,
            'achievement_percentage' => $achievement,
            'score' => $score,
            'weight' => $weight,
            'status' => $this->statusForAchievement($achievement),
            'source' => $result?->source_type ?? $indicator->data_source,
            'result' => $result,
        ];
    }

    public function statusForAchievement(?float $achievement): string
    {
        if ($achievement === null) return 'NO_DATA';
        $settings = app(SiteSettings::class);
        $onTrack = (float) $settings->get('performance_on_track_threshold', 80);
        $critical = (float) $settings->get('performance_critical_threshold', 60);
        return match (true) {
            $achievement >= $onTrack => 'ON_TRACK',
            $achievement >= $critical => 'NEEDS_ATTENTION',
            default => 'CRITICAL',
        };
    }

    private function targetsForPeriod(ReportingPeriod $period): Builder
    {
        return PerformanceTarget::query()->with(['indicator', 'results' => fn ($query) => $query->where('status', 'VERIFIED')->latest('verified_at')])->where('reporting_period_id', $period->id);
    }

    private function calculateForTargets(Collection $targets): ?float
    {
        $rows = $targets->map(fn (PerformanceTarget $target) => $this->evaluateTarget($target))
            ->filter(fn (array $row) => $row['achievement_percentage'] !== null)
            ->unique(fn (array $row) => $row['indicator']->id.'-'.$row['target']->department_id.'-'.$row['target']->programme_id.'-'.$row['target']->project_id.'-'.$row['target']->team_member_id);
        return $this->calculateWeightedScore($rows->map(fn (array $row) => ['achievement_percentage' => $row['achievement_percentage'], 'weight' => $row['weight']]));
    }

    private function activityActual(PerformanceTarget $target): ?float
    {
        $period = $target->reportingPeriod;
        $query = ProjectActivity::query()
            ->where(fn (Builder $q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', $period->end_date))
            ->where(fn (Builder $q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $period->start_date));
        if ($target->project_id || $target->indicator->project_id) {
            $query->where('project_id', $target->project_id ?: $target->indicator->project_id);
        } elseif ($target->department_id || $target->indicator->department_id) {
            $departmentId = $target->department_id ?: $target->indicator->department_id;
            $query->where('department_id', $departmentId);
        } elseif ($target->programme_id || $target->indicator->programme_id) {
            $programmeId = $target->programme_id ?: $target->indicator->programme_id;
            $query->whereHas('project', fn (Builder $project) => $project->where('programme_id', $programmeId));
        }
        $activities = $query->get(['completion_percentage']);
        return $activities->isEmpty() ? null : round((float) $activities->avg('completion_percentage'), 2);
    }

    private function taskActual(PerformanceTarget $target): ?float
    {
        if (! ($departmentId = $target->department_id ?: $target->indicator->department_id)) return null;
        $period = $target->reportingPeriod;
        $tasks = DepartmentTask::query()->where('department_id', $departmentId)
            ->where(fn (Builder $query) => $query->whereBetween('due_date', [$period->start_date, $period->end_date])->orWhereBetween('completed_at', [$period->start_date->startOfDay(), $period->end_date->endOfDay()]));
        $total = (clone $tasks)->count();
        if ($total === 0) return null;
        $completed = (clone $tasks)->where('status', 'COMPLETED')->count();
        return round($completed / $total * 100, 2);
    }

    private function scoreCap(): float
    {
        return max(100.0, min(200.0, (float) app(SiteSettings::class)->get('performance_score_cap', 100)));
    }
}

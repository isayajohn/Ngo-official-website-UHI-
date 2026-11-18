<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;

class PerformanceTarget extends Model
{
    protected $fillable = ['performance_indicator_id', 'reporting_period_id', 'department_id', 'programme_id', 'project_id', 'district_id', 'team_member_id', 'target_value', 'weight_override', 'notes', 'created_by_user_id'];

    protected function casts(): array { return ['target_value' => 'decimal:4', 'weight_override' => 'decimal:2']; }

    protected static function booted(): void
    {
        static::saving(function (self $target): void {
            $indicator = $target->indicator;
            if (! $indicator) throw ValidationException::withMessages(['performance_indicator_id' => 'Select an available KPI.']);
            if ($indicator->level === 'DEPARTMENT' && (int) $indicator->department_id !== (int) $target->department_id) throw ValidationException::withMessages(['department_id' => 'The target department must match the KPI scope.']);
            if ($indicator->level === 'PROGRAMME' && (int) $indicator->programme_id !== (int) $target->programme_id) throw ValidationException::withMessages(['programme_id' => 'The target programme must match the KPI scope.']);
            if ($indicator->level === 'PROJECT' && (int) $indicator->project_id !== (int) $target->project_id) throw ValidationException::withMessages(['project_id' => 'The target project must match the KPI scope.']);
            $scopeIsValid = match ($indicator->level) {
                'DEPARTMENT' => (int) $target->department_id === (int) $indicator->department_id && ! $target->team_member_id,
                'PROGRAMME' => (int) $target->programme_id === (int) $indicator->programme_id && ! $target->team_member_id,
                'PROJECT' => (int) $target->project_id === (int) $indicator->project_id && ! $target->team_member_id,
                'STAFF' => $target->team_member_id && ! $target->project_id && ! $target->programme_id,
                default => ! $target->team_member_id,
            };
            if (! $scopeIsValid) throw ValidationException::withMessages(['performance_indicator_id' => 'Target scope must match the selected KPI level.']);
            if ($target->project_id && $target->programme_id && (int) $target->project?->programme_id !== (int) $target->programme_id) throw ValidationException::withMessages(['project_id' => 'The selected project must belong to the selected programme.']);
            if ($target->project_id && $target->department_id && ! $target->project?->departments()->whereKey($target->department_id)->exists()) throw ValidationException::withMessages(['department_id' => 'The selected department must be assigned to the selected project.']);
            if ($target->district_id && $target->project?->district_id && (int) $target->project->district_id !== (int) $target->district_id) throw ValidationException::withMessages(['district_id' => 'The district must match the selected project location.']);
            if ($target->weight_override !== null && ((float) $target->weight_override < 0 || (float) $target->weight_override > 100)) throw ValidationException::withMessages(['weight_override' => 'Weight override must be between 0 and 100.']);
            $duplicate = static::query()->where('performance_indicator_id', $target->performance_indicator_id)
                ->where('reporting_period_id', $target->reporting_period_id);
            foreach (['department_id', 'programme_id', 'project_id', 'district_id', 'team_member_id'] as $scopeField) {
                $target->{$scopeField} === null ? $duplicate->whereNull($scopeField) : $duplicate->where($scopeField, $target->{$scopeField});
            }
            if ($target->exists) $duplicate->whereKeyNot($target->getKey());
            if ($duplicate->exists()) throw ValidationException::withMessages(['target_value' => 'A target already exists for this indicator, period and scope.']);
        });
        static::created(fn (self $target) => \App\Services\DepartmentActivity::record($target->department_id, auth()->id(), 'performance.target_created', $target));
        static::updated(fn (self $target) => $target->wasChanged() ? \App\Services\DepartmentActivity::record($target->department_id, auth()->id(), 'performance.target_changed', $target, ['changed' => array_keys($target->getChanges())]) : null);
    }

    public function indicator(): BelongsTo { return $this->belongsTo(PerformanceIndicator::class, 'performance_indicator_id'); }
    public function reportingPeriod(): BelongsTo { return $this->belongsTo(ReportingPeriod::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function programme(): BelongsTo { return $this->belongsTo(Programme::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function teamMember(): BelongsTo { return $this->belongsTo(TeamMember::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
    public function results(): HasMany { return $this->hasMany(PerformanceResult::class); }
}

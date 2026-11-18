<?php

namespace App\Models;

use App\Enums\IndicatorCalculationType;
use App\Enums\IndicatorType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class PerformanceIndicator extends Model
{
    use SoftDeletes;

    public const LEVELS = ['ORGANIZATION', 'DEPARTMENT', 'PROGRAMME', 'PROJECT', 'STAFF', 'FINANCE', 'MEL'];
    public const MEASUREMENT_TYPES = ['NUMBER', 'PERCENTAGE', 'CURRENCY', 'BOOLEAN', 'RATING', 'DURATION'];
    public const DIRECTIONS = ['HIGHER_IS_BETTER', 'LOWER_IS_BETTER', 'TARGET_RANGE'];
    public const SOURCES = ['MANUAL', 'PROJECT_ACTIVITY', 'MEL_INDICATOR', 'FINANCE', 'TASK', 'REPORT_SUBMISSION', 'HR', 'PROCUREMENT', 'CUSTOM'];
    public const FREQUENCIES = ['MONTHLY', 'QUARTERLY', 'SEMI_ANNUAL', 'ANNUAL', 'CUSTOM'];

    protected $fillable = [
        'code', 'name', 'description', 'level', 'department_id', 'programme_id', 'project_id', 'indicator_id',
        'measurement_type', 'unit', 'direction', 'weight', 'target_value', 'minimum_value', 'maximum_value',
        'reporting_frequency', 'data_source', 'active', 'short_name', 'results_framework_item_id', 'indicator_type',
        'calculation_type', 'calculation_description', 'numerator_definition', 'denominator_definition', 'collection_method',
        'responsible_user_id', 'verification_method', 'baseline_value', 'baseline_date', 'definition_notes',
    ];

    protected function casts(): array
    {
        return ['weight' => 'decimal:2', 'target_value' => 'decimal:4', 'minimum_value' => 'decimal:4', 'maximum_value' => 'decimal:4', 'baseline_value' => 'decimal:4', 'baseline_date' => 'date', 'indicator_type' => IndicatorType::class, 'calculation_type' => IndicatorCalculationType::class, 'active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $indicator): void {
            if ($indicator->weight < 0 || $indicator->weight > 100) {
                throw ValidationException::withMessages(['weight' => 'KPI weight must be between 0 and 100.']);
            }
            if ($indicator->direction === 'TARGET_RANGE' && $indicator->minimum_value !== null && $indicator->maximum_value !== null && (float) $indicator->minimum_value > (float) $indicator->maximum_value) {
                throw ValidationException::withMessages(['maximum_value' => 'The maximum range value must be at least the minimum value.']);
            }
            if ($indicator->level === 'DEPARTMENT' && ! $indicator->department_id) throw ValidationException::withMessages(['department_id' => 'Department KPIs require a department.']);
            if ($indicator->level === 'PROGRAMME' && ! $indicator->programme_id) throw ValidationException::withMessages(['programme_id' => 'Programme KPIs require a programme.']);
            if ($indicator->level === 'PROJECT' && ! $indicator->project_id) throw ValidationException::withMessages(['project_id' => 'Project KPIs require a project.']);
            if ($indicator->results_framework_item_id) {
                $item = ResultsFrameworkItem::query()->find($indicator->results_framework_item_id);
                if (! $item) throw ValidationException::withMessages(['results_framework_item_id' => 'Choose an available results framework item.']);
                if ($item->programme_id && $indicator->programme_id && (int) $item->programme_id !== (int) $indicator->programme_id) throw ValidationException::withMessages(['results_framework_item_id' => 'The framework item must belong to the selected programme.']);
                if ($item->project_id && $indicator->project_id && (int) $item->project_id !== (int) $indicator->project_id) throw ValidationException::withMessages(['results_framework_item_id' => 'The framework item must belong to the selected project.']);
            }
        });
        static::created(fn (self $indicator) => \App\Services\DepartmentActivity::record($indicator->department_id, auth()->id(), 'performance.kpi_created', $indicator));
        static::updated(fn (self $indicator) => $indicator->wasChanged() ? \App\Services\DepartmentActivity::record($indicator->department_id, auth()->id(), 'performance.kpi_changed', $indicator, ['changed' => array_keys($indicator->getChanges())]) : null);
    }

    public function scopeActive(Builder $query): Builder { return $query->where('active', true); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function programme(): BelongsTo { return $this->belongsTo(Programme::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function targets(): HasMany { return $this->hasMany(PerformanceTarget::class); }
    public function results(): HasMany { return $this->hasMany(PerformanceResult::class); }
    public function resultsFrameworkItem(): BelongsTo { return $this->belongsTo(ResultsFrameworkItem::class); }
    public function responsibleUser(): BelongsTo { return $this->belongsTo(User::class, 'responsible_user_id'); }
}

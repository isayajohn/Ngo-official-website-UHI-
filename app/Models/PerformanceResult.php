<?php

namespace App\Models;

use App\Services\DepartmentActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PerformanceResult extends Model
{
    public const STATUSES = ['DRAFT', 'SUBMITTED', 'VERIFIED', 'RETURNED', 'REJECTED'];

    protected $fillable = [
        'performance_indicator_id', 'performance_target_id', 'reporting_period_id', 'department_id', 'programme_id', 'project_id', 'team_member_id',
        'actual_value', 'achievement_percentage', 'score', 'status', 'source_type', 'source_id', 'entered_by_user_id', 'verified_by_user_id', 'verified_at', 'comments',
    ];

    protected function casts(): array
    {
        return ['actual_value' => 'decimal:4', 'achievement_percentage' => 'decimal:2', 'score' => 'decimal:2', 'verified_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $result): void {
            if ($result->exists && $result->getOriginal('status') === 'VERIFIED' && $result->isDirty()) {
                throw ValidationException::withMessages(['status' => 'Verified KPI results are immutable. Create a reviewed revision to correct this result.']);
            }
            if ($result->status === 'VERIFIED' && (! $result->verified_by_user_id || ! $result->verified_at)) {
                throw ValidationException::withMessages(['status' => 'A verified result must record its reviewer and verification time.']);
            }
            if ($result->performance_target_id) {
                $target = PerformanceTarget::find($result->performance_target_id);
                if (! $target || (int) $target->performance_indicator_id !== (int) $result->performance_indicator_id || (int) $target->reporting_period_id !== (int) $result->reporting_period_id) {
                    throw ValidationException::withMessages(['performance_target_id' => 'The selected target must belong to the selected KPI and reporting period.']);
                }
            }
        });

        static::created(fn (self $result) => DepartmentActivity::record($result->department_id, auth()->id(), 'performance.result_created', $result, ['status' => $result->status]));
        static::updated(function (self $result): void {
            if ($result->wasChanged('status')) DepartmentActivity::record($result->department_id, auth()->id(), 'performance.result_status_changed', $result, ['status' => $result->status]);
        });
    }

    public function indicator(): BelongsTo { return $this->belongsTo(PerformanceIndicator::class, 'performance_indicator_id'); }
    public function target(): BelongsTo { return $this->belongsTo(PerformanceTarget::class, 'performance_target_id'); }
    public function reportingPeriod(): BelongsTo { return $this->belongsTo(ReportingPeriod::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function programme(): BelongsTo { return $this->belongsTo(Programme::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function teamMember(): BelongsTo { return $this->belongsTo(TeamMember::class); }
    public function enteredBy(): BelongsTo { return $this->belongsTo(User::class, 'entered_by_user_id'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_user_id'); }
}

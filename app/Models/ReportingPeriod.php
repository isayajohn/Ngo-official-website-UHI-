<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class ReportingPeriod extends Model
{
    protected $fillable = ['name', 'period_type', 'financial_year_id', 'start_date', 'end_date', 'submission_deadline', 'data_entry_start', 'data_entry_deadline', 'verification_deadline', 'status'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'submission_deadline' => 'date', 'data_entry_start' => 'date', 'data_entry_deadline' => 'date', 'verification_deadline' => 'date'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $period): void {
            if ($period->end_date?->lt($period->start_date)) {
                throw ValidationException::withMessages(['end_date' => 'The period end date must be on or after its start date.']);
            }
            if ($period->submission_deadline && $period->submission_deadline->lt($period->start_date)) {
                throw ValidationException::withMessages(['submission_deadline' => 'The submission deadline must be on or after the period start date.']);
            }
            foreach (['data_entry_start', 'data_entry_deadline', 'verification_deadline'] as $field) {
                if ($period->{$field} && $period->{$field}->lt($period->start_date)) throw ValidationException::withMessages([$field => 'The date must be on or after the reporting period start date.']);
            }
            if ($period->data_entry_deadline && $period->data_entry_start && $period->data_entry_deadline->lt($period->data_entry_start)) throw ValidationException::withMessages(['data_entry_deadline' => 'The data entry deadline must be on or after data entry opens.']);
            if ($period->verification_deadline && $period->data_entry_deadline && $period->verification_deadline->lt($period->data_entry_deadline)) throw ValidationException::withMessages(['verification_deadline' => 'The verification deadline must be on or after the data entry deadline.']);
        });
        static::created(fn (self $period) => \App\Services\DepartmentActivity::record(null, auth()->id(), 'performance.period_created', $period));
        static::updated(fn (self $period) => $period->wasChanged() ? \App\Services\DepartmentActivity::record(null, auth()->id(), 'performance.period_changed', $period, ['changed' => array_keys($period->getChanges())]) : null);
    }

    public function financialYear(): BelongsTo { return $this->belongsTo(FinancialYear::class); }
    public function targets(): HasMany { return $this->hasMany(PerformanceTarget::class); }
    public function results(): HasMany { return $this->hasMany(PerformanceResult::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class FinancialYear extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'status', 'closed_at', 'closed_by_user_id'];
    protected function casts(): array { return ['start_date' => 'date', 'end_date' => 'date', 'closed_at' => 'datetime']; }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by_user_id'); }

    protected static function booted(): void
    {
        static::saving(function (FinancialYear $year): void {
            if ($year->end_date?->lt($year->start_date)) throw ValidationException::withMessages(['end_date' => 'The financial year end must be on or after its start.']);
            if (auth()->check() && $year->isDirty('status')) {
                if ($year->status === 'CLOSED') abort_unless(auth()->user()->can('finance.year.close'), 403);
                elseif ($year->getOriginal('status') === 'CLOSED') abort_unless(auth()->user()->can('finance.year.reopen'), 403);
            }
            if ($year->status === 'CLOSED' && ! $year->closed_at) $year->closed_at = now();
            if ($year->status === 'CLOSED' && auth()->check() && ! $year->closed_by_user_id) $year->closed_by_user_id = auth()->id();
            if ($year->status !== 'CLOSED') { $year->closed_at = null; $year->closed_by_user_id = null; }
        });
        static::created(fn (FinancialYear $year) => \App\Services\FinanceAudit::record($year, 'created', ['name' => $year->name, 'status' => $year->status]));
        static::updated(function (FinancialYear $year): void { if ($year->wasChanged()) \App\Services\FinanceAudit::record($year, 'updated', ['changed' => array_keys($year->getChanges())]); });
    }
}

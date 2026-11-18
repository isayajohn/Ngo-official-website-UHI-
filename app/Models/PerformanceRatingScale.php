<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PerformanceRatingScale extends Model
{
    protected $fillable = ['name', 'minimum_score', 'maximum_score', 'label', 'description', 'display_order', 'active'];
    protected function casts(): array { return ['minimum_score' => 'decimal:2', 'maximum_score' => 'decimal:2', 'display_order' => 'integer', 'active' => 'boolean']; }

    protected static function booted(): void
    {
        static::saving(function (self $scale): void {
            if ((float) $scale->minimum_score > (float) $scale->maximum_score) {
                throw ValidationException::withMessages(['maximum_score' => 'The maximum score must be at least the minimum score.']);
            }
        });
        static::created(fn (self $scale) => \App\Services\DepartmentActivity::record(null, auth()->id(), 'performance.rating_scale_created', $scale));
        static::updated(fn (self $scale) => $scale->wasChanged() ? \App\Services\DepartmentActivity::record(null, auth()->id(), 'performance.rating_scale_changed', $scale, ['changed' => array_keys($scale->getChanges())]) : null);
    }
}

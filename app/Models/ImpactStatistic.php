<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ImpactStatistic extends Model
{
    use HasTranslatedContent, SoftDeletes;

    public const METRICS = [
        'people_reached' => 'People reached',
        'communities_supported' => 'Communities supported',
        'health_workers_trained' => 'Health workers trained',
        'regions_covered' => 'Regions covered',
        'staff_members' => 'Staff members',
        'health_facilities' => 'Health facilities',
        'projects' => 'Projects',
        'partners' => 'Partners',
        'other' => 'Other indicator',
    ];

    public const HOMEPAGE_METRICS = ['regions_covered', 'staff_members', 'health_facilities', 'projects'];

    protected $fillable = ['programme_id', 'project_id', 'metric', 'label', 'value', 'suffix', 'icon', 'as_of_date', 'verification_source', 'display_order', 'is_active'];

    protected $attributes = ['value' => 0, 'suffix' => '', 'icon' => 'users', 'display_order' => 0, 'is_active' => false];

    protected function casts(): array
    {
        return ['label' => 'array', 'value' => 'integer', 'as_of_date' => 'date', 'verified_at' => 'datetime', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (ImpactStatistic $stat) {
            if ($stat->project_id) {
                $programmeId = Project::whereKey($stat->project_id)->value('programme_id');
                if (! $programmeId || ($stat->programme_id && (int) $stat->programme_id !== (int) $programmeId)) {
                    throw ValidationException::withMessages(['data.project_id' => 'The project must belong to the selected programme.']);
                }
                $stat->programme_id = $programmeId;
            }
            if ($stat->getOriginal('verified_at') && $stat->isDirty(['label', 'metric', 'value', 'suffix', 'programme_id', 'project_id', 'as_of_date', 'verification_source'])) {
                $stat->verified_at = null;
                $stat->verified_by = null;
                $stat->is_active = false;
            }
            if ($stat->is_active && ! $stat->verified_at) {
                throw ValidationException::withMessages(['data.is_active' => 'Save and verify this indicator before publishing it.']);
            }
        });
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verify(User $user): void
    {
        Gate::forUser($user)->authorize('verify', $this);
        if (! filled($this->verification_source) || ! $this->as_of_date || $this->as_of_date->isFuture()) {
            throw ValidationException::withMessages(['data.verification_source' => 'Add a verification source and an as-of date that is not in the future.']);
        }
        $this->forceFill(['verified_at' => now(), 'verified_by' => $user->id])->save();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNotNull('verified_at')->whereNotNull('as_of_date')->whereDate('as_of_date', '<=', today())
            ->where(fn ($q) => $q->whereNull('programme_id')->orWhereHas('programme', fn ($p) => $p->where('is_active', true)))
            ->where(fn ($q) => $q->whereNull('project_id')->orWhereHas('project', fn ($p) => $p->published()));
    }

    public function displayNumber(): string
    {
        $divisor = match ($this->suffix) {
            'K+' => 1000, 'M+' => 1000000, default => 1
        };
        $value = $this->value / $divisor;

        return number_format($value, $divisor === 1 || floor($value) === $value ? 0 : 1);
    }
}

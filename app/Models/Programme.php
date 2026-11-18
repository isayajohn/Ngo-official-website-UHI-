<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Programme extends Model
{
    use SoftDeletes;

    protected $fillable = ['slug', 'title', 'short_description', 'description', 'image', 'image_alt', 'icon', 'display_order', 'is_active', 'meta_title', 'meta_description'];

    protected function casts(): array
    {
        return ['title' => 'array', 'short_description' => 'array', 'description' => 'array', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Programme $programme) {
            $base = Str::slug($programme->slug ?: ($programme->title['en'] ?? 'programme'));
            $slug = $base;
            $suffix = 2;
            while (static::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            $programme->slug = $slug;
        });
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function impactStatistics(): HasMany
    {
        return $this->hasMany(ImpactStatistic::class);
    }

    public function performanceIndicators(): HasMany { return $this->hasMany(PerformanceIndicator::class); }
    public function performanceTargets(): HasMany { return $this->hasMany(PerformanceTarget::class); }
    public function performanceResults(): HasMany { return $this->hasMany(PerformanceResult::class); }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function translated(string $field): string
    {
        $translations = $this->getAttribute($field) ?? [];

        return filled($translations[app()->getLocale()] ?? null)
            ? $translations[app()->getLocale()]
            : ($translations['en'] ?? '');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('display_order')->orderBy('id');
    }
}

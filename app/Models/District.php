<?php

namespace App\Models;

use App\Models\Concerns\HasPublicSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class District extends Model
{
    use HasPublicSlug, SoftDeletes;

    protected $fillable = ['region_id', 'name', 'slug', 'display_order', 'is_active'];

    protected $attributes = ['is_active' => true, 'display_order' => 0];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (District $district) {
            if ($district->exists && $district->isDirty('region_id') && Project::withTrashed()->where('district_id', $district->id)->where('region_id', '!=', $district->region_id)->exists()) {
                throw ValidationException::withMessages(['data.region_id' => 'This district is used by projects. Update their locations before moving it to another region.']);
            }
        });
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereHas('region', fn ($q) => $q->published())->orderBy('display_order')->orderBy('name');
    }
}

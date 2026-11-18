<?php

namespace App\Models;

use App\Models\Concerns\HasPublicSlug;
use App\Models\Concerns\HasTranslatedContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasPublicSlug, HasTranslatedContent, SoftDeletes;

    protected $fillable = ['country_id', 'name', 'slug', 'description', 'display_order', 'is_active'];

    protected $attributes = ['is_active' => true, 'display_order' => 0];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'description' => 'array'];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereHas('country', fn ($q) => $q->published())->orderBy('display_order')->orderBy('name');
    }
}

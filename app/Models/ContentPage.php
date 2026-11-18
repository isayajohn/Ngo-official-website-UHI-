<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentPage extends Model
{
    use HasFactory, HasTranslatedContent, SoftDeletes;

    protected $fillable = ['path', 'eyebrow', 'title', 'description', 'body', 'hero_image', 'hero_image_alt', 'meta_title', 'meta_description', 'og_image', 'is_published', 'display_order'];

    protected function casts(): array
    {
        return ['title' => 'array', 'description' => 'array', 'body' => 'array', 'is_published' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}

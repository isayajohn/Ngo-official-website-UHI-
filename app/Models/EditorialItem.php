<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EditorialItem extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'news' => 'News',
        'event' => 'Event',
        'publication' => 'Publication',
        'gallery' => 'Gallery',
    ];

    protected $fillable = [
        'type', 'title', 'slug', 'summary', 'body', 'image', 'image_alt', 'document', 'document_label',
        'external_url', 'event_starts_at', 'event_ends_at', 'event_location', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return ['event_starts_at' => 'datetime', 'event_ends_at' => 'datetime', 'published_at' => 'datetime', 'is_published' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (EditorialItem $item): void {
            if (blank($item->slug)) {
                $base = Str::slug($item->title) ?: 'item';
                $slug = $base;
                $suffix = 2;
                while (static::withTrashed()->where('slug', $slug)->when($item->exists, fn (Builder $query) => $query->whereKeyNot($item->getKey()))->exists()) {
                    $slug = $base.'-'.$suffix++;
                }
                $item->slug = $slug;
            }

            if ($item->is_published && ! $item->published_at) {
                $item->published_at = now();
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}

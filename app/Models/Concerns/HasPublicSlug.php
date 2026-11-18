<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPublicSlug
{
    protected static function bootHasPublicSlug(): void
    {
        static::creating(function ($record) {
            $base = Str::slug($record->slug ?: ($record->name ?: ($record->title['en'] ?? class_basename($record))));
            $base = $base ?: Str::lower(class_basename($record));
            $slug = $base;
            $suffix = 2;
            while (static::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            $record->slug = $slug;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

<?php

namespace App\Models\Concerns;

trait HasTranslatedContent
{
    public function translated(string $field): string
    {
        $translations = $this->getAttribute($field) ?? [];

        return filled($translations[app()->getLocale()] ?? null) ? $translations[app()->getLocale()] : ($translations['en'] ?? '');
    }
}

<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class SiteSettings
{
    private ?array $data = null;

    public function all(): array
    {
        return $this->data ??= array_replace(config('ughi.defaults'), SiteSetting::where('key', 'global')->first()?->data ?? []);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function image(string $key, string $fallback = ''): string
    {
        $path = $this->get($key);

        return $path ? Storage::disk('public')->url($path) : asset($fallback);
    }
}

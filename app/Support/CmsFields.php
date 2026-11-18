<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CmsFields
{
    public static function image(string $name): FileUpload
    {
        return FileUpload::make($name)->disk('public')->visibility('public')->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->rules(['extensions:jpg,jpeg,png,webp'])->maxSize(5120)
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageStorage::class)->store($file))
            ->placeholder('Click the button below to upload<br><span class="filepond--label-action">Browse file</span>')
            ->helperText('JPEG, PNG or WebP. Maximum 5 MB / 16 megapixels; optimized to WebP at up to 1920px.');
    }
}

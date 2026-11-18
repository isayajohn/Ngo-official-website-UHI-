<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CmsFields
{
    public static function favicon(): FileUpload
    {
        return FileUpload::make('favicon')->disk('public')->visibility('public')->directory('site/favicons')
            ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon'])
            ->rules(['extensions:ico', 'mimetypes:image/x-icon,image/vnd.microsoft.icon'])
            ->maxSize(1024)->helperText('ICO only. Recommended sizes: 16×16, 32×32, and 48×48 pixels.');
    }

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

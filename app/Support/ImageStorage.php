<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageStorage
{
    public function store(UploadedFile $file): string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowed)) {
            throw ValidationException::withMessages(['data' => 'Please choose a JPEG, PNG or WebP image.']);
        }
        $size = getimagesize($file->getRealPath());
        if (! $size || ! in_array($size['mime'], $allowed) || $size[0] * $size[1] > 16000000) {
            throw ValidationException::withMessages(['data' => 'Please choose an image under 16 megapixels.']);
        }
        $source = match ($size['mime']) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
        };
        $path = 'images/'.Str::uuid().'.webp';
        foreach ([1920, 960, 480] as $maximum) {
            $ratio = min(1, $maximum / max($size[0], $size[1]));
            $image = imagecreatetruecolor(max(1, (int) round($size[0] * $ratio)), max(1, (int) round($size[1] * $ratio)));
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagecopyresampled($image, $source, 0, 0, 0, 0, imagesx($image), imagesy($image), $size[0], $size[1]);
            ob_start();
            imagewebp($image, null, 82);
            $bytes = ob_get_clean();
            imagedestroy($image);
            Storage::disk('public')->put($maximum === 1920 ? $path : self::thumbnail($path, $maximum), $bytes);
        }
        imagedestroy($source);

        return $path;
    }

    public static function thumbnail(string $path, int $maximum = 960): string
    {
        return preg_replace('/\.webp$/', '-'.$maximum.'.webp', $path);
    }
}

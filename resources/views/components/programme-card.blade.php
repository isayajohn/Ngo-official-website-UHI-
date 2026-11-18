@props(['programme'])
@php
$fallbackImages = ['images/community-care.jpg', 'images/listening-care.jpg'];
$backgroundImage = $programme->image
    ? Storage::disk('public')->url(\App\Support\ImageStorage::thumbnail($programme->image))
    : asset($fallbackImages[max(0, ((int) $programme->display_order - 1)) % count($fallbackImages)]);
@endphp
<article class="programme-tile group flex h-full min-w-0 flex-col overflow-hidden rounded-2xl bg-white">
<div class="programme-media relative"><img src="{{ $backgroundImage }}" alt="{{ $programme->image_alt ?: $programme->translated('title') }}" width="900" height="600" loading="lazy" class="aspect-[16/9] w-full object-cover transition duration-500 group-hover:scale-[1.04]"><span class="programme-icon"><x-ughi-icon :name="$programme->icon" class="h-6 w-6"/></span></div>
<div class="flex flex-1 flex-col p-6 pt-9"><h3 class="min-h-[3.25rem] text-xl leading-snug font-bold tracking-[-0.02em]">{{ $programme->translated('title') }}</h3><p class="mt-3 flex-1 text-[15px] leading-7 text-muted">{{ $programme->translated('short_description') }}</p><a href="{{ route('programmes.show', $programme) }}" class="text-link mt-5" aria-label="Learn more about {{ $programme->translated('title') }}">Learn More<x-ughi-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></a></div>
</article>

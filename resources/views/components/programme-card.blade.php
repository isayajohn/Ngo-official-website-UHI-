@props(['programme'])
@php
$fallbackImages = ['images/community-care.jpg', 'images/listening-care.jpg', 'images/tanzania-regions.svg'];
$backgroundImage = $programme->image
    ? Storage::disk('public')->url(\App\Support\ImageStorage::thumbnail($programme->image))
    : asset($fallbackImages[max(0, ((int) $programme->display_order - 1)) % count($fallbackImages)]);
@endphp
<article class="group relative isolate flex min-h-[370px] h-full overflow-hidden rounded-[22px] border border-white/30 bg-secondary shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_40px_rgb(18_59_45_/_14%)]">
<div class="absolute inset-0 -z-20 bg-cover bg-center transition duration-700 ease-out group-hover:scale-105" role="img" aria-label="{{ $programme->image_alt ?: $programme->translated('title') }}" style="background-image: url('{{ $backgroundImage }}');"></div>
<div class="absolute inset-0 -z-10 bg-gradient-to-t from-secondary via-secondary/75 to-secondary/15"></div>
<div class="flex min-h-[370px] w-full flex-col justify-between p-6 text-white sm:p-7">
<div class="flex items-start justify-between gap-4"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[14px] border border-white/20 bg-white/15 text-white backdrop-blur-sm"><x-ughi-icon :name="$programme->icon" class="h-6 w-6" /></span><span class="font-heading text-xs font-semibold tracking-[0.16em] text-white/70">{{ str_pad($programme->display_order, 2, '0', STR_PAD_LEFT) }}</span></div>
<div><h3 class="text-xl leading-[1.3] font-bold tracking-[-0.025em] text-white">{{ $programme->translated('title') }}</h3><p class="mt-3 max-w-[34rem] text-sm leading-6 text-white/80">{{ $programme->translated('short_description') }}</p><a href="{{ route('programmes.show', $programme) }}" class="mt-6 inline-flex items-center gap-2 rounded-lg border border-white/35 bg-white/10 px-3.5 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white hover:text-primary-dark" aria-label="Learn more about {{ $programme->translated('title') }}">Learn More<x-ughi-icon class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" /></a></div>
</div>
</article>

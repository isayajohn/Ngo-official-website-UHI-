<x-layout :title="$title" :description="$description">
<x-page-hero eyebrow="{{ strtoupper($title) }}" :title="$title" :description="$description" />
<section class="section"><div class="wrap">
@if($items->count())
<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
@foreach($items as $item)
<article class="card overflow-hidden">
@if($item->image)<img src="{{ Storage::disk('public')->url(\App\Support\ImageStorage::thumbnail($item->image)) }}" alt="{{ $item->image_alt ?: $item->title }}" loading="lazy" class="aspect-[3/2] w-full object-cover">@endif
<div class="p-6">
@if($type === 'event' && $item->event_starts_at)<p class="mb-3 text-xs font-semibold text-primary">{{ $item->event_starts_at->format('M j, Y · g:i A') }}@if($item->event_location) · {{ $item->event_location }}@endif</p>@else<p class="mb-3 text-xs font-semibold text-primary">{{ $item->published_at?->format('M j, Y') }}</p>@endif
<h2 class="text-xl font-bold leading-snug">{{ $item->title }}</h2>
@if($item->summary)<p class="mt-3 line-clamp-3 text-sm leading-6 text-muted">{{ $item->summary }}</p>@endif
@if($type === 'publication' && ($item->document || $item->external_url))<a href="{{ $item->document ? Storage::disk('public')->url($item->document) : $item->external_url }}" target="_blank" rel="noopener" class="text-link mt-5">{{ $item->document_label ?: 'View publication' }}<x-ughi-icon name="arrow-up-right" class="h-4 w-4" /></a>@elseif($type !== 'gallery')<a href="{{ url('/'.request()->path().'/'.$item->slug) }}" class="text-link mt-5">Read more<x-ughi-icon name="arrow-right" class="h-4 w-4" /></a>@elseif($item->image)<a href="{{ Storage::disk('public')->url($item->image) }}" target="_blank" rel="noopener" class="text-link mt-5">View photo<x-ughi-icon name="arrow-up-right" class="h-4 w-4" /></a>@endif
</div></article>
@endforeach
</div><div class="mt-10">{{ $items->links() }}</div>
@else<x-empty-state :title="$emptyTitle" :description="$emptyDescription" />@endif
</div></section></x-layout>

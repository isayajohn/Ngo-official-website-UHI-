<x-layout :title="$item->title" :description="$item->summary" :image="$image">
<x-page-hero :eyebrow="strtoupper(\App\Models\EditorialItem::TYPES[$item->type])" :title="$item->title" :description="$item->summary" />
<section class="section"><article class="wrap max-w-4xl">
@if($item->type === 'event' && $item->event_starts_at)<p class="mb-6 text-sm font-semibold text-primary">{{ $item->event_starts_at->format('l, F j, Y · g:i A') }}@if($item->event_ends_at) – {{ $item->event_ends_at->format('g:i A') }}@endif@if($item->event_location) · {{ $item->event_location }}@endif</p>@endif
@if($image)<img src="{{ $image }}" alt="{{ $item->image_alt ?: $item->title }}" class="mb-10 max-h-[560px] w-full rounded-[24px] object-cover">@endif
@if($item->body)<div class="whitespace-pre-line text-lg leading-8 text-muted">{{ $item->body }}</div>@endif
@if($item->type === 'publication' && ($item->document || $item->external_url))<a class="btn btn-primary mt-8" href="{{ $item->document ? Storage::disk('public')->url($item->document) : $item->external_url }}" target="_blank" rel="noopener">{{ $item->document_label ?: 'View publication' }}<x-ughi-icon name="arrow-up-right" class="h-4 w-4" /></a>@endif
<a href="{{ url('/'.request()->segment(1).(request()->segment(1) === 'resources' ? '/publications' : '')) }}" class="text-link mt-10">Back to {{ \App\Models\EditorialItem::TYPES[$item->type] }}<x-ughi-icon name="arrow-left" class="h-4 w-4" /></a>
</article></section></x-layout>

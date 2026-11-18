<x-layout title="Partners" description="Organizations working with UGHI for healthier communities.">
<x-page-hero eyebrow="OUR PARTNERS" title="Working together for healthier communities." description="UGHI works with organizations that share a commitment to community-led, equitable health."/>
<section class="section"><div class="wrap">
@if($partners->isNotEmpty())
<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
@foreach($partners as $partner)<article class="card flex h-full flex-col p-6">
    <div class="flex h-28 items-center justify-center rounded-2xl bg-canvas p-5">@if($partner->logo)<img src="{{ Storage::disk('public')->url($partner->logo) }}" alt="{{ $partner->logo_alt ?: $partner->name }}" class="max-h-20 max-w-full object-contain">@elseif($partner->featured_image)<img src="{{ Storage::disk('public')->url($partner->featured_image) }}" alt="{{ $partner->image_alt ?: $partner->name }}" class="h-full w-full rounded-xl object-cover">@else<x-ughi-icon name="users" class="h-10 w-10 text-primary"/>@endif</div>
    @if($partner->partner_type)<p class="eyebrow mt-6">{{ $partner->partner_type }}</p>@endif
    <h2 class="mt-2 text-xl font-bold">{{ $partner->name }}</h2>
    @if($partner->short_description)<p class="mt-3 text-sm leading-7 text-muted">{{ $partner->short_description }}</p>@endif
    <a href="{{ route('partners.show', $partner) }}" class="text-link mt-auto pt-7">View partnership<x-ughi-icon class="h-4 w-4"/></a>
</article>@endforeach
</div>
@else
<x-empty-state title="Partners are being added" description="UGHI partnership information will be published here as collaborations are confirmed."/>
@endif
</div></section></x-layout>

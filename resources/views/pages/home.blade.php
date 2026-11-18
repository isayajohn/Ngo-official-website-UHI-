<x-layout>
@php
$heroSlides = [
    ['image' => $site->image('hero_image', 'images/community-care.jpg'), 'alt' => $site->get('hero_image_alt') ?: 'A healthcare professional in conversation with a patient', 'title' => (($heroCaption = $site->get('hero_image_caption')) && ! str_starts_with($heroCaption, 'Illustrative photography')) ? $heroCaption : 'Better health, stronger communities'],
    ['image' => $site->image('hero_image_2', 'images/listening-care.jpg'), 'alt' => $site->get('hero_image_2_alt') ?: 'Community volunteers working together', 'title' => $site->get('hero_image_2_caption') ?: 'Community-led action for lasting change'],
];
if ($site->get('hero_image_3')) {
    $heroSlides[] = ['image' => $site->image('hero_image_3'), 'alt' => $site->get('hero_image_3_alt') ?: 'Community health work in Tanzania', 'title' => $site->get('hero_image_3_caption') ?: 'Health and wellbeing for all'];
}
$values = [
    ['Community-led', 'Communities help shape and guide our work.', 'users'],
    ['Evidence-based', 'We use learning and evidence to strengthen our work.', 'book'],
    ['Inclusive', 'We work to reach people fairly and respectfully.', 'heart'],
    ['Accountable', 'We act with care, integrity and responsibility.', 'shield'],
];
@endphp
<section x-data="heroCarousel({{ count($heroSlides) }})" @mouseenter="stop()" @mouseleave="if (!reducedMotion) start()" @focusin="stop()" @focusout="if (!reducedMotion) start()" @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd($event)" @keydown.left.window="previous()" @keydown.right.window="next()" class="hero-home relative isolate flex min-h-[560px] items-center overflow-hidden bg-secondary lg:min-h-[640px]">
    <div class="hero-carousel-stage" role="region" aria-roledescription="carousel" aria-label="Featured UHI stories" aria-live="polite">
        @foreach($heroSlides as $index => $slide)
        <div class="hero-carousel-card" :class="active === {{ $index }} ? 'hero-carousel-card--active' : 'hero-carousel-card--previous'" :aria-hidden="active !== {{ $index }}">
            <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}" width="1920" height="900" {{ $index === 0 ? 'fetchpriority=high' : 'loading=lazy' }} class="hero-carousel-card__image">
            <div class="hero-carousel-card__overlay" aria-hidden="true"></div>
        </div>
        @endforeach
    </div>
    <div class="wrap relative z-10 w-full pb-24 pt-20 text-white">
        <div class="max-w-[650px]">
            <p class="mb-5 text-xs font-bold tracking-[0.2em] text-white/85">{{ $site->get('hero_eyebrow') }}</p>
            <h1 class="max-w-[620px] whitespace-pre-line text-4xl leading-[1.08] font-extrabold tracking-[-0.045em] sm:text-5xl lg:text-[58px]">{{ $site->get('hero_title') }}</h1>
            <p class="mt-6 max-w-[590px] text-base leading-7 text-white/90 sm:text-lg sm:leading-8">{{ $site->get('hero_description') }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $site->get('hero_primary_url') }}" class="btn btn-accent">{{ $site->get('hero_primary_label') }}<x-ughi-icon class="h-4 w-4"/></a>
                <a href="{{ $site->get('hero_secondary_url') }}" class="btn border border-white/80 text-white hover:bg-white hover:text-primary-dark">{{ $site->get('hero_secondary_label') }}</a>
            </div>
        </div>
        <div class="absolute right-4 bottom-7 flex items-center gap-3 sm:right-6 lg:right-8">
            <button type="button" @click="previous()" class="hero-carousel-control" aria-label="Previous featured story"><x-ughi-icon name="arrow-left" class="h-4 w-4"/></button>
            <div class="flex items-center gap-2" role="tablist" aria-label="Featured stories">
                @foreach($heroSlides as $index => $slide)
                <button type="button" @click="go({{ $index }})" :aria-selected="active === {{ $index }}" role="tab" class="hero-carousel-dot" :class="active === {{ $index }} ? 'w-8 bg-accent' : 'w-2.5 bg-white/60 hover:bg-white'" aria-label="Show featured story {{ $index + 1 }}"></button>
                @endforeach
            </div>
            <button type="button" @click="next()" class="hero-carousel-control" aria-label="Next featured story"><x-ughi-icon name="arrow-right" class="h-4 w-4"/></button>
        </div>
    </div>
</section>

@if($impactStatistics->isNotEmpty())
<section class="impact-band" aria-label="Verified impact">
    <div class="wrap grid grid-cols-2 gap-6 py-10 sm:grid-cols-2 lg:grid-cols-4 lg:py-14">
        @foreach($impactStatistics as $statistic)<x-impact-stat :statistic="$statistic" :context="false" dark class="impact-stat"/>@endforeach
    </div>
</section>
@endif

<section class="section" id="about">
    <div class="wrap grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
        <div class="about-photo-frame"><img src="{{ $site->image('about_image', 'images/listening-care.jpg') }}" alt="{{ $site->get('about_image_alt') ?: 'Community members working together on health' }}" width="1000" height="760" loading="lazy" class="relative aspect-[4/3] w-full rounded-2xl object-cover"></div>
        <div><x-section-heading :eyebrow="$site->get('about_eyebrow')" :title="$site->get('about_title')" :description="$site->get('about_description')"/><p class="mt-5 max-w-2xl text-sm leading-7 text-muted">{{ $site->get('footer_about') }}</p><a href="/about" class="btn btn-primary mt-7">Read More<x-ughi-icon class="h-4 w-4"/></a></div>
    </div>
</section>

@if($programmes->isNotEmpty())
<section class="section bg-canvas" id="programmes">
    <div class="wrap">
        <div class="mb-10 text-center"><p class="eyebrow justify-center">{{ $site->get('programmes_eyebrow') }}</p><h2 class="heading">{{ $site->get('programmes_title') }}</h2><p class="mx-auto mt-4 max-w-2xl text-muted">{{ $site->get('programmes_description') }}</p></div>
        <div class="grid items-stretch gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($programmes->take(3) as $programme)<x-programme-card :programme="$programme"/>@endforeach</div>
        <div class="mt-10 text-center"><a href="/programmes" class="btn btn-primary">View All Programmes<x-ughi-icon class="h-4 w-4"/></a></div>
    </div>
</section>
@endif

<section class="section values-section">
    <div class="wrap">
        <div class="mb-10 text-center"><p class="eyebrow justify-center">WHAT GUIDES US</p><h2 class="heading">Vision, Mission &amp; Values</h2><p class="mx-auto mt-4 max-w-2xl text-muted">The principles that guide our work with communities across Tanzania.</p></div>
        <div class="grid gap-7 lg:grid-cols-[.85fr_1.15fr]">
            <div class="mission-panel rounded-2xl p-7 text-white sm:p-9">
                <div class="border-b border-white/20 pb-6"><div class="flex items-center gap-3"><span class="value-icon"><x-ughi-icon name="globe" class="h-5 w-5"/></span><h3 class="text-xl font-bold">Our Vision</h3></div><p class="mt-4 pl-14 leading-7 text-white/90">{{ $site->get('vision') }}</p></div>
                <div class="pt-6"><div class="flex items-center gap-3"><span class="value-icon"><x-ughi-icon name="heart" class="h-5 w-5"/></span><h3 class="text-xl font-bold">Our Mission</h3></div><p class="mt-4 pl-14 leading-7 text-white/90">{{ $site->get('mission') }}</p></div>
            </div>
            <div><h3 class="mb-4 flex items-center gap-3 text-lg font-bold"><span class="h-1 w-7 rounded-full bg-accent"></span>Our Core Values</h3><div class="grid gap-4 sm:grid-cols-2">
                @foreach($values as [$title, $description, $icon])<article class="value-card rounded-2xl bg-white p-5"><span class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><x-ughi-icon :name="$icon" class="h-5 w-5"/></span><h4 class="font-bold">{{ $title }}</h4><p class="mt-2 text-sm leading-6 text-muted">{{ $description }}</p></article>@endforeach
            </div></div>
        </div>
    </div>
</section>

@if($stories->isNotEmpty())
<section class="section">
    <div class="wrap">
        <div class="mb-9 flex flex-wrap items-end justify-between gap-5"><div><p class="eyebrow">FROM OUR COMMUNITIES</p><h2 class="heading">Latest News &amp; Stories</h2><p class="mt-3 text-muted">Updates from UHI and the communities we work alongside.</p></div><a href="/news" class="text-link">View All News<x-ughi-icon name="arrow-right" class="h-4 w-4"/></a></div>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($stories as $story)
            <article class="news-card overflow-hidden rounded-2xl bg-white">
                @if($story->image)<img src="{{ Storage::disk('public')->url(\App\Support\ImageStorage::thumbnail($story->image)) }}" alt="{{ $story->image_alt ?: $story->title }}" loading="lazy" class="aspect-[3/2] w-full object-cover">@else<img src="{{ asset('images/community-care.jpg') }}" alt="Community health work" loading="lazy" class="aspect-[3/2] w-full object-cover">@endif
                <div class="p-6"><p class="mb-3 text-xs font-bold uppercase tracking-wide text-primary">{{ $story->published_at?->format('M d, Y') }}</p><h3 class="text-lg leading-snug font-bold">{{ $story->title }}</h3>@if($story->summary)<p class="mt-3 line-clamp-3 text-sm leading-6 text-muted">{{ $story->summary }}</p>@endif<a href="{{ route('news.show', $story->slug) }}" class="text-link mt-5">Read More<x-ughi-icon name="arrow-right" class="h-4 w-4"/></a></div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($featuredProject)<section class="section bg-canvas"><div class="wrap grid items-center gap-10 md:grid-cols-2 lg:gap-16"><div><p class="eyebrow">FEATURED PROJECT</p><p class="mb-3 text-xs font-semibold text-primary">{{ $featuredProject->programme->translated('title') }}</p><h2 class="heading">{{ $featuredProject->translated('title') }}</h2>@if($featuredProject->locationLabel())<p class="mt-5 flex items-center gap-2 text-sm text-muted"><x-ughi-icon name="pin" class="h-4 w-4"/>{{ $featuredProject->locationLabel() }}</p>@endif<p class="mt-5 leading-8 text-muted">{{ $featuredProject->translated('short_description') }}</p><a href="{{ route('projects.show', $featuredProject) }}" class="btn btn-primary mt-7">View Project<x-ughi-icon class="h-4 w-4"/></a></div>@if($featuredProject->featured_image)<img src="{{ Storage::disk('public')->url($featuredProject->featured_image) }}" alt="{{ $featuredProject->image_alt ?? '' }}" width="1200" height="900" loading="lazy" class="aspect-[4/3] w-full rounded-2xl object-cover">@endif</div></section>@endif

@if($countries->isNotEmpty())<section class="section"><div class="wrap grid items-center gap-12 lg:grid-cols-2 lg:gap-20"><div><x-section-heading eyebrow="WHERE WE WORK" :title="$site->get('location_title')" :description="$site->get('location_description')"/><div class="mt-7"><x-location-summary :countries="$countries"/></div><a href="/where-we-work" class="text-link mt-8">Explore Where We Work<x-ughi-icon class="h-4 w-4"/></a></div>@if($countries->firstWhere('code', 'TZ'))<div class="flex min-h-80 items-center justify-center rounded-2xl bg-canvas p-7"><x-tanzania-map :country="$countries->firstWhere('code', 'TZ')"/></div>@endif</div></section>@endif

<section class="join-band"><div class="wrap flex flex-wrap items-center justify-between gap-6 py-12 text-white md:py-14"><div><p class="mb-3 text-xs font-bold tracking-[0.18em] text-white/75">BE PART OF THE CHANGE</p><h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $site->get('cta_title') }}</h2><p class="mt-3 max-w-2xl text-white/85">{{ $site->get('cta_description') }}</p></div><div class="flex flex-wrap gap-3"><a href="/get-involved" class="btn btn-accent">Get Involved<x-ughi-icon class="h-4 w-4"/></a><a href="/contact" class="btn border border-white/75 text-white hover:bg-white hover:text-primary-dark">Contact Us</a></div></div></section>

@if($partners->isNotEmpty())<section class="section"><div class="wrap"><div class="mb-8 text-center"><p class="eyebrow justify-center">WORKING TOGETHER</p><h2 class="heading">Our Partners</h2><p class="mt-3 text-muted">Working together for healthier communities.</p></div><div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">@foreach($partners as $partner)<a href="{{ route('partners.show', $partner) }}" class="partner-tile flex min-h-24 items-center justify-center rounded-xl bg-white p-5" aria-label="{{ $partner->name }}">@if($partner->logo)<img src="{{ Storage::disk('public')->url($partner->logo) }}" alt="{{ $partner->logo_alt ?: $partner->name }}" class="max-h-14 max-w-full object-contain">@else<span class="text-sm font-semibold text-ink">{{ $partner->name }}</span>@endif</a>@endforeach</div></div></section>@endif
</x-layout>

<x-layout :title="$site->get('about_title')">
    <x-page-hero :eyebrow="$site->get('about_eyebrow') ?: 'WHO WE ARE'" :title="$site->get('about_title')" :description="$site->get('about_description')" />

    <section class="section">
        <div class="wrap grid items-center gap-12 lg:grid-cols-2">
            <div class="about-photo-frame order-2 lg:order-1">
                <img src="{{ $site->image('about_image', 'images/community-care.jpg') }}" alt="{{ $site->get('about_image_alt') ?: 'Community members and health workers collaborating' }}" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-soft">
            </div>
            <div class="order-1 lg:order-2">
                <p class="eyebrow">OUR PURPOSE</p>
                <h2 class="heading">Healthier communities, led by people.</h2>
                <p class="mt-6 text-base leading-8 text-muted">{{ $site->get('goal') }}</p>
                <a href="/our-team" class="btn btn-primary mt-8">Meet our team<x-ughi-icon name="arrow-right" class="h-4 w-4"/></a>
            </div>
        </div>
    </section>

    <section class="bg-canvas py-16 md:py-24">
        <div class="wrap">
            <div class="mx-auto max-w-2xl text-center">
                <p class="eyebrow justify-center">WHAT DRIVES US</p>
                <h2 class="heading">Vision, Mission &amp; Values</h2>
                <p class="mt-4 text-muted">The principles that guide our work with communities.</p>
            </div>
            <div class="mt-12 grid gap-7 lg:grid-cols-[.9fr_1.1fr]">
                <div class="mission-panel rounded-3xl p-8 text-white md:p-10">
                    <div class="border-b border-white/20 pb-7"><p class="text-xs font-bold tracking-[.16em] text-white/70">OUR VISION</p><p class="mt-3 text-xl font-semibold leading-8">{{ $site->get('vision') }}</p></div>
                    <div class="pt-7"><p class="text-xs font-bold tracking-[.16em] text-white/70">OUR MISSION</p><p class="mt-3 leading-7 text-white/90">{{ $site->get('mission') }}</p></div>
                </div>
                <div>
                    <h3 class="mb-5 flex items-center gap-3 text-xl font-bold"><span class="text-accent">✦</span>Our Core Values</h3>
                    @php($aboutValues = collect($site->get('about_values', []))->filter(fn ($value) => filled($value['label'] ?? null)))
                    @if($aboutValues->isNotEmpty())
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($aboutValues as $value)<article class="value-card rounded-2xl bg-white p-6"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary"><x-ughi-icon name="check" class="h-5 w-5"/></span><h4 class="mt-4 font-bold">{{ $value['label'] }}</h4></article>@endforeach
                        </div>
                    @else
                        <x-empty-state title="Our values are being updated" description="Our guiding values will be shared here soon." />
                    @endif
                </div>
            </div>
        </div>
    </section>

    @php($operatingCountries = \App\Models\Country::published()->with(['regions' => fn ($query) => $query->published()])->limit(6)->get())
    @if($operatingCountries->isNotEmpty())
        <section class="bg-canvas py-16 md:py-24">
            <div class="wrap">
                <div class="mx-auto max-w-2xl text-center"><p class="eyebrow justify-center">GEOGRAPHIC COVERAGE</p><h2 class="heading">{{ $site->get('location_title') }}</h2><p class="mt-4 leading-7 text-muted">{{ $site->get('location_description') }}</p></div>
                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($operatingCountries as $country)<article class="card p-6"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><x-ughi-icon name="pin" class="h-5 w-5"/></span><h3 class="text-lg font-bold">{{ $country->name }}</h3></div><div class="mt-5 flex flex-wrap gap-2">@foreach($country->regions as $region)<span class="rounded-full bg-canvas px-3 py-1.5 text-xs font-medium text-muted">{{ $region->name }}</span>@endforeach</div></article>@endforeach
                </div>
                <div class="mt-9 text-center"><a href="/where-we-work" class="text-link">Explore our operating areas<x-ughi-icon name="arrow-right" class="h-4 w-4"/></a></div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="wrap flex flex-col items-start justify-between gap-8 rounded-3xl bg-primary/5 p-8 md:flex-row md:items-center md:p-10">
            <div><p class="eyebrow">OUR JOURNEY</p><h2 class="text-3xl font-bold tracking-[-.035em]">Learn how our story has grown.</h2><p class="mt-3 max-w-2xl leading-7 text-muted">Explore the milestones, experiences and partnerships that have shaped UGHI.</p></div>
            <a href="/our-history" class="btn btn-primary shrink-0">Explore Our History<x-ughi-icon name="arrow-right" class="h-4 w-4"/></a>
        </div>
    </section>
</x-layout>

<x-layout :title="$title">@if($kind === 'contact')<section class="contact-page-hero"><div class="wrap text-center"><h1 class="text-4xl font-extrabold tracking-[-.04em] text-white md:text-5xl">{{ $title ?: 'Contact Us' }}</h1><nav aria-label="Breadcrumb" class="mt-6 flex items-center justify-center gap-3 text-sm"><a href="/" class="text-white/70 hover:text-white">Home</a><span class="text-white/50">/</span><span class="font-medium text-white">{{ $title ?: 'Contact' }}</span></nav></div></section>@else<x-page-hero :eyebrow="$eyebrow ?? 'USTAWI GLOBAL HEALTH INITIATIVE'" :title="$title" :description="$description"/>@endif<section class="section"><div class="wrap">@if(!empty($image))<img src="{{ $image }}" alt="{{ $imageAlt ?? $title }}" class="mb-10 aspect-[2.4/1] w-full rounded-[24px] object-cover">@endif
@if($kind === 'contact')
    @php
        $contactEmail = $site->get('email');
        $contactPhone = $site->get('phone');
        $contactWhatsapp = $site->get('whatsapp');
        $contactAddress = $site->get('physical_address');
        $socialLinks = collect(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'x' => 'X'])->mapWithKeys(fn ($label, $key) => [$label => $site->get($key)])->filter();
        $operatingCountries = \App\Models\Country::published()->with(['regions' => fn ($q) => $q->published()->with(['districts' => fn ($d) => $d->published()])])->limit(6)->get();
        $contactDepartments = \App\Models\Department::query()->where('active', true)->where('accepts_public_messages', true)->orderBy('display_order')->orderBy('name')->get();
    @endphp
    <div class="grid gap-12 lg:grid-cols-[1.25fr_.9fr] lg:items-start">
        <div>
            <p class="eyebrow">GET IN TOUCH</p>
            <h2 class="heading max-w-2xl">Send Us a Message</h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-muted">Have a question or want to partner with us? Fill in the form and our team will get back to you.</p>
            @if(session('contact_success'))<p class="mt-5 rounded-xl bg-primary/10 p-4 text-sm font-semibold text-primary-dark" role="status">{{ session('contact_success') }}</p>@endif
            <form method="POST" action="{{ route('contact.store') }}" class="mt-7 grid gap-5 sm:grid-cols-2">@csrf
                <div class="hidden" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
                <div><label for="contact-name" class="text-sm font-semibold">Full Name <span class="text-accent">*</span></label><input id="contact-name" name="name" value="{{ old('name') }}" required maxlength="160" class="filter-input mt-2" autocomplete="name">@error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="contact-email" class="text-sm font-semibold">Email Address <span class="text-accent">*</span></label><input id="contact-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" class="filter-input mt-2" autocomplete="email">@error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="contact-phone" class="text-sm font-semibold">Phone Number</label><input id="contact-phone" name="phone" value="{{ old('phone') }}" maxlength="50" class="filter-input mt-2" autocomplete="tel"></div>
                <div><label for="contact-subject" class="text-sm font-semibold">Subject</label><input id="contact-subject" name="subject" value="{{ old('subject') }}" maxlength="255" class="filter-input mt-2"></div>
                @if($contactDepartments->isNotEmpty())<div class="sm:col-span-2"><label for="contact-department" class="text-sm font-semibold">Department <span class="font-normal text-muted">(optional)</span></label><select id="contact-department" name="department_id" class="filter-input mt-2"><option value="">General UGHI enquiry</option>@foreach($contactDepartments as $contactDepartment)<option value="{{ $contactDepartment->id }}" @selected(old('department_id') == $contactDepartment->id)>{{ $contactDepartment->name }}</option>@endforeach</select></div>@endif
                <div class="sm:col-span-2"><label for="contact-message" class="text-sm font-semibold">Message <span class="text-accent">*</span></label><textarea id="contact-message" name="message" required maxlength="10000" rows="5" class="filter-input mt-2">{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-4"><p class="max-w-sm text-xs leading-5 text-muted">Please do not include medical records or safeguarding disclosures in this form.</p><button type="submit" class="btn btn-primary">Send Message<x-ughi-icon name="arrow-up-right" class="h-4 w-4"/></button></div>
            </form>
        </div>
        <aside class="contact-office-card">
            <h2 class="text-2xl font-bold tracking-[-.03em]">Head Office</h2>
            @if($contactAddress)<div class="mt-7 flex gap-4"><x-ughi-icon name="pin" class="mt-1 h-5 w-5 shrink-0 text-blue-200"/><div><p class="font-semibold">Address</p><p class="mt-1 whitespace-pre-line text-sm leading-7 text-white/85">{{ $contactAddress }}</p></div></div>@endif
            @if($contactEmail)<a href="mailto:{{ $contactEmail }}" class="mt-5 flex gap-4"><x-ughi-icon name="mail" class="mt-1 h-5 w-5 shrink-0 text-blue-200"/><span><span class="block font-semibold">Email</span><span class="mt-1 block break-all text-sm text-white/85">{{ $contactEmail }}</span></span></a>@endif
            @if($contactPhone)<a href="tel:{{ $contactPhone }}" class="mt-5 flex gap-4"><x-ughi-icon name="phone" class="mt-1 h-5 w-5 shrink-0 text-blue-200"/><span><span class="block font-semibold">Phone</span><span class="mt-1 block text-sm text-white/85">{{ $contactPhone }}</span></span></a>@endif
            @if($site->get('postal_address'))<div class="mt-5 flex gap-4"><x-ughi-icon name="mail" class="mt-1 h-5 w-5 shrink-0 text-blue-200"/><div><p class="font-semibold">Postal Address</p><p class="mt-1 whitespace-pre-line text-sm leading-7 text-white/85">{{ $site->get('postal_address') }}</p></div></div>@endif
            @if(!$contactAddress && !$contactEmail && !$contactPhone && !$site->get('postal_address'))<p class="mt-4 text-sm leading-7 text-white/75">Official office contact details will appear here once they are confirmed by the organization.</p>@endif
            @if($contactWhatsapp)<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactWhatsapp) }}" target="_blank" rel="noopener" class="btn mt-7 border border-white/40 bg-white/10 text-white hover:bg-white/20"><x-ughi-icon name="message-circle" class="h-4 w-4"/>WhatsApp</a>@endif
        </aside>
    </div>
    @if($operatingCountries->isNotEmpty())<div class="mt-20 grid gap-10 border-t border-line pt-12 lg:grid-cols-[.8fr_1.2fr]"><div><p class="eyebrow">WHERE WE WORK</p><h2 class="text-3xl font-bold tracking-[-.03em]">Find us in our operating areas.</h2><p class="mt-4 leading-7 text-muted">Our published country, region and district information is managed by the UGHI team.</p><a href="/where-we-work" class="text-link mt-6">Explore locations<x-ughi-icon class="h-4 w-4"/></a></div><div class="card p-6"><x-location-summary :countries="$operatingCountries"/></div></div>@endif
@elseif($kind === 'leadership')
    @if(filled($body))<article class="prose prose-lg mb-12 max-w-3xl text-muted"><p class="whitespace-pre-line leading-8">{{ $body }}</p></article>@endif
    @if($leadershipGroups->isNotEmpty())
        <div x-data="{ layout: 'hierarchy' }">
            <div class="mb-12 flex justify-center gap-2" role="group" aria-label="Choose team layout">
                <button type="button" @click="layout = 'hierarchy'" :aria-pressed="layout === 'hierarchy'" class="btn min-h-10 px-5 py-2 text-sm" :class="layout === 'hierarchy' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'border border-line bg-white text-muted hover:border-primary hover:text-primary'"><x-ughi-icon name="branch" class="h-4 w-4"/>Hierarchy</button>
                <button type="button" @click="layout = 'grid'" :aria-pressed="layout === 'grid'" class="btn min-h-10 px-5 py-2 text-sm" :class="layout === 'grid' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'border border-line bg-white text-muted hover:border-primary hover:text-primary'"><x-ughi-icon name="grid" class="h-4 w-4"/>Grid</button>
            </div>
            <div class="space-y-16">
                @foreach($leadershipGroups as $group)
                    @php
                        $memberIds = $group['members']->pluck('id');
                        $roots = $group['members']->filter(fn ($member) => !$member->leadership_parent_id || !$memberIds->contains($member->leadership_parent_id))->values();
                        if ($roots->isEmpty()) $roots = $group['members'];
                    @endphp
                    <section aria-labelledby="team-group-{{ $group['key'] }}">
                        <div class="mb-8 text-center"><p class="eyebrow justify-center">MEET THE PEOPLE BEHIND UGHI</p><h2 id="team-group-{{ $group['key'] }}" class="text-3xl font-bold tracking-[-.035em]">{{ $group['label'] }}</h2></div>
                        <div x-show="layout === 'hierarchy'" x-cloak class="leadership-tree">
                            @foreach($roots as $root)@include('components.leadership-node', ['member' => $root, 'trail' => []])@endforeach
                        </div>
                        <div x-show="layout === 'grid'" x-cloak class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($group['members'] as $leader)<x-team-profile-card :member="$leader"/>@endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    @else
        <x-empty-state :title="$emptyTitle" :description="$emptyDescription"/>
    @endif
@elseif($kind === 'history')
    @if(filled($body))<article class="prose prose-lg mb-12 max-w-3xl text-muted"><p class="whitespace-pre-line leading-8">{{ $body }}</p></article>@endif
    @if(count($history))
        <section x-data="{ layout: 'vertical' }" aria-label="UGHI history timeline">
            <div class="mb-10 flex justify-center gap-2" role="group" aria-label="Choose timeline layout">
                <button type="button" @click="layout = 'vertical'" :aria-pressed="layout === 'vertical'" class="btn min-h-10 px-5 py-2 text-sm" :class="layout === 'vertical' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'border border-line bg-white text-muted hover:border-primary hover:text-primary'"><x-ughi-icon name="branch" class="h-4 w-4"/>Vertical</button>
                <button type="button" @click="layout = 'horizontal'" :aria-pressed="layout === 'horizontal'" class="btn min-h-10 px-5 py-2 text-sm" :class="layout === 'horizontal' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'border border-line bg-white text-muted hover:border-primary hover:text-primary'"><x-ughi-icon name="grid" class="h-4 w-4"/>Horizontal</button>
            </div>
            <div x-show="layout === 'vertical'" x-cloak class="history-timeline-vertical">
                @foreach($history as $milestone)<article class="history-milestone"><div class="history-milestone-year">{{ $milestone['year'] ?? '' }}</div><div class="card p-6 md:p-8"><h2 class="text-xl font-bold">{{ $milestone['title'] ?? '' }}</h2><p class="mt-3 whitespace-pre-line text-sm leading-7 text-muted">{{ $milestone['description'] ?? '' }}</p></div></article>@endforeach
            </div>
            <div x-show="layout === 'horizontal'" x-cloak class="history-timeline-horizontal">
                @foreach($history as $milestone)<article class="history-milestone-horizontal"><span class="history-horizontal-dot"></span><p class="text-sm font-bold tracking-wide text-primary">{{ $milestone['year'] ?? '' }}</p><div class="card mt-4 h-full p-6"><h2 class="text-lg font-bold">{{ $milestone['title'] ?? '' }}</h2><p class="mt-3 whitespace-pre-line text-sm leading-7 text-muted">{{ $milestone['description'] ?? '' }}</p></div></article>@endforeach
            </div>
        </section>
    @else
        <x-empty-state title="Our journey is being documented" description="Our history and milestones will be published here once they have been confirmed by the organization."/>
    @endif
@elseif(in_array($kind, ['volunteer', 'membership', 'partnership'], true))
    @if(filled($body))<p class="mb-8 max-w-3xl whitespace-pre-line text-lg leading-8 text-muted">{{ $body }}</p>@endif
    @include('components.involvement-form', ['type' => $kind])
@elseif($kind === 'involved')<div class="grid gap-5 sm:grid-cols-3">@foreach(['Volunteer' => '/get-involved/volunteer', 'Membership' => '/get-involved/membership', 'Partner with us' => '/get-involved/partner'] as $label => $url)<a href="{{ $url }}" class="card p-6 text-primary"><h2 class="text-xl font-semibold">{{ $label }}</h2><x-ughi-icon class="mt-6 h-5 w-5"/></a>@endforeach</div>
@elseif($kind === 'governance')
    @if(filled($body))
        <article class="prose prose-lg max-w-3xl text-muted"><p class="whitespace-pre-line leading-8">{{ $body }}</p></article>
    @else
        <div class="space-y-8">@foreach(['General Meeting' => 'The General Meeting forms part of UGHI’s governance structure.', 'Board' => 'The Board provides oversight and strategic direction.', 'Secretariat' => 'The Secretariat supports day-to-day coordination and implementation.'] as $heading => $text)<div><h2 class="text-2xl font-semibold">{{ $heading }}</h2><p class="mt-3 leading-8 text-muted">{{ $text }}</p></div>@endforeach</div>
    @endif
@elseif(filled($body))<article class="prose prose-lg max-w-3xl text-muted"><p class="whitespace-pre-line leading-8">{{ $body }}</p></article>
@else<x-empty-state :title="$emptyTitle" :description="$emptyDescription"/>@endif
<a href="/programmes" class="text-link mt-10">Explore Our Work<x-ughi-icon class="h-4 w-4"/></a>
</div></section></x-layout>

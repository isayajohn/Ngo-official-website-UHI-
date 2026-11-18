<x-layout :title="$title"><x-page-hero :eyebrow="$eyebrow ?? 'USTAWI GLOBAL HEALTH INITIATIVE'" :title="$title" :description="$description"/><section class="section"><div class="wrap">@if(!empty($image))<img src="{{ $image }}" alt="{{ $imageAlt ?? $title }}" class="mb-10 aspect-[2.4/1] w-full rounded-[24px] object-cover">@endif
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
    <div class="grid gap-12 lg:grid-cols-[1.05fr_.95fr] lg:items-start">
        <div>
            <p class="eyebrow">START A CONVERSATION</p>
            <h2 class="heading max-w-2xl">We’re here to listen, learn and work with communities.</h2>
            <p class="mt-6 max-w-2xl text-base leading-8 text-muted">Whether you are looking for programme information, a partnership conversation or a way to support community health, use the details below to reach the UGHI team.</p>
            <div class="mt-9 grid gap-4 sm:grid-cols-2">
                @if($contactEmail)<a href="mailto:{{ $contactEmail }}" class="card group p-5 hover:border-primary/40"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary"><x-ughi-icon name="mail" class="h-5 w-5"/></span><span class="mt-4 block text-xs font-bold tracking-[.14em] text-muted">EMAIL</span><span class="mt-2 block break-words text-sm font-semibold text-ink group-hover:text-primary">{{ $contactEmail }}</span></a>@endif
                @if($contactPhone)<a href="tel:{{ $contactPhone }}" class="card group p-5 hover:border-primary/40"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent/20 text-secondary"><x-ughi-icon name="phone" class="h-5 w-5"/></span><span class="mt-4 block text-xs font-bold tracking-[.14em] text-muted">PHONE</span><span class="mt-2 block text-sm font-semibold text-ink group-hover:text-primary">{{ $contactPhone }}</span></a>@endif
                @if($contactWhatsapp)<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactWhatsapp) }}" class="card group p-5 hover:border-primary/40" target="_blank" rel="noopener"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#25D366]/10 text-[#168a43]"><x-ughi-icon name="message-circle" class="h-5 w-5"/></span><span class="mt-4 block text-xs font-bold tracking-[.14em] text-muted">WHATSAPP</span><span class="mt-2 block text-sm font-semibold text-ink group-hover:text-primary">Message the team</span></a>@endif
                @if($contactAddress)<div class="card p-5"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-secondary/10 text-secondary"><x-ughi-icon name="pin" class="h-5 w-5"/></span><span class="mt-4 block text-xs font-bold tracking-[.14em] text-muted">OFFICE</span><span class="mt-2 block whitespace-pre-line text-sm leading-6 text-ink">{{ $contactAddress }}</span></div>@endif
            </div>
            @if(!$contactEmail && !$contactPhone && !$contactWhatsapp && !$contactAddress)
                <div class="mt-9 rounded-2xl border border-accent/40 bg-accent/10 p-6"><p class="font-semibold text-secondary">Official contact details are being verified.</p><p class="mt-2 text-sm leading-7 text-muted">UGHI’s contact channels will appear here as soon as they are confirmed by the organization.</p></div>
            @endif
            @if($socialLinks->isNotEmpty())<div class="mt-9 border-t border-line pt-6"><p class="text-xs font-bold tracking-[.14em] text-muted">FOLLOW UGHI</p><div class="mt-4 flex flex-wrap gap-3">@foreach($socialLinks as $label => $url)<a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-secondary min-h-10 px-4 py-2 text-xs">{{ $label }}<x-ughi-icon name="arrow-up-right" class="h-4 w-4"/></a>@endforeach</div></div>@endif
        </div>
        <aside class="rounded-[24px] bg-primary-dark p-7 text-white md:p-9">
            <p class="text-xs font-bold tracking-[.16em] text-accent">WHAT CAN WE HELP WITH?</p>
            <h2 class="mt-4 text-3xl font-bold tracking-[-.03em]">Choose the right starting point.</h2>
            <div class="mt-8 space-y-3">
                @foreach([['Programme information', 'Learn about UGHI’s health and community programmes.', '/programmes'], ['Partnerships', 'Explore ways to collaborate with UGHI.', '/get-involved/partner'], ['Volunteer and membership', 'See current ways to participate.', '/get-involved']] as [$label, $copy, $url])<a href="{{ $url }}" class="group block rounded-2xl border border-white/15 p-4 hover:border-white/40 hover:bg-white/10"><span class="flex items-center justify-between gap-4 font-semibold">{{ $label }}<x-ughi-icon name="arrow-up-right" class="h-4 w-4 shrink-0 text-accent"/></span><span class="mt-2 block text-sm leading-6 text-white/70">{{ $copy }}</span></a>@endforeach
            </div>
            @if($contactEmail)<a href="mailto:{{ $contactEmail }}?subject=Enquiry%20for%20UGHI" class="btn mt-8 w-full bg-white text-primary-dark hover:bg-canvas">Send an enquiry by email<x-ughi-icon name="arrow-up-right" class="h-4 w-4"/></a>@endif
            <p class="mt-5 text-xs leading-6 text-white/60">Please do not include confidential medical, safeguarding or personal case information in an ordinary email.</p>
        </aside>
    </div>
    <section class="mt-20 grid gap-10 border-t border-line pt-12 lg:grid-cols-[.8fr_1.2fr]">
        <div><p class="eyebrow">SEND A MESSAGE</p><h2 class="text-3xl font-bold tracking-[-.03em]">Tell us how we can help.</h2><p class="mt-4 leading-7 text-muted">Your message is routed internally to the department you choose. We will use your details only to respond to this enquiry. See our <a href="{{ url('/privacy-policy') }}" class="text-link inline">Privacy Policy</a>.</p>@if(session('contact_success'))<p class="mt-6 rounded-xl bg-primary/10 p-4 text-sm font-semibold text-primary-dark" role="status">{{ session('contact_success') }}</p>@endif</div>
        <form method="POST" action="{{ route('contact.store') }}" class="card grid gap-5 p-6 md:grid-cols-2 md:p-8">@csrf
            <div class="hidden" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <div><label for="contact-name" class="text-sm font-semibold">Name</label><input id="contact-name" name="name" value="{{ old('name') }}" required maxlength="160" class="filter-input mt-2" autocomplete="name">@error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            <div><label for="contact-email" class="text-sm font-semibold">Email</label><input id="contact-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" class="filter-input mt-2" autocomplete="email">@error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            <div><label for="contact-phone" class="text-sm font-semibold">Phone <span class="font-normal text-muted">(optional)</span></label><input id="contact-phone" name="phone" value="{{ old('phone') }}" maxlength="50" class="filter-input mt-2" autocomplete="tel"></div>
            <div><label for="contact-department" class="text-sm font-semibold">Send to <span class="font-normal text-muted">(optional)</span></label><select id="contact-department" name="department_id" class="filter-input mt-2"><option value="">General UGHI enquiry</option>@foreach($contactDepartments as $contactDepartment)<option value="{{ $contactDepartment->id }}" @selected(old('department_id') == $contactDepartment->id)>{{ $contactDepartment->name }}</option>@endforeach</select></div>
            <div class="md:col-span-2"><label for="contact-subject" class="text-sm font-semibold">Subject <span class="font-normal text-muted">(optional)</span></label><input id="contact-subject" name="subject" value="{{ old('subject') }}" maxlength="255" class="filter-input mt-2"></div>
            <div class="md:col-span-2"><label for="contact-message" class="text-sm font-semibold">Message</label><textarea id="contact-message" name="message" required maxlength="10000" rows="6" class="filter-input mt-2">{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-4"><p class="text-xs leading-5 text-muted">Do not submit medical records or safeguarding disclosures through this form.</p><button type="submit" class="btn btn-primary">Send message<x-ughi-icon class="h-4 w-4"/></button></div>
        </form>
    </section>
    @if($operatingCountries->isNotEmpty())<div class="mt-20 grid gap-10 border-t border-line pt-12 lg:grid-cols-[.8fr_1.2fr]"><div><p class="eyebrow">WHERE WE WORK</p><h2 class="text-3xl font-bold tracking-[-.03em]">Find us in our operating areas.</h2><p class="mt-4 leading-7 text-muted">Our published country, region and district information is managed by the UGHI team.</p><a href="/where-we-work" class="text-link mt-6">Explore locations<x-ughi-icon class="h-4 w-4"/></a></div><div class="card p-6"><x-location-summary :countries="$operatingCountries"/></div></div>@endif
@elseif($kind === 'leadership')
    @if(filled($body))<article class="prose prose-lg mb-12 max-w-3xl text-muted"><p class="whitespace-pre-line leading-8">{{ $body }}</p></article>@endif
    @if($leaders->isNotEmpty())
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($leaders as $leader)
                <article class="card overflow-hidden">
                    @if($leader->photo)<img src="{{ Storage::disk('public')->url($leader->photo) }}" alt="{{ $leader->photo_alt ?: $leader->name }}" class="aspect-[4/3] w-full object-cover">@else<div class="flex aspect-[4/3] items-center justify-center bg-primary/8 text-primary"><x-ughi-icon name="users" class="h-14 w-14"/></div>@endif
                    <div class="p-6"><p class="eyebrow">{{ $leader->leadership_role ?: ($leader->position?->title ?: 'UGHI leadership') }}</p><h2 class="mt-2 text-xl font-bold">{{ $leader->name }}</h2>@if($leader->department)<p class="mt-1 text-sm font-medium text-primary">{{ $leader->department->name }}</p>@endif @if($leader->short_bio)<p class="mt-4 text-sm leading-7 text-muted">{{ $leader->short_bio }}</p>@endif @if($leader->email)<a href="mailto:{{ $leader->email }}" class="text-link mt-5">Contact {{ $leader->name }}<x-ughi-icon name="arrow-up-right" class="h-4 w-4"/></a>@endif</div>
                </article>
            @endforeach
        </div>
    @else
        <x-empty-state :title="$emptyTitle" :description="$emptyDescription"/>
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

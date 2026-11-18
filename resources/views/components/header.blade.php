@php
$menus = [
    'About' => ['/about', ['Who We Are' => '/about', 'Departments' => '/about/departments', 'Partners' => '/about/partners', 'Vision & Mission' => '/about/vision-mission', 'Leadership' => '/about/leadership', 'Governance' => '/about/governance', 'Where We Work' => '/where-we-work']],
    'Our Work' => ['/programmes', ['All Programmes' => '/programmes'] + \App\Models\Programme::published()->get()->mapWithKeys(fn($p) => [$p->translated('title') => route('programmes.show', $p)])->all()],
    'Resources' => ['/resources/publications', ['News' => '/news', 'Events' => '/events', 'Publications' => '/resources/publications', 'Gallery' => '/gallery']],
    'Get Involved' => ['/get-involved', ['Volunteer' => '/get-involved/volunteer', 'Membership' => '/get-involved/membership', 'Partner With Us' => '/get-involved/partner', 'Donate' => '/donate']],
];
@endphp
<header x-data="{ mobile: false }" @keydown.escape.window="if (mobile) { mobile = false; $refs.mobileToggle.focus() }" class="sticky top-0 z-50 border-b border-line bg-white">
<div class="wrap flex h-[88px] items-center justify-between gap-4">
<x-logo />
<nav aria-label="Main navigation" class="hidden items-center gap-[18px] xl:flex">
<a href="/" class="nav-link {{ request()->routeIs('home') ? 'text-primary' : '' }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
@foreach(['About', 'Our Work', 'Projects', 'Impact', 'Resources', 'Get Involved', 'Contact'] as $label)
@if(isset($menus[$label]))
<x-dropdown :label="$label" :id="'nav-'.Str::slug($label)" :width="$label === 'Our Work' ? 'w-80' : 'w-60'" variant="nav">
@foreach($menus[$label][1] as $text => $url)<li><a role="menuitem" href="{{ $url }}">{{ $text }}</a></li>@endforeach
</x-dropdown>
@else
<a class="nav-link" href="/{{ Str::slug($label) }}">{{ $label }}</a>
@endif
@endforeach
</nav>
<div class="flex items-center gap-4">
<div class="hidden items-center gap-1.5 text-xs font-medium sm:flex" aria-label="Language">
@foreach(['en' => 'EN', 'sw' => 'SW'] as $locale => $label)
<form action="{{ route('locale', $locale) }}" method="post">@csrf<button type="submit" class="p-1 {{ app()->getLocale() === $locale ? 'font-bold text-primary' : 'text-muted' }}" @if(app()->getLocale() === $locale) aria-current="true" @endif>{{ $label }}</button></form>@if(!$loop->last)<span class="text-line">/</span>@endif
@endforeach
</div>
<a href="/donate" class="btn btn-primary hidden min-h-10 px-4 py-2.5 text-xs sm:inline-flex"><x-ughi-icon name="heart" class="h-4 w-4"/>Donate</a>
<button x-ref="mobileToggle" type="button" @click="mobile = !mobile" :aria-expanded="mobile" aria-controls="mobile-menu" :aria-label="mobile ? 'Close menu' : 'Open menu'" class="flex h-10 w-10 items-center justify-center rounded-lg border border-line xl:hidden"><x-ughi-icon name="menu" /></button>
</div>
</div>
<nav id="mobile-menu" x-cloak x-show="mobile" class="max-h-[calc(100dvh-88px)] overflow-y-auto border-t border-line bg-white px-5 py-4 xl:hidden" aria-label="Mobile navigation">
<a class="nav-link" href="/">Home</a>
@foreach(['About', 'Our Work', 'Projects', 'Impact', 'Resources', 'Get Involved', 'Contact'] as $label)
@if(isset($menus[$label]))
<x-dropdown :label="$label" :id="'mobile-nav-'.Str::slug($label)" width="w-full" trigger="click" variant="mobile">
@foreach($menus[$label][1] as $text => $url)<li><a role="menuitem" href="{{ $url }}" @click="mobile = false">{{ $text }}</a></li>@endforeach
</x-dropdown>
@else<a class="nav-link" href="/{{ Str::slug($label) }}">{{ $label }}</a>@endif
@endforeach
<div class="mt-4 flex gap-4 sm:hidden">@foreach(['en' => 'English', 'sw' => 'Kiswahili'] as $locale => $label)<form method="post" action="{{ route('locale', $locale) }}">@csrf<button type="submit" class="text-link p-2">{{ $label }}</button></form>@endforeach</div>
</nav>
</header>

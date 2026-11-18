@php
$menuItems = $site->get('header_menu', config('ughi.defaults.header_menu', []));
$menuItems = is_array($menuItems) && count($menuItems) ? $menuItems : config('ughi.defaults.header_menu', []);
$isActiveMenuUrl = static function (?string $url): bool {
    if (!filled($url)) return false;
    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    return $path === '' ? request()->routeIs('home') : (request()->is($path) || request()->is($path.'/*'));
};
@endphp
<div class="site-contact-bar text-white">
<div class="site-utility-inner wrap flex min-h-11 flex-wrap items-center justify-between gap-x-8 gap-y-2 py-2 text-[11px] font-medium sm:text-xs">
<div class="site-contacts flex flex-wrap items-center gap-x-6 gap-y-1.5">
@if($site->get('email'))<a class="inline-flex items-center gap-2 whitespace-nowrap hover:text-white/75" href="mailto:{{ $site->get('email') }}"><x-ughi-icon name="mail" class="h-3.5 w-3.5 shrink-0"/>{{ $site->get('email') }}</a>@endif
@if($site->get('phone'))<a class="inline-flex items-center gap-2 whitespace-nowrap hover:text-white/75" href="tel:{{ $site->get('phone') }}"><x-ughi-icon name="phone" class="h-3.5 w-3.5 shrink-0"/>{{ $site->get('phone') }}</a>@endif
@unless($site->get('email') || $site->get('phone'))<a class="hover:text-white/75" href="/contact">Contact UGHI</a>@endunless
</div>
<div class="site-utility-meta flex items-center gap-3 sm:gap-4"><span class="hidden sm:inline">{{ $site->get('tagline') }}</span><div class="flex items-center gap-2">@foreach(['facebook', 'x', 'instagram', 'linkedin', 'youtube'] as $network)@if($site->get($network))<a href="{{ $site->get($network) }}" aria-label="{{ ucfirst($network) }}" rel="noopener noreferrer" class="flex h-7 w-7 items-center justify-center rounded-full border border-white/35 text-[10px] font-bold transition-colors hover:bg-white/15">{{ $network === 'facebook' ? 'f' : ($network === 'instagram' ? '◎' : ($network === 'linkedin' ? 'in' : ($network === 'youtube' ? '▶' : '𝕏'))) }}</a>@endif @endforeach</div></div>
</div>
</div>
<header x-data="{ mobile: false }" @keydown.escape.window="if (mobile) { mobile = false; $refs.mobileToggle.focus() }" class="site-header sticky top-0 z-50 border-b border-line bg-white/95 shadow-[0_5px_20px_rgb(6_71_127_/_8%)] backdrop-blur">
<div class="site-header-inner wrap flex h-[76px] items-center justify-between gap-5 lg:max-w-[1200px]">
<x-logo />
<nav aria-label="Main navigation" class="site-main-nav hidden items-center gap-1 xl:flex">
@foreach($menuItems as $index => $item)
@php
    $label = $item['label'] ?? '';
    $url = $item['url'] ?? '';
    $children = collect($item['children'] ?? [])->filter(fn ($child) => filled($child['label'] ?? null) && filled($child['url'] ?? null));
    $active = $isActiveMenuUrl($url) || $children->contains(fn ($child) => $isActiveMenuUrl($child['url'] ?? null));
@endphp
@if(filled($label) && $children->isNotEmpty())
<x-dropdown :label="$label" :id="'nav-item-'.$index" width="w-64" variant="nav" :active="$active" :delay="100">
@if(filled($url) && !$children->contains(fn ($child) => ($child['url'] ?? null) === $url))<li><a role="menuitem" href="{{ $url }}" @if(str_starts_with($url, 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $label }} overview</a></li>@endif
@foreach($children as $child)<li><a role="menuitem" href="{{ $child['url'] }}" @if(str_starts_with($child['url'], 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $child['label'] }}</a></li>@endforeach
</x-dropdown>
@elseif(filled($label) && filled($url))
<a class="nav-link {{ $active ? 'text-primary' : '' }}" href="{{ $url }}" @if(str_starts_with($url, 'http')) target="_blank" rel="noopener noreferrer" @endif @if($active) aria-current="page" @endif>{{ $label }}</a>
@endif
@endforeach
</nav>
<div class="flex items-center gap-4">
<div class="hidden items-center gap-1.5 text-xs font-medium sm:flex" aria-label="Language">
@foreach(['en' => 'EN', 'sw' => 'SW'] as $locale => $label)
<form action="{{ route('locale', $locale) }}" method="post">@csrf<button type="submit" class="p-1 {{ app()->getLocale() === $locale ? 'font-bold text-primary' : 'text-muted' }}" @if(app()->getLocale() === $locale) aria-current="true" @endif>{{ $label }}</button></form>@if(!$loop->last)<span class="text-line">/</span>@endif
@endforeach
</div>
<a href="/donate" class="btn btn-primary hidden min-h-10 px-4 py-2.5 text-xs sm:inline-flex xl:hidden"><x-ughi-icon name="heart" class="h-4 w-4"/>Donate</a>
<button x-ref="mobileToggle" type="button" @click="mobile = !mobile" :aria-expanded="mobile" aria-controls="mobile-menu" :aria-label="mobile ? 'Close menu' : 'Open menu'" class="flex h-10 w-10 items-center justify-center rounded-lg border border-line xl:hidden"><x-ughi-icon name="menu" /></button>
</div>
</div>
<nav id="mobile-menu" x-cloak x-show="mobile" class="max-h-[calc(100dvh-116px)] overflow-y-auto border-t border-line bg-white px-5 py-4 xl:hidden" aria-label="Mobile navigation">
@foreach($menuItems as $index => $item)
@php
    $label = $item['label'] ?? '';
    $url = $item['url'] ?? '';
    $children = collect($item['children'] ?? [])->filter(fn ($child) => filled($child['label'] ?? null) && filled($child['url'] ?? null));
    $active = $isActiveMenuUrl($url) || $children->contains(fn ($child) => $isActiveMenuUrl($child['url'] ?? null));
@endphp
@if(filled($label) && $children->isNotEmpty())
<x-dropdown :label="$label" :id="'mobile-nav-item-'.$index" width="w-full" trigger="click" variant="mobile" :active="$active">
@if(filled($url) && !$children->contains(fn ($child) => ($child['url'] ?? null) === $url))<li><a role="menuitem" href="{{ $url }}" @click="mobile = false">{{ $label }} overview</a></li>@endif
@foreach($children as $child)<li><a role="menuitem" href="{{ $child['url'] }}" @click="mobile = false">{{ $child['label'] }}</a></li>@endforeach
</x-dropdown>
@elseif(filled($label) && filled($url))
<a class="nav-link {{ $active ? 'text-primary' : '' }}" href="{{ $url }}" @click="mobile = false" @if($active) aria-current="page" @endif>{{ $label }}</a>
@endif
@endforeach
<div class="mt-4 flex gap-4 sm:hidden">@foreach(['en' => 'English', 'sw' => 'Kiswahili'] as $locale => $label)<form method="post" action="{{ route('locale', $locale) }}">@csrf<button type="submit" class="text-link p-2">{{ $label }}</button></form>@endforeach</div>
</nav>
</header>

<footer class="bg-primary-dark text-white">
<div class="wrap py-16">
<div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.65fr_1fr_1fr_1fr_1fr] lg:gap-8">
<div><x-logo light /><p class="mt-6 max-w-64 text-sm leading-7 text-white/75">{{ $site->get('tagline') }}</p><p class="mt-3 max-w-64 text-xs leading-6 text-white/65">{{ $site->get('footer_about') }}</p>@php($operatingCountries = \App\Models\Country::published()->limit(6)->pluck('name'))
@if($operatingCountries->isNotEmpty())<p class="mt-5 text-xs text-white/60">{{ $operatingCountries->join(' · ') }}</p>@endif</div>
@foreach($site->get('footer_links', []) as $group)
@if(filled($group['heading'] ?? null) && !empty($group['links'] ?? []))<div><h2 class="mb-5 font-sans text-[11px] font-semibold tracking-[0.14em] text-white/90">{{ $group['heading'] }}</h2>@foreach($group['links'] as $link)@if(filled($link['label'] ?? null) && filled($link['url'] ?? null))<a class="footer-link" href="{{ $link['url'] }}" @if(str_starts_with($link['url'], 'http')) rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>@endif @endforeach</div>@endif
@endforeach
</div>
@if($site->get('email') || $site->get('phone') || $site->get('whatsapp') || $site->get('physical_address') || $site->get('postal_address'))
<div class="mt-10 flex flex-wrap gap-6 border-t border-white/15 pt-6 text-sm text-white/80" aria-label="Contact details">
@if($site->get('email'))<a href="mailto:{{ $site->get('email') }}">{{ $site->get('email') }}</a>@endif
@if($site->get('phone'))<a href="tel:{{ $site->get('phone') }}">{{ $site->get('phone') }}</a>@endif
@if($site->get('whatsapp'))<a href="https://wa.me/{{ preg_replace('/\D+/', '', $site->get('whatsapp')) }}" rel="noopener noreferrer">WhatsApp: {{ $site->get('whatsapp') }}</a>@endif
@if($site->get('physical_address'))<span>{{ $site->get('physical_address') }}</span>@endif
@if($site->get('postal_address'))<span>{{ $site->get('postal_address') }}</span>@endif
</div>@endif
<div class="mt-12 flex flex-wrap items-center justify-between gap-5 border-t border-white/15 pt-6 text-xs text-white/65">
<p>© {{ now()->year }} {{ $site->get('organization_name') }}. All rights reserved.</p>
<div class="flex flex-wrap gap-5">@foreach($site->get('footer_legal_links', []) as $link)@if(filled($link['label'] ?? null) && filled($link['url'] ?? null))<a href="{{ $link['url'] }}" @if(str_starts_with($link['url'], 'http')) rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>@endif @endforeach</div>
</div>
@php($socials = array_filter(collect(['facebook', 'instagram', 'linkedin', 'youtube', 'x'])->mapWithKeys(fn($key) => [$key => $site->get($key)])->all()))
@if($socials)<div class="mt-5 flex gap-5">@foreach($socials as $name => $url)<a href="{{ $url }}" rel="noopener noreferrer" class="text-xs text-white/80">{{ ucfirst($name) }}</a>@endforeach</div>@endif
</div>
</footer>

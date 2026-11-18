<footer class="site-footer">
<div class="wrap py-14 md:py-16">
@php($footerGroups = collect($site->get('footer_links', []))->filter(fn ($group) => filled($group['heading'] ?? null) && !empty($group['links'] ?? [])))
<div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.15fr_1.5fr_1.1fr] lg:gap-12">
    <div>
        <x-logo light />
        <p class="mt-6 max-w-sm text-sm leading-7 text-white/75">{{ $site->get('footer_about') ?: $site->get('tagline') }}</p>
        @php($socials = array_filter(collect(['facebook', 'x', 'instagram', 'linkedin', 'youtube'])->mapWithKeys(fn($key) => [$key => $site->get($key)])->all()))
        @if($socials)<div class="mt-6 flex gap-2.5" aria-label="Social media">@foreach($socials as $name => $url)<a href="{{ $url }}" aria-label="{{ ucfirst($name) }}" rel="noopener noreferrer" class="footer-social">{{ $name === 'facebook' ? 'f' : ($name === 'instagram' ? '◎' : ($name === 'linkedin' ? 'in' : ($name === 'youtube' ? '▶' : '𝕏'))) }}</a>@endforeach</div>@endif
    </div>

    <div class="grid gap-8 sm:grid-cols-2">@foreach($footerGroups as $group)
        <div><h2 class="footer-heading">{{ $group['heading'] }}</h2>@foreach($group['links'] as $link)@if(filled($link['label'] ?? null) && filled($link['url'] ?? null))<a class="footer-link" href="{{ $link['url'] }}" @if(str_starts_with($link['url'], 'http')) rel="noopener noreferrer" @endif><span class="text-accent">›</span>{{ $link['label'] }}</a>@endif @endforeach</div>
    @endforeach</div>

    <div>
        <h2 class="footer-heading">Contact Info</h2>
        <div class="space-y-4 text-[13px] leading-6 text-white/80">
            @if($site->get('physical_address'))<div class="flex gap-3"><x-ughi-icon name="pin" class="mt-1 h-4 w-4 shrink-0 text-blue-200"/><span class="whitespace-pre-line">{{ $site->get('physical_address') }}</span></div>@endif
            @if($site->get('phone'))<a class="flex gap-3 hover:text-white" href="tel:{{ $site->get('phone') }}"><x-ughi-icon name="phone" class="mt-1 h-4 w-4 shrink-0 text-blue-200"/><span>{{ $site->get('phone') }}</span></a>@endif
            @if($site->get('email'))<a class="flex gap-3 hover:text-white" href="mailto:{{ $site->get('email') }}"><x-ughi-icon name="mail" class="mt-1 h-4 w-4 shrink-0 text-blue-200"/><span>{{ $site->get('email') }}</span></a>@endif
            @if($site->get('whatsapp'))<a class="flex gap-3 hover:text-white" href="https://wa.me/{{ preg_replace('/\D+/', '', $site->get('whatsapp')) }}" rel="noopener noreferrer"><x-ughi-icon name="message-circle" class="mt-1 h-4 w-4 shrink-0 text-blue-200"/><span>WhatsApp: {{ $site->get('whatsapp') }}</span></a>@endif
            @if($site->get('postal_address'))<div class="flex gap-3"><x-ughi-icon name="mail" class="mt-1 h-4 w-4 shrink-0 text-blue-200"/><span class="whitespace-pre-line">{{ $site->get('postal_address') }}</span></div>@endif
            @if(!$site->get('physical_address') && !$site->get('phone') && !$site->get('email') && !$site->get('whatsapp') && !$site->get('postal_address'))<a class="text-white/80 hover:text-white" href="/contact">Contact UGHI</a>@endif
        </div>
    </div>
</div>
<div class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-white/15 pt-6 text-xs text-white/60">
    <p>© {{ now()->year }} {{ $site->get('organization_name') }}. All rights reserved.</p>
    <div class="flex flex-wrap gap-x-5 gap-y-2">@foreach($site->get('footer_legal_links', []) as $link)@if(filled($link['label'] ?? null) && filled($link['url'] ?? null))<a class="hover:text-white" href="{{ $link['url'] }}" @if(str_starts_with($link['url'], 'http')) rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>@endif @endforeach</div>
</div>
</div>
</footer>

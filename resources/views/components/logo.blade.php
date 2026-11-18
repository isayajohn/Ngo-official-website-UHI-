@props(['light' => false])
@php
    $logoSource = isset($site) ? $site->image('logo', 'images/ughi-logo.svg') : asset('images/ughi-logo.svg');
    $logoDescription = isset($site) ? ($site->get('logo_alt') ?: 'UHI — Ustawi Health Initiative. Healthy, Resilient, Thriving Communities.') : 'UHI — Ustawi Health Initiative. Healthy, Resilient, Thriving Communities.';
@endphp
<a href="{{ route('home') }}" aria-label="UHI Ustawi Health Initiative home" class="flex shrink-0 items-center">
<img src="{{ $logoSource }}" alt="{{ $logoDescription }}" class="h-auto w-[165px] object-contain sm:w-[190px] {{ $light ? 'rounded-md bg-white p-1.5' : '' }}" width="760" height="180">
</a>

@props(['light' => false])
<a href="{{ route('home') }}" aria-label="UHI Ustawi Health Initiative home" class="flex shrink-0 items-center">
<img src="{{ asset('images/ughi-logo.svg') }}" alt="UHI — Ustawi Health Initiative. Healthy, Resilient, Thriving Communities." class="h-auto w-[210px] sm:w-[250px] {{ $light ? 'brightness-0 invert' : '' }}" width="760" height="180">
</a>

@props(['statistic', 'context' => true, 'dark' => false])
@php($divisor = match($statistic->suffix) { 'K+' => 1000, 'M+' => 1000000, default => 1 })
@php($count = $statistic->value / $divisor)
<div {{ $attributes->class(['min-w-0 p-6', $dark ? 'rounded-none border-0 bg-transparent text-center' : 'rounded-2xl border border-line bg-white']) }}>
<x-ughi-icon :name="$statistic->icon" class="mb-3 h-7 w-7 {{ $dark ? 'mx-auto text-white/65' : 'text-primary' }}"/>
<p class="font-heading text-4xl font-extrabold tracking-tight {{ $dark ? 'text-white' : 'text-primary-dark' }}" x-data="impactCounter({{ $count }}, {{ $divisor === 1 || floor($count) === $count ? 0 : 1 }})"><span aria-hidden="true"><span x-text="display">{{ $statistic->displayNumber() }}</span>{{ $statistic->suffix }}</span><span class="sr-only">{{ number_format($statistic->value) }}{{ str_ends_with($statistic->suffix, '+') ? '+' : '' }}</span></p>
<p class="mt-2 text-xs font-bold uppercase tracking-[0.08em] {{ $dark ? 'text-white' : '' }}">{{ $statistic->translated('label') }}</p>
@if($context)<p class="mt-2 text-xs leading-6 text-muted">{{ $statistic->project?->translated('title') ?: ($statistic->programme?->translated('title') ?: 'Organization-wide') }}</p>@endif
@unless($dark)<p class="mt-3 text-[11px] text-muted">As of {{ $statistic->as_of_date->format('d M Y') }}</p>@endunless
</div>

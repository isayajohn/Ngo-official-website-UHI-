@props(['statistic', 'context' => true])
@php($divisor = match($statistic->suffix) { 'K+' => 1000, 'M+' => 1000000, default => 1 })
@php($count = $statistic->value / $divisor)
<div {{ $attributes->class(['min-w-0 rounded-2xl border border-line bg-white p-6']) }}>
<x-ughi-icon :name="$statistic->icon" class="mb-4 h-6 w-6 text-primary"/>
<p class="font-heading text-4xl font-bold tracking-tight text-primary-dark" x-data="impactCounter({{ $count }}, {{ $divisor === 1 || floor($count) === $count ? 0 : 1 }})"><span aria-hidden="true"><span x-text="display">{{ $statistic->displayNumber() }}</span>{{ $statistic->suffix }}</span><span class="sr-only">{{ number_format($statistic->value) }}{{ str_ends_with($statistic->suffix, '+') ? '+' : '' }}</span></p>
<p class="mt-3 text-sm font-semibold">{{ $statistic->translated('label') }}</p>
@if($context)<p class="mt-2 text-xs leading-6 text-muted">{{ $statistic->project?->translated('title') ?: ($statistic->programme?->translated('title') ?: 'Organization-wide') }}</p>@endif
<p class="mt-3 text-[11px] text-muted">As of {{ $statistic->as_of_date->format('d M Y') }}</p>
</div>

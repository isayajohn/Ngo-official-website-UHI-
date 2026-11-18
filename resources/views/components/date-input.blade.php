@props([
    'id' => null,
    'name',
    'value' => null,
    'min' => null,
    'max' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $inputId = $id ?: 'date-'.\Illuminate\Support\Str::uuid();
    $dateValue = $value ?? old($name);
    if ($dateValue instanceof \DateTimeInterface) {
        $dateValue = $dateValue->format('Y-m-d');
    }
@endphp

<div class="date-picker-component">
    <div class="date-picker-component__icon" aria-hidden="true">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
            <path d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
        </svg>
    </div>
    <input
        {{ $attributes->except('aria-label')->class(['date-picker-component__input']) }}
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="date"
        value="{{ $dateValue }}"
        @if($min) min="{{ $min }}" @endif
        @if($max) max="{{ $max }}" @endif
        @required($required)
        @disabled($disabled)
        aria-label="{{ $attributes->get('aria-label', $name) }}"
    />
</div>

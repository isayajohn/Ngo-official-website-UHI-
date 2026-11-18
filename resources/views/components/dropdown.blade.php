@props([
    'label',
    'id' => null,
    'align' => 'left',
    'width' => 'w-44',
    'trigger' => 'hover',
    'delay' => 100,
    'variant' => 'brand',
    'active' => false,
])

@php
    $dropdownId = $id ?: 'dropdown-'.\Illuminate\Support\Str::uuid();
    $menuId = $dropdownId.'-menu';
    $alignmentClass = $align === 'right' ? 'right-0' : 'left-0';
    $containerClass = $variant === 'mobile' ? 'block w-full' : 'relative inline-flex';
    $triggerClass = $variant === 'nav'
        ? 'dropdown-menu-trigger dropdown-menu-trigger--nav'
        : ($variant === 'mobile'
            ? 'dropdown-menu-trigger dropdown-menu-trigger--mobile'
            : 'dropdown-menu-trigger dropdown-menu-trigger--brand');
    $panelClass = $variant === 'mobile' ? 'dropdown-menu-panel--mobile' : ($variant === 'nav' ? 'dropdown-menu-panel--nav' : '');
    $activeTriggerClass = $active ? 'text-primary' : '';
@endphp

<div
    class="{{ $containerClass }}"
    x-data="dropdownMenu({ trigger: '{{ $trigger }}', delay: {{ (int) $delay }} })"
    data-dropdown-trigger="{{ $trigger }}"
    data-dropdown-delay="{{ (int) $delay }}"
    @mouseenter="pointerEnter()"
    @mouseleave="pointerLeave()"
    @focusin="focusIn()"
    @focusout="if (!$el.contains($event.relatedTarget)) close()"
    @click.outside="close()"
    @keydown.escape.stop="closeAndFocus()"
>
    <button
        id="{{ $dropdownId }}"
        x-ref="toggle"
        data-dropdown-toggle="{{ $menuId }}"
        data-dropdown-trigger="{{ $trigger }}"
        data-dropdown-delay="{{ (int) $delay }}"
        class="{{ $triggerClass }} {{ $activeTriggerClass }}"
        type="button"
        aria-haspopup="true"
        aria-controls="{{ $menuId }}"
        :aria-expanded="open.toString()"
        @click="activate()"
    >
        <span>{{ $label }}</span>
        <svg class="h-4 w-4 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180 text-primary' : ''" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
            <path d="m19 9-7 7-7-7" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
        </svg>
    </button>

    <div
        id="{{ $menuId }}"
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        class="dropdown-menu-panel {{ $panelClass }} {{ $alignmentClass }} {{ $width }}"
        role="menu"
        aria-labelledby="{{ $dropdownId }}"
        @click="close()"
    >
        <ul class="dropdown-menu-list">
            {{ $slot }}
        </ul>
    </div>
</div>

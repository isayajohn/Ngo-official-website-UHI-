@props(['country' => null])
@if($country && $country->code === 'TZ')
<figure class="w-full">
    <a href="{{ asset('images/tanzania-regions.svg') }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-2xl bg-white" aria-label="Open larger Tanzania regional map (opens in a new tab)">
        <img src="{{ asset('images/tanzania-regions.svg') }}" alt="Regional map of Tanzania with named regions, white boundaries and green, yellow, orange and red fills." width="1250" height="1060" loading="lazy" class="h-auto w-full">
    </a>
    <figcaption class="mt-4 text-center text-xs leading-6 text-muted">
        Regions of {{ $country->name }}. Colors distinguish regions; our operating areas are listed alongside.
        <a href="{{ asset('images/tanzania-regions.svg') }}" target="_blank" rel="noopener" class="mt-1 block font-semibold text-primary underline underline-offset-4">View larger map<span class="sr-only"> (opens in a new tab)</span></a>
    </figcaption>
</figure>
@endif

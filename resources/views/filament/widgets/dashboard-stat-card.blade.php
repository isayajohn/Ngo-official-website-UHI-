@if(isset($stat['url']))<a href="{{ $stat['url'] }}" class="block rounded-2xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary" aria-label="{{ $stat['label'] }}. Open donation management">@endif
<article class="ughi-stat-card ughi-stat-card-{{ $stat['tone'] }}">
    <div class="ughi-stat-card-glow" aria-hidden="true"></div>
    <div class="ughi-stat-icon ughi-stat-icon-{{ $stat['tone'] }}"><x-filament::icon :icon="$stat['icon']" class="h-6 w-6" /></div>
    <div class="ughi-stat-copy">
        <p class="ughi-stat-label">{{ $stat['label'] }}</p>
        <p class="ughi-stat-value">{{ $stat['value'] }}</p>
        <p class="ughi-stat-description">{{ $stat['description'] }}</p>
    </div>
</article>
@if(isset($stat['url']))</a>@endif

<x-filament-widgets::widget>
    @php
        $overviewStats = collect($stats)->reject(fn ($stat) => ($stat['group'] ?? null) === 'donations');
        $donationStats = collect($stats)->filter(fn ($stat) => ($stat['group'] ?? null) === 'donations');
    @endphp
    @if($overviewStats->isNotEmpty())
        <section aria-labelledby="dashboard-overview-heading">
            <h2 id="dashboard-overview-heading" class="ughi-dashboard-section-title">Organization overview</h2>
            <div class="ughi-dashboard-stat-grid">@foreach($overviewStats as $stat) @include('filament.widgets.dashboard-stat-card', ['stat' => $stat]) @endforeach</div>
        </section>
    @endif
    @if($donationStats->isNotEmpty())
        <section class="ughi-dashboard-donations" aria-labelledby="dashboard-donations-heading">
            <div class="ughi-dashboard-section-heading">
                <div><h2 id="dashboard-donations-heading" class="ughi-dashboard-section-title">Donation management</h2><p>Review pledges and confirmed payments.</p></div>
                <a href="{{ \App\Filament\Resources\DonationResource::getUrl('index') }}">View all donations <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4" /></a>
            </div>
            <div class="ughi-dashboard-stat-grid ughi-dashboard-stat-grid-donations">@foreach($donationStats as $stat) @include('filament.widgets.dashboard-stat-card', ['stat' => $stat]) @endforeach</div>
        </section>
    @endif
</x-filament-widgets::widget>

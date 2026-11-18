<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-primary-600 dark:text-primary-400">Monitoring &amp; Evaluation</p>
            <h2 class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">Results framework and indicator management</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-300">Define the results chain, maintain indicator definitions, and set period targets. Verified results and reporting workflows will be added in the next phase.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Indicators', $totalIndicators, 'heroicon-o-chart-bar'], ['Active indicators', $activeIndicators, 'heroicon-o-check-circle'], ['Results framework items', $frameworkItems, 'heroicon-o-rectangle-stack'], ['Open reporting periods', $openPeriods, 'heroicon-o-calendar-days']] as [$label, $value, $icon])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $label }}</p>
                        <x-filament::icon :icon="$icon" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ number_format($value) }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Latest reporting period</h3>
            @if ($latestPeriod)
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $latestPeriod->name }}</span>
                    <span>{{ $latestPeriod->start_date?->format('M j, Y') }} – {{ $latestPeriod->end_date?->format('M j, Y') }}</span>
                    <x-filament::badge :color="in_array($latestPeriod->status, ['OPEN', 'ACTIVE'], true) ? 'success' : 'gray'">{{ str($latestPeriod->status)->lower()->replace('_', ' ')->title() }}</x-filament::badge>
                </div>
            @else
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No reporting periods have been created yet.</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>

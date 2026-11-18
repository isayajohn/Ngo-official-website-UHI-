<x-filament-panels::page>
    <div class="ughi-dashboard-shell space-y-6">
        <header class="ughi-dashboard-hero">
            <p class="ughi-dashboard-kicker">UGHI · REPORTS & PERFORMANCE</p>
            <h1>Performance overview</h1>
            <p>{{ $scopeTitle }}</p>
            <span>Verified KPI results and live project activity or task measures for the selected reporting period.</span>
        </header>

        <form method="GET" class="grid gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:grid-cols-4">
            <label class="text-sm font-medium">Reporting period
                <select name="period" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-800">
                    @foreach($periods as $option)<option value="{{ $option->id }}" @selected($period?->id === $option->id)>{{ $option->name }}</option>@endforeach
                </select>
            </label>
            @if($organizationView || auth()->user()->can('performance.view_department'))
                <label class="text-sm font-medium">Department
                    <select name="department" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-800">
                        @if($organizationView)<option value="">All departments</option>@endif
                        @foreach($departmentOptions as $option)<option value="{{ $option->id }}" @selected((int) $departmentId === (int) $option->id)>{{ $option->name }}</option>@endforeach
                    </select>
                </label>
            @endif
            @if($organizationView || auth()->user()->can('performance.view_project'))
                <label class="text-sm font-medium">Project
                    <select name="project" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-800">
                        <option value="">All accessible projects</option>
                        @foreach($projectOptions as $option)<option value="{{ $option->id }}" @selected((int) $projectId === (int) $option->id)>{{ $option->translated('title') }}</option>@endforeach
                    </select>
                </label>
            @endif
            <div class="flex items-end"><button class="rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-800">Apply filters</button></div>
        </form>

        @if(!$period)
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">No reporting period configured</h2>
                <p class="mt-2 text-sm text-gray-500">Create a reporting period before setting targets and viewing performance.</p>
                @can('performance.manage_target')<a class="mt-4 inline-flex rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white" href="{{ \App\Filament\Resources\ReportingPeriodResource::getUrl('index') }}">Manage reporting periods</a>@endcan
            </section>
        @else
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Performance summary">
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-sm text-gray-500">Overall score</p><p class="mt-2 text-3xl font-bold">{{ $score !== null ? number_format($score, 1).'%' : '—' }}</p><p class="mt-1 text-sm text-primary-700 dark:text-primary-300">{{ $rating?->label ?? 'No rating' }}</p></article>
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-sm text-gray-500">KPIs on track</p><p class="mt-2 text-3xl font-bold">{{ number_format($onTrack) }}</p><p class="mt-1 text-sm text-gray-500">Verified or system-backed</p></article>
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-sm text-gray-500">Critical KPIs</p><p class="mt-2 text-3xl font-bold">{{ number_format($critical) }}</p><p class="mt-1 text-sm text-gray-500">Below configured threshold</p></article>
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-sm text-gray-500">Verified results</p><p class="mt-2 text-3xl font-bold">{{ number_format($resultCount) }}</p><p class="mt-1 text-sm text-gray-500">For this reporting period</p></article>
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-sm text-gray-500">Task completion</p><p class="mt-2 text-3xl font-bold">{{ $taskCompletion !== null ? number_format($taskCompletion, 1).'%' : '—' }}</p><p class="mt-1 text-sm text-gray-500">{{ number_format($taskTotal) }} scoped tasks</p></article>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3"><div><h2 class="text-lg font-semibold">KPI performance</h2><p class="text-sm text-gray-500">Targets compared with verified actuals or available live operational data.</p></div><span class="text-sm text-gray-500">{{ $period->name }}</span></div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300"><tr><th class="px-3 py-3">KPI</th><th class="px-3 py-3">Source</th><th class="px-3 py-3 text-right">Target</th><th class="px-3 py-3 text-right">Actual</th><th class="px-3 py-3 text-right">Achievement</th><th class="px-3 py-3 text-right">Weight</th><th class="px-3 py-3">Status</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($rows as $row)
                                <tr><td class="px-3 py-3"><div class="font-semibold">{{ $row['indicator']->name }}</div><div class="text-xs text-gray-500">{{ $row['indicator']->code }}</div></td><td class="px-3 py-3">{{ str($row['source'])->lower()->replace('_', ' ')->title() }}</td><td class="px-3 py-3 text-right tabular-nums">{{ number_format($row['target_value'], 2) }} {{ $row['indicator']->unit }}</td><td class="px-3 py-3 text-right tabular-nums">{{ $row['actual_value'] !== null ? number_format($row['actual_value'], 2).' '.($row['indicator']->unit ?? '') : '—' }}</td><td class="px-3 py-3 text-right tabular-nums">{{ $row['achievement_percentage'] !== null ? number_format($row['achievement_percentage'], 1).'%' : '—' }}</td><td class="px-3 py-3 text-right tabular-nums">{{ number_format($row['weight'], 1) }}%</td><td class="px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $row['status'] === 'ON_TRACK' ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : ($row['status'] === 'CRITICAL' ? 'bg-rose-50 text-rose-800 dark:bg-rose-950 dark:text-rose-200' : ($row['status'] === 'NEEDS_ATTENTION' ? 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')) }}">{{ str($row['status'])->lower()->replace('_', ' ')->title() }}</span></td></tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-10 text-center text-gray-500">No targets are set for this scope and period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($noData > 0)<p class="mt-3 text-xs text-gray-500">{{ $noData }} target(s) have no verified result or matching operational data yet.</p>@endif
            </section>

            @if($organizationView && $departmentPerformance->isNotEmpty())
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    <h2 class="mb-4 text-lg font-semibold">Department performance</h2>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach($departmentPerformance as $item)
                            <a href="{{ \App\Filament\Pages\PerformanceDashboard::getUrl(['department' => $item['department']->id, 'period' => $period->id]) }}" class="rounded-xl border border-gray-200 p-4 hover:border-primary-300 dark:border-gray-800 dark:hover:border-primary-700"><div class="font-semibold">{{ $item['department']->name }}</div><div class="mt-2 text-2xl font-bold">{{ $item['score'] !== null ? number_format($item['score'], 1).'%' : '—' }}</div><div class="text-xs text-gray-500">{{ $item['rating']?->label ?? 'No rating' }}</div></a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($organizationView && $projectPerformance->isNotEmpty())
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    <h2 class="mb-4 text-lg font-semibold">Project performance</h2>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach($projectPerformance as $item)
                            <a href="{{ \App\Filament\Pages\PerformanceDashboard::getUrl(['project' => $item['project']->id, 'period' => $period->id]) }}" class="rounded-xl border border-gray-200 p-4 hover:border-primary-300 dark:border-gray-800 dark:hover:border-primary-700"><div class="font-semibold">{{ $item['project']->translated('title') }}</div><div class="mt-2 text-2xl font-bold">{{ $item['score'] !== null ? number_format($item['score'], 1).'%' : '—' }}</div><div class="text-xs text-gray-500">{{ $item['rating']?->label ?? $item['project']->status->getLabel() }}</div></a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
    </div>
</x-filament-panels::page>

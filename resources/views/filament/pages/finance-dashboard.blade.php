<x-filament-panels::page>
    <div class="ughi-dashboard-shell space-y-8">
        <header class="ughi-dashboard-hero">
            <div class="ughi-dashboard-hero-orb ughi-dashboard-hero-orb-one" aria-hidden="true"></div>
            <div class="ughi-dashboard-hero-orb ughi-dashboard-hero-orb-two" aria-hidden="true"></div>
            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="ughi-dashboard-kicker">FINANCE · FOUNDATION PHASE</p>
                    <h1>Finance dashboard</h1>
                    <p>Finance Foundation</p>
                    <span>Manage financial-year setup, chart accounts and opening cash and bank balances. This phase does not include posted income or expenditure.</span>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <form method="GET" class="w-full sm:w-auto">
                        <label for="finance-year-filter" class="mb-1 block text-sm font-semibold text-gray-700">Financial year</label>
                        <select id="finance-year-filter" name="year" onchange="this.form.submit()" class="w-full rounded-lg border-white/70 bg-white/90 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:min-w-56">
                            <option value="">Current open year</option>
                            @foreach($years as $option)
                                <option value="{{ $option->id }}" @selected($year?->id === $option->id)>{{ $option->name }} · {{ $option->status }}</option>
                            @endforeach
                        </select>
                    </form>
                    @can('finance.year.manage')
                        <a href="{{ \App\Filament\Resources\FinancialYearResource::getUrl('index') }}" class="inline-flex items-center justify-center rounded-lg border border-white/80 bg-white/85 px-4 py-2 text-sm font-semibold text-primary-800 shadow-sm hover:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">Manage years</a>
                    @endcan
                </div>
            </div>
        </header>

        <section aria-labelledby="finance-summary-heading">
            <div class="ughi-dashboard-section-heading">
                <div>
                    <h2 id="finance-summary-heading" class="ughi-dashboard-section-title">Finance setup at a glance</h2>
                    <p>Active configuration for the finance foundation.</p>
                </div>
            </div>
            <div class="ughi-dashboard-stat-grid">
                @foreach($financeStats as $stat)
                    @include('filament.widgets.dashboard-stat-card', ['stat' => $stat])
                @endforeach
            </div>
        </section>

        <section aria-labelledby="opening-balances-heading">
            <div class="ughi-dashboard-section-heading">
                <div>
                    <h2 id="opening-balances-heading" class="ughi-dashboard-section-title">Opening balances</h2>
                    <p>Balances are grouped by currency. No exchange rates or conversions are applied.</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                @foreach([['Bank accounts', $bankOpeningByCurrency, $bankAccounts], ['Cash accounts', $cashOpeningByCurrency, $cashAccounts]] as [$title, $balances, $records])
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                        <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 dark:border-gray-800 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $title }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $records->count() }} active {{ \Illuminate\Support\Str::lower($title) }}</p>
                            </div>
                            @if($title === 'Bank accounts' && auth()->user()->can('finance.bank.manage'))
                                <a href="{{ \App\Filament\Resources\BankAccountResource::getUrl('index') }}" class="text-sm font-semibold text-primary-700 hover:underline dark:text-primary-400">Manage banks</a>
                            @elseif($title === 'Cash accounts' && auth()->user()->can('finance.cash.manage'))
                                <a href="{{ \App\Filament\Resources\CashAccountResource::getUrl('index') }}" class="text-sm font-semibold text-primary-700 hover:underline dark:text-primary-400">Manage cash</a>
                            @endif
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse($balances as $balance)
                                <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $balance->currency }}</span>
                                    <strong class="ml-2 text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format((float) $balance->total, 2) }}</strong>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No opening balances recorded.</p>
                            @endforelse
                        </div>

                        <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($records as $record)
                                <div class="flex items-start justify-between gap-4 py-3">
                                    <div class="min-w-0">
                                        <div class="truncate font-medium text-gray-900 dark:text-white">{{ $record->bank_name ?? $record->name }}{{ isset($record->account_name) ? ' · '.$record->account_name : '' }}</div>
                                        <div class="mt-1 text-sm text-gray-500">{{ $record->department?->name ?? $record->account?->code }}</div>
                                        @if($record->masked_account_number)
                                            <div class="mt-1 text-xs text-gray-500">{{ $record->masked_account_number }}</div>
                                        @endif
                                    </div>
                                    <div class="shrink-0 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ $record->currency }} {{ number_format((float) $record->opening_balance, 2) }}</div>
                                </div>
                            @empty
                                <p class="py-4 text-sm text-gray-500">No active accounts configured.</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    </div>
</x-filament-panels::page>

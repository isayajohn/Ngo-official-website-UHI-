<x-filament-panels::page>
    <div class="ughi-dashboard-shell space-y-8">
        <header class="ughi-dashboard-hero">
            <div class="ughi-dashboard-hero-orb ughi-dashboard-hero-orb-one" aria-hidden="true"></div>
            <div class="ughi-dashboard-hero-orb ughi-dashboard-hero-orb-two" aria-hidden="true"></div>
            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="ughi-dashboard-kicker">ORGANIZATION · EXECUTIVE OVERVIEW</p>
                    <h1>Organization overview</h1>
                    <p>{{ $scopeLabel }}</p>
                    <span>Operational health across teams, projects, work and decisions. Figures reflect current database records.</span>
                </div>
                <form method="GET" class="w-full sm:w-auto">
                    <label for="organization-department-filter" class="mb-1 block text-sm font-semibold text-gray-700">Filter department</label>
                    <select id="organization-department-filter" name="department" onchange="this.form.submit()" class="w-full rounded-lg border-white/70 bg-white/90 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:min-w-64">
                        <option value="">All active departments</option>
                        @foreach($departmentOptions as $option)
                            <option value="{{ $option->id }}" @selected((string) $departmentId === (string) $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </header>

        <section aria-labelledby="organization-summary-heading">
            <div class="ughi-dashboard-section-heading">
                <div>
                    <h2 id="organization-summary-heading" class="ughi-dashboard-section-title">Organization at a glance</h2>
                    <p>Current operational indicators · overdue tasks are calculated from due dates.</p>
                </div>
            </div>
            <div class="ughi-dashboard-stat-grid ughi-dashboard-stat-grid-organization">
                @foreach($organizationStats as $stat)
                    @include('filament.widgets.dashboard-stat-card', ['stat' => $stat])
                @endforeach
            </div>
        </section>

        <section aria-labelledby="department-performance-heading" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
            <div class="ughi-dashboard-section-heading">
                <div>
                    <h2 id="department-performance-heading" class="ughi-dashboard-section-title">Department performance</h2>
                    <p>Leadership, staffing, active projects and outstanding work.</p>
                </div>
            </div>
            <x-data-table label="Department performance">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800">
                            <th scope="col" class="py-3 pr-4">Department</th>
                            <th scope="col" class="py-3 pr-4">Head</th>
                            <th scope="col" class="py-3 pr-4 text-right">Staff</th>
                            <th scope="col" class="py-3 pr-4 text-right">Active projects</th>
                            <th scope="col" class="py-3 pr-4 text-right">Open tasks</th>
                            <th scope="col" class="py-3 text-right">Overdue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($departments as $department)
                            @php
                                $departmentHead = $department->activeLeadership->firstWhere('leadership_role', 'HEAD')?->teamMember
                                    ?? $department->activeLeadership->firstWhere('leadership_role', 'ACTING_HEAD')?->teamMember
                                    ?? $department->head;
                            @endphp
                            <tr>
                                <td class="py-4 pr-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $department->name }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $department->code }}</div>
                                </td>
                                <td class="py-4 pr-4 text-gray-600 dark:text-gray-300">{{ $departmentHead?->name ?? 'Unassigned' }}</td>
                                <td class="py-4 pr-4 text-right tabular-nums">{{ number_format($department->team_members_count) }}</td>
                                <td class="py-4 pr-4 text-right tabular-nums">{{ number_format($department->active_projects_count) }}</td>
                                <td class="py-4 pr-4 text-right tabular-nums">{{ number_format($department->open_tasks_count) }}</td>
                                <td class="py-4 text-right">
                                    <span class="inline-flex min-w-8 justify-center rounded-full px-2 py-1 text-xs font-semibold {{ $department->overdue_tasks_count > 0 ? 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">{{ number_format($department->overdue_tasks_count) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-sm text-gray-500">No active departments found.</td></tr>
                        @endforelse
                    </tbody>
            </x-data-table>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <h2 class="ughi-dashboard-section-title">Projects by status</h2>
                @php $maxProjectCount = max($projectsByStatus->max() ?? 0, 1); @endphp
                @forelse($projectsByStatus as $status => $count)
                    <div class="mb-4 last:mb-0">
                        <div class="mb-1.5 flex justify-between gap-3 text-sm"><span class="text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', strtolower($status)) }}</span><span class="font-semibold tabular-nums">{{ number_format($count) }}</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-full rounded-full bg-primary-600" style="width: {{ round($count / $maxProjectCount * 100) }}%" role="progressbar" aria-label="{{ str_replace('_', ' ', $status) }} projects" aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $maxProjectCount }}"></div></div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No project records in this view.</p>
                @endforelse
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <h2 class="ughi-dashboard-section-title">Tasks by status</h2>
                @php $maxTaskCount = max($tasksByStatus->max() ?? 0, 1); @endphp
                @forelse($tasksByStatus as $status => $count)
                    <div class="mb-4 last:mb-0">
                        <div class="mb-1.5 flex justify-between gap-3 text-sm"><span class="text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', strtolower($status)) }}</span><span class="font-semibold tabular-nums">{{ number_format($count) }}</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-full rounded-full {{ $status === 'OVERDUE' ? 'bg-amber-500' : 'bg-[#06477F]' }}" style="width: {{ round($count / $maxTaskCount * 100) }}%" role="progressbar" aria-label="{{ str_replace('_', ' ', $status) }} tasks" aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $maxTaskCount }}"></div></div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No task records in this view.</p>
                @endforelse
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <h2 class="ughi-dashboard-section-title">Task completion · last 6 months</h2>
                <div class="flex h-40 items-end gap-2" role="img" aria-label="Monthly completed tasks across the selected organization view">
                    @foreach($monthlyTaskCompletion as $month => $count)
                        <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                            <span class="text-xs font-medium tabular-nums text-gray-500">{{ $count }}</span>
                            <div class="w-full rounded-t bg-primary-600" style="height: {{ $count > 0 ? max(6, round($count / max($monthlyTaskCompletion->max(), 1) * 70)) : 2 }}%" title="{{ $month }}: {{ $count }} tasks completed"></div>
                            <span class="truncate text-[10px] text-gray-500">{{ \Illuminate\Support\Str::before($month, ' ') }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="ughi-dashboard-section-heading">
                    <div><h2 class="ughi-dashboard-section-title">Approvals awaiting action</h2><p>Latest requests routed to departments.</p></div>
                    <a href="{{ \App\Filament\Resources\ApprovalRequestResource::getUrl('index') }}" class="text-sm font-semibold text-primary-700 hover:underline dark:text-primary-400">Open inbox</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($pendingApprovalItems as $approval)
                        <div class="flex items-start justify-between gap-4 py-3">
                            <div class="min-w-0"><div class="truncate font-semibold text-gray-900 dark:text-white">{{ $approval->display_title }}</div><div class="mt-1 text-sm text-gray-500">{{ $approval->department?->name ?? 'Organization-wide' }} · {{ $approval->requestedBy?->name ?? 'Unknown submitter' }}</div><div class="mt-1 text-xs text-gray-500">{{ $approval->submitted_at?->diffForHumans() ?? 'Submission date not recorded' }} · Step {{ $approval->current_step }}</div></div>
                            <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">{{ str_replace('_', ' ', $approval->status) }}</span>
                        </div>
                    @empty
                        <p class="py-4 text-sm text-gray-500">No approvals are currently pending.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="ughi-dashboard-section-heading"><div><h2 class="ughi-dashboard-section-title">Upcoming deadlines</h2><p>Tasks and active project end dates.</p></div></div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($upcomingDeadlines as $deadline)
                        <div class="flex items-center justify-between gap-4 py-3">
                            <div class="min-w-0"><div class="truncate font-semibold text-gray-900 dark:text-white">{{ $deadline->title }}</div><div class="mt-1 text-xs text-gray-500">{{ $deadline->type }} · {{ $deadline->department ?? 'Organization-wide' }}</div></div>
                            <time datetime="{{ $deadline->due_at->toDateString() }}" class="shrink-0 text-sm font-medium text-gray-600 dark:text-gray-300">{{ $deadline->due_at->format('M j, Y') }}</time>
                        </div>
                    @empty
                        <p class="py-4 text-sm text-gray-500">No upcoming task or active project deadlines.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
            <div class="ughi-dashboard-section-heading"><div><h2 class="ughi-dashboard-section-title">Recent department activity</h2><p>Latest leadership, task and approval changes.</p></div></div>
            <div class="grid gap-x-8 md:grid-cols-2">
                @forelse($activity as $entry)
                    <div class="border-t border-gray-100 py-3 dark:border-gray-800">
                        <div class="text-sm"><span class="font-semibold text-gray-900 dark:text-white">{{ $entry->user?->name ?? 'System' }}</span><span class="text-gray-600 dark:text-gray-300"> {{ str_replace(['.', '_'], ' ', $entry->event) }}</span></div>
                        <div class="mt-1 flex justify-between gap-3 text-xs text-gray-500"><span>{{ $entry->department?->name }}</span><time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->diffForHumans() }}</time></div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-gray-500">No recent activity recorded.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="ughi-dashboard-shell space-y-6">
        <header class="ughi-dashboard-hero">
            <div class="ughi-dashboard-hero-orb ughi-dashboard-hero-orb-one" aria-hidden="true"></div>
            <div class="ughi-dashboard-hero-orb ughi-dashboard-hero-orb-two" aria-hidden="true"></div>
            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="ughi-dashboard-kicker">ORGANIZATION · DEPARTMENT OPERATIONS</p>
                    <h1>Department overview</h1>
                    <p>{{ $department?->name ?? 'Your department workspace' }}</p>
                    <span>{{ $department?->short_description ?: 'Track your team, projects, tasks and decisions in one place.' }}</span>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <form method="GET" class="w-full max-w-sm">
                <label for="department-filter" class="mb-1 block text-sm font-semibold text-gray-700">Department</label>
                <select id="department-filter" name="department" onchange="this.form.submit()" class="w-full rounded-lg border-white/70 bg-white/90 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @foreach($departments as $option)
                        <option value="{{ $option->id }}" @selected($department?->id === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </form>

            @if($department && auth()->user()->can('department.assign_task'))
                <a href="{{ \App\Filament\Resources\DepartmentTaskResource::getUrl('create', ['department_id' => $department->id]) }}" class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    Assign a task
                </a>
            @endif
                </div>
            </div>
        </header>

        @if($department)
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Department lead</p>
                        <h2 class="text-xl font-semibold text-gray-950 dark:text-white">{{ $head?->name ?? 'Not assigned' }}</h2>
                        @if($head?->position)
                            <p class="mt-1 text-sm text-gray-500">{{ $head->position->title }}</p>
                        @endif
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-800 dark:bg-primary-950 dark:text-primary-200">{{ $department->active ? 'Active department' : 'Inactive department' }}</span>
                </div>
            </section>

            <section aria-labelledby="department-summary-heading">
                <h2 id="department-summary-heading" class="ughi-dashboard-section-title">Department at a glance</h2>
                <div class="ughi-dashboard-stat-grid ughi-dashboard-stat-grid-department">
                    @foreach($departmentStats as $stat)
                        @include('filament.widgets.dashboard-stat-card', ['stat' => $stat])
                    @endforeach
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold">Department leadership</h3>
                            <p class="text-sm text-gray-500">Current appointments and responsibilities</p>
                        </div>
                        @can('department.manage_leadership')
                            <a class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-400" href="{{ \App\Filament\Resources\DepartmentLeadershipResource::getUrl('index', ['tableFilters[department_id][value]' => $department->id]) }}">Manage leadership</a>
                        @endcan
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($department->activeLeadership as $appointment)
                            <div class="flex items-start justify-between gap-4 py-3">
                                <div>
                                    <div class="font-medium">{{ $appointment->teamMember?->name ?? 'Unassigned team member' }}</div>
                                    <div class="text-sm text-gray-500">{{ str_replace('_', ' ', $appointment->leadership_role) }}@if($appointment->teamMember?->position) · {{ $appointment->teamMember->position->title }}@endif</div>
                                </div>
                                <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-800 dark:bg-primary-950 dark:text-primary-200">{{ $appointment->appointment_status }}</span>
                            </div>
                        @empty
                            <p class="py-3 text-sm text-gray-500">No active leadership appointments recorded.</p>
                        @endforelse
                        @if($department->activeLeadership->isEmpty() && $department->head)
                            <div class="py-3">
                                <div class="font-medium">{{ $department->head->name }}</div>
                                <div class="text-sm text-gray-500">Legacy department head assignment</div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold">Projects</h3>
                            <p class="text-sm text-gray-500">Lead and supporting department projects</p>
                        </div>
                        <a class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-400" href="{{ \App\Filament\Resources\ProjectResource::getUrl('index') }}">View projects</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($projects as $project)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <div class="truncate font-medium">{{ $project->translated('title') }}</div>
                                    <div class="text-sm text-gray-500">{{ $project->programme?->translated('title') ?? 'Programme not assigned' }} · {{ $project->pivot?->is_lead ? 'Lead department' : 'Supporting department' }}</div>
                                </div>
                                <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium dark:bg-gray-800">{{ $project->status->getLabel() }}</span>
                            </div>
                        @empty
                            <p class="py-3 text-sm text-gray-500">No projects are assigned to this department.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div><h3 class="text-lg font-semibold">Staff workload</h3><p class="text-sm text-gray-500">Active staff and their open assigned tasks</p></div>
                        <a class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-400" href="{{ \App\Filament\Resources\TeamMemberResource::getUrl('index') }}">View team</a>
                    </div>
                    <x-data-table label="Staff workload">
                            <thead><tr class="border-b border-gray-200 text-gray-500 dark:border-gray-800"><th scope="col" class="py-2 pr-4 font-medium">Staff member</th><th scope="col" class="py-2 pr-4 font-medium">Position</th><th scope="col" class="py-2 text-right font-medium">Open tasks</th></tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($staff as $member)
                                    <tr><td class="py-3 pr-4 font-medium">{{ $member->name }}</td><td class="py-3 pr-4 text-gray-500">{{ $member->position?->title ?? 'Position not assigned' }}</td><td class="py-3 text-right tabular-nums">{{ $member->open_task_count }}</td></tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-gray-500">No active team members recorded.</td></tr>
                                @endforelse
                            </tbody>
                    </x-data-table>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">Upcoming deadlines</h3>
                        <p class="text-sm text-gray-500">Tasks and project milestones, ordered by due date</p>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($upcomingDeadlines as $deadline)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0"><div class="truncate font-medium">{{ $deadline->title }}</div><div class="text-xs text-gray-500">{{ $deadline->type }} · {{ str_replace('_', ' ', $deadline->status) }}</div></div>
                                <time datetime="{{ $deadline->due_at->toDateString() }}" class="shrink-0 text-sm text-gray-600 dark:text-gray-300">{{ $deadline->due_at->format('M j, Y') }}</time>
                            </div>
                        @empty
                            <p class="py-3 text-sm text-gray-500">No upcoming task or project milestone deadlines.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div><h3 class="text-lg font-semibold">Approvals awaiting action</h3><p class="text-sm text-gray-500">Requests routed to this department</p></div>
                        <a class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-400" href="{{ \App\Filament\Resources\ApprovalRequestResource::getUrl('index') }}">Open approval inbox</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($pendingApprovalItems as $approval)
                            <div class="flex items-start justify-between gap-4 py-3">
                                <div class="min-w-0"><div class="truncate font-medium">{{ $approval->display_title }}</div><div class="text-sm text-gray-500">{{ $approval->requestedBy?->name ?? 'Unknown submitter' }} · Step {{ $approval->current_step }}</div></div>
                                <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-200">{{ str_replace('_', ' ', $approval->status) }}</span>
                            </div>
                        @empty
                            <p class="py-3 text-sm text-gray-500">No approvals are currently pending.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4"><h3 class="text-lg font-semibold">Recent department activity</h3><p class="text-sm text-gray-500">Latest leadership, task and approval changes</p></div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($activity as $entry)
                            <div class="py-3"><div><span class="font-medium">{{ $entry->user?->name ?? 'System' }}</span> <span class="text-gray-600">{{ str_replace(['.', '_'], [' ', ' '], $entry->event) }}</span></div><time datetime="{{ $entry->created_at->toIso8601String() }}" class="text-xs text-gray-500">{{ $entry->created_at->diffForHumans() }}</time></div>
                        @empty
                            <p class="py-3 text-sm text-gray-500">No recent activity recorded.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Tasks by status</h3>
                    @forelse($tasksByStatus as $status => $count)
                        <div class="mb-3 last:mb-0"><div class="mb-1 flex justify-between text-sm"><span>{{ str_replace('_', ' ', $status) }}</span><span class="tabular-nums">{{ $count }}</span></div><div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800"><div role="progressbar" aria-label="{{ str_replace('_', ' ', $status) }} tasks" aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ max($tasksByStatus->max(), 1) }}" class="h-2 rounded-full bg-primary-600" style="width: {{ round($count / max($tasksByStatus->max(), 1) * 100) }}%"></div></div></div>
                    @empty
                        <p class="text-sm text-gray-500">No task data yet.</p>
                    @endforelse
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Projects by status</h3>
                    @forelse($projectsByStatus as $status => $count)
                        <div class="mb-3 last:mb-0"><div class="mb-1 flex justify-between text-sm"><span>{{ str_replace('_', ' ', $status) }}</span><span class="tabular-nums">{{ $count }}</span></div><div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800"><div role="progressbar" aria-label="{{ str_replace('_', ' ', $status) }} projects" aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ max($projectsByStatus->max(), 1) }}" class="h-2 rounded-full bg-[#06477F]" style="width: {{ round($count / max($projectsByStatus->max(), 1) * 100) }}%"></div></div></div>
                    @empty
                        <p class="text-sm text-gray-500">No project data yet.</p>
                    @endforelse
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Task completion by month</h3>
                    <div class="flex h-36 items-end gap-2" role="img" aria-label="Completed department tasks in the last six months">
                        @foreach($monthlyTaskCompletion as $month => $count)
                            <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                                <span class="text-xs tabular-nums text-gray-500">{{ $count }}</span>
                                <div class="w-full rounded-t bg-primary-600" style="height: {{ $count > 0 ? max(8, round($count / max($monthlyTaskCompletion->max(), 1) * 72)) : 2 }}%" title="{{ $month }}: {{ $count }} completed"></div>
                                <span class="truncate text-[10px] text-gray-500">{{ \Illuminate\Support\Str::before($month, ' ') }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        @else
            <section class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">No active departments to show</h2>
                <p class="mt-2 text-sm text-gray-500">Assign your account to a department or activate a department to use this control panel.</p>
                @can('department.create')
                    <a class="mt-4 inline-flex text-sm font-medium text-primary-700 hover:underline dark:text-primary-400" href="{{ \App\Filament\Resources\DepartmentResource::getUrl('index') }}">Manage departments</a>
                @endcan
            </section>
        @endif
    </div>
</x-filament-panels::page>

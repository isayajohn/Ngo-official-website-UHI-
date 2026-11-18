<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['My tasks', $myTasks->count(), 'heroicon-o-clipboard-document-check'],
                ['Pending my approval', $pendingApprovals->count(), 'heroicon-o-inbox-stack'],
                ['Overdue tasks', $overdue->count(), 'heroicon-o-clock'],
                ['Returned items', $returnedItems->count(), 'heroicon-o-arrow-uturn-left'],
            ] as [$label, $count, $icon])
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between"><p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p><x-filament::icon :icon="$icon" class="h-5 w-5 text-primary-600" /></div>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ number_format($count) }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div><h2 class="text-lg font-semibold">My tasks</h2><p class="text-sm text-gray-500">Tasks assigned to your account or linked staff profile.</p></div>
                    @canany(['department.view', 'department.assign_task', 'organization.full_access'])<a href="{{ \App\Filament\Resources\DepartmentTaskResource::getUrl('index') }}" class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-300">All department tasks</a>@endcanany
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($myTasks as $task)
                        <article class="py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-medium">{{ $task->title }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ $task->department?->name ?? 'Department' }} · Due {{ $task->due_date?->format('M j, Y') ?? 'No due date' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ str($task->effective_status)->lower()->replace('_', ' ')->title() }} · {{ str($task->priority)->lower()->title() }} priority</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @can('update', $task)
                                        <a href="{{ \App\Filament\Resources\DepartmentTaskResource::getUrl('edit', ['record' => $task]) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Open task</a>
                                    @endcan
                                    @if(! in_array($task->status, ['COMPLETED', 'CANCELLED'], true))
                                        <form method="POST" action="{{ route('department-tasks.complete', $task) }}" class="flex items-center gap-2">
                                            @csrf
                                            <input name="completion_note" aria-label="Completion note for {{ $task->title }}" class="w-40 rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800" placeholder="Completion note">
                                            <button class="rounded-lg bg-primary-700 px-3 py-2 text-xs font-semibold text-white hover:bg-primary-800">Complete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @can('view', $task)
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs font-medium text-primary-700 dark:text-primary-300">Add a comment</summary>
                                    <form method="POST" action="{{ route('department-tasks.comments', $task) }}" class="mt-2 flex flex-wrap gap-2">
                                        @csrf
                                        <input name="comment" required maxlength="10000" aria-label="Comment on {{ $task->title }}" class="min-w-48 flex-1 rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800" placeholder="Write a task update">
                                        <button class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Post comment</button>
                                    </form>
                                </details>
                            @endcan
                        </article>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-500">No tasks are assigned to you.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3"><h2 class="text-lg font-semibold">Pending my approval</h2><p class="text-sm text-gray-500">Review requests at a workflow step assigned to your permissions or department role.</p></div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($pendingApprovals as $approval)
                        <article class="py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div><h3 class="font-medium">{{ $approval->approvable && method_exists($approval->approvable, 'translated') ? $approval->approvable->translated('title') : ($approval->approvable?->title ?? $approval->workflow?->name ?? 'Approval request') }}</h3><p class="mt-1 text-sm text-gray-500">{{ $approval->department?->name ?? 'Organization-wide' }} · Submitted by {{ $approval->requestedBy?->name ?? 'Unknown' }}</p><p class="mt-1 text-xs text-gray-500">Step {{ $approval->current_step }} · {{ $approval->submitted_at?->format('M j, Y') }}</p></div>
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">{{ str($approval->status)->lower()->replace('_', ' ')->title() }}</span>
                            </div>
                            <form method="POST" action="{{ route('my-work.approval-action', $approval) }}" class="mt-3 space-y-2">
                                @csrf
                                <textarea name="comment" rows="2" maxlength="10000" aria-label="Approval comment for {{ $approval->workflow?->name }}" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800" placeholder="Comment required to return or reject"></textarea>
                                <div class="flex flex-wrap gap-2">
                                    <button name="action" value="APPROVED" class="rounded-lg bg-primary-700 px-3 py-2 text-xs font-semibold text-white hover:bg-primary-800">Approve</button>
                                    <button name="action" value="RETURNED" class="rounded-lg border border-amber-300 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-200 dark:hover:bg-amber-950">Return</button>
                                    <button name="action" value="REJECTED" class="rounded-lg border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-800 dark:text-rose-200 dark:hover:bg-rose-950">Reject</button>
                                </div>
                            </form>
                        </article>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-500">No approval requests are waiting for you.</p>
                    @endforelse
                </div>
                <a href="{{ \App\Filament\Resources\ApprovalRequestResource::getUrl('index') }}" class="mt-3 inline-flex text-sm font-medium text-primary-700 hover:underline dark:text-primary-300">Open approval inbox</a>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3"><h2 class="text-lg font-semibold">Tasks assigned by me</h2><p class="text-sm text-gray-500">Follow up on work you delegated.</p></div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($assignedByMe as $task)
                        <div class="flex items-center justify-between gap-3 py-3"><div><p class="font-medium">{{ $task->title }}</p><p class="text-sm text-gray-500">{{ $task->assignedToUser?->name ?? $task->assignedToTeamMember?->name ?? 'Unassigned' }} · {{ $task->department?->name }}</p></div><div class="flex items-center gap-3"><span class="text-xs text-gray-500">{{ str($task->effective_status)->lower()->replace('_', ' ')->title() }}</span>@can('update', $task)<a href="{{ \App\Filament\Resources\DepartmentTaskResource::getUrl('edit', ['record' => $task]) }}" class="text-xs font-semibold text-primary-700 hover:underline dark:text-primary-300">Open</a>@endcan</div></div>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-500">You have not assigned any tasks.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3"><h2 class="text-lg font-semibold">Upcoming deadlines</h2><p class="text-sm text-gray-500">Your task and project milestone due dates.</p></div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($upcoming as $task)
                        <div class="flex items-center justify-between gap-3 py-3"><div><p class="font-medium">{{ $task->title }}</p><p class="text-sm text-gray-500">Task · {{ $task->department?->name }}</p></div><span class="text-sm font-semibold">{{ $task->due_date?->format('M j, Y') }}</span></div>
                    @empty
                        @if($upcomingMilestones->isEmpty())<p class="py-5 text-center text-sm text-gray-500">No upcoming deadlines.</p>@endif
                    @endforelse
                    @foreach($upcomingMilestones as $milestone)
                        <div class="flex items-center justify-between gap-3 py-3"><div><p class="font-medium">{{ $milestone->title }}</p><p class="text-sm text-gray-500">Project milestone · {{ $milestone->project?->translated('title') }}</p></div><span class="text-sm font-semibold">{{ $milestone->due_date?->format('M j, Y') }}</span></div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3"><h2 class="text-lg font-semibold">Returned items</h2><p class="text-sm text-gray-500">Requests returned to you with reviewer comments.</p></div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($returnedItems as $item)
                        <div class="flex items-center justify-between gap-3 py-3"><div><p class="font-medium">{{ $item->approvable && method_exists($item->approvable, 'translated') ? $item->approvable->translated('title') : ($item->approvable?->title ?? $item->workflow?->name ?? 'Approval request') }}</p><p class="text-sm text-gray-500">{{ $item->department?->name ?? 'Organization-wide' }} · {{ $item->submitted_at?->format('M j, Y') }}</p></div><span class="text-xs font-semibold text-amber-800 dark:text-amber-200">Returned</span></div>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-500">No returned items.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3"><h2 class="text-lg font-semibold">Overdue tasks</h2><p class="text-sm text-gray-500">Items past their due date that are still open.</p></div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($overdue as $task)
                        <div class="flex items-center justify-between gap-3 py-3"><div><p class="font-medium">{{ $task->title }}</p><p class="text-sm text-gray-500">{{ $task->department?->name }}</p></div><span class="text-sm font-semibold text-rose-700 dark:text-rose-300">{{ $task->due_date?->format('M j, Y') }}</span></div>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-500">You have no overdue tasks.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <div class="mb-3"><h2 class="text-lg font-semibold">Recent activity</h2><p class="text-sm text-gray-500">Latest changes in departments you belong to or lead.</p></div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($recentActivity as $entry)
                        <div class="flex flex-wrap items-center justify-between gap-2 py-3"><div><p class="font-medium">{{ $entry->user?->name ?? 'System' }} · {{ $entry->department?->name }}</p><p class="text-sm text-gray-600 dark:text-gray-300">{{ str_replace('.', ' ', $entry->event) }}</p></div><time class="text-xs text-gray-500">{{ $entry->created_at->diffForHumans() }}</time></div>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-500">No recent activity in your departments.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>

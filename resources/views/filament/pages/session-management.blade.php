<x-filament-panels::page>
    @if(! $sessionsAvailable)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
            Session inventory requires Laravel's database session driver. Configure <code>SESSION_DRIVER=database</code> to use these controls.
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Your active sessions</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Review devices signed in to your account and end any session you do not recognize.</p>
                </div>

                <div class="space-y-3">
                    @forelse($sessions as $session)
                        <article wire:key="session-{{ $session->management_key }}" class="flex flex-col gap-4 rounded-xl border border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                                    <x-filament::icon icon="heroicon-o-computer-desktop" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-medium text-gray-950 dark:text-white">{{ $session->browser }}</h3>
                                        @if($session->is_current)
                                            <span class="rounded-full bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-800 dark:bg-primary-950 dark:text-primary-200">This device</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $session->ip_address ?: 'IP address unavailable' }} · Last active {{ $session->last_active_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @unless($session->is_current)
                                <button type="button" wire:click="revokeSession('{{ $session->management_key }}')" wire:confirm="End this session? Remembered sign-ins across devices will also be reset." wire:loading.attr="disabled" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:opacity-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950">
                                    End session
                                </button>
                            @endunless
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">No active session records were found.</p>
                    @endforelse
                </div>
            </section>

            <aside class="h-fit rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Sign out other devices</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Enter your password to end every other session. This device stays signed in; remembered sign-ins across devices are reset.</p>
                <form wire:submit="revokeOtherSessions" class="mt-4 space-y-3">
                    <div>
                        <label for="session-password" class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-gray-200">Current password</label>
                        <input id="session-password" type="password" wire:model="password" autocomplete="current-password" required class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <x-filament::button type="submit" color="danger" icon="heroicon-o-arrow-left-start-on-rectangle" wire:loading.attr="disabled">Sign out other sessions</x-filament::button>
                </form>
            </aside>
        </div>
    @endif
</x-filament-panels::page>

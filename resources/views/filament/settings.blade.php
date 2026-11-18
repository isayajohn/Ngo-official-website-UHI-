<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}
        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 pt-5 dark:border-white/10">
            <p class="text-sm text-gray-500 dark:text-gray-400">Save the active tab as you go, or save all tabs together here.</p>
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Save all changes</span><span wire:loading wire:target="save">Saving…</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

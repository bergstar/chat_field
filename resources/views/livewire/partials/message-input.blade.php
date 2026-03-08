<div class="border-t border-gray-200 p-4 dark:border-white/10">
    @if ($sendError)
        <div class="mb-3 rounded-xl border border-danger-200 bg-danger-50 px-3 py-2 text-sm text-danger-700 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-200">
            {{ $sendError }}
        </div>
    @endif

    <div class="flex items-end gap-4">
        <div class="max-h-96 w-full overflow-y-auto">
            {{ $this->form }}
        </div>

        <x-filament::button type="button" wire:click="sendMessage" wire:loading.attr="disabled" icon="heroicon-m-paper-airplane" class="!gap-0" />
    </div>
</div>

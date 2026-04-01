<div class="border-t border-gray-200 p-4 dark:border-white/10">
    @if ($sendError)
        <div class="mb-3 rounded-xl border border-danger-200 bg-danger-50 px-3 py-2 text-sm text-danger-700 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-200">
            {{ $sendError }}
        </div>
    @endif

    @if ($readOnly)
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
            {{ $readOnlyNotice ?: __('chat-field::chat-field.messages.read_only') }}
        </div>
    @else
        <div class="chat-field-composer flex items-end gap-3">
            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-m-plus"
                wire:click="openUploadPicker"
                x-on:click.stop="$root.querySelector('.chat-field-filepond input[type=file]')?.click()"
                wire:loading.attr="disabled"
                class="!gap-0 shrink-0"
            />

            <div class="w-full">
                {{ $this->form }}
            </div>

            <x-filament::button
                type="button"
                wire:click="sendMessage"
                wire:loading.attr="disabled"
                icon="heroicon-m-paper-airplane"
                class="!gap-0 shrink-0"
            />
        </div>
    @endif
</div>

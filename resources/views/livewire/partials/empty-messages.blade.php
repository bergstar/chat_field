<div class="flex flex-1 items-center justify-center px-6 py-10 text-center">
    <div class="max-w-sm space-y-2">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-200">
            <x-filament::icon icon="heroicon-m-chat-bubble-left-right" class="h-5 w-5" />
        </div>

        <p class="text-sm font-semibold text-gray-950 dark:text-white">
            {{ __('No messages yet') }}
        </p>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Start the conversation for this record.') }}
        </p>
    </div>
</div>

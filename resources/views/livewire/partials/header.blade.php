<div class="flex items-center gap-3 border-b border-gray-200 px-5 py-4 dark:border-white/10">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-700 dark:bg-primary-500/15 dark:text-primary-200">
        {{ $this->ownerInitials() }}
    </div>

    <div class="min-w-0">
        <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">
            {{ __('Chat') }}
        </p>

        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
            {{ $this->ownerMetaLabel() }}: {{ $this->ownerLabel() }}
        </p>
    </div>
</div>

<div wire:key="message-{{ $message->getKey() }}">
    @if ($showDateDivider)
        <div class="my-4 flex justify-center">
            <x-filament::badge color="gray">
                {{ $this->formatDividerDate($message->created_at) }}
            </x-filament::badge>
        </div>
    @endif

    @if (! $isClient)
        <div class="mb-2 flex items-end gap-2">
            @if ($showIncomingAvatar)
                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-50 text-[11px] font-semibold text-primary-700 dark:bg-primary-500/15 dark:text-primary-200">
                    {{ $this->messageAuthorInitials($message) }}
                </div>
            @else
                <div class="h-7 w-7"></div>
            @endif

            <div class="max-w-[85%] rounded-2xl rounded-bl-md bg-gray-100 px-3 py-2 text-sm text-gray-900 dark:bg-white/10 dark:text-white md:max-w-xl">
                @if ($message->body)
                    <p class="whitespace-pre-wrap">{{ $message->body }}</p>
                @endif

                @foreach ($message->attachments ?? [] as $attachment)
                    @include('chat-field::livewire.partials.attachment-item', [
                        'attachment' => $attachment,
                        'message' => $message,
                        'buttonClasses' => 'mt-2 flex w-full items-center gap-2 rounded-xl bg-white px-3 py-2 text-left text-gray-700 ring-1 ring-gray-200 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:ring-white/10 dark:hover:bg-gray-700',
                        'iconWrapperClasses' => 'inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-white',
                        'labelClasses' => 'truncate text-sm',
                    ])
                @endforeach

                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $this->messageMetaLabel($message) }}
                </p>
            </div>
        </div>
    @else
        <div class="mb-2 flex flex-col items-end gap-1">
            <div class="max-w-[85%] rounded-2xl rounded-br-md bg-primary-600 px-3 py-2 text-sm text-white md:max-w-xl">
                @if ($message->body)
                    <p class="whitespace-pre-wrap">{{ $message->body }}</p>
                @endif

                @foreach ($message->attachments ?? [] as $attachment)
                    @include('chat-field::livewire.partials.attachment-item', [
                        'attachment' => $attachment,
                        'message' => $message,
                        'buttonClasses' => 'mt-2 flex w-full items-center gap-2 rounded-xl bg-primary-500/80 px-3 py-2 text-left text-white ring-1 ring-white/10 transition hover:bg-primary-500',
                        'iconWrapperClasses' => 'inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary-700 text-white',
                        'labelClasses' => 'truncate text-sm text-white',
                    ])
                @endforeach

                <p class="mt-2 text-xs text-primary-100/80">
                    {{ $this->messageMetaLabel($message) }}
                </p>
            </div>
        </div>
    @endif
</div>

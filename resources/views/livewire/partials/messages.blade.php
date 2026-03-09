<div
    id="chat-field-window-container"
    class="chat-field-scroll flex flex-1 flex-col-reverse overflow-y-auto p-5"
>
    @if ($threadMessages->isEmpty())
        @include('chat-field::livewire.partials.empty-messages')
    @endif

    @foreach ($threadMessages as $index => $message)
        @php
            $nextMessage = $threadMessages[$index + 1] ?? null;
            $previousMessage = $threadMessages[$index - 1] ?? null;
            $timezone = config('chat-field.timezone') ?: config('app.timezone', 'UTC');

            $currentDate = \Illuminate\Support\Carbon::parse($message->created_at)
                ->setTimezone($timezone)
                ->format('Y-m-d');

            $nextDate = $nextMessage
                ? \Illuminate\Support\Carbon::parse($nextMessage->created_at)
                    ->setTimezone($timezone)
                    ->format('Y-m-d')
                : null;

            $previousDate = $previousMessage
                ? \Illuminate\Support\Carbon::parse($previousMessage->created_at)
                    ->setTimezone($timezone)
                    ->format('Y-m-d')
                : null;

            $isClient = $this->isClientMessage($message);
            $showDateDivider = $currentDate !== $nextDate;
            $showIncomingAvatar = ! $isClient
                && (
                    ! $previousMessage
                    || $this->messageAuthorKey($previousMessage) !== $this->messageAuthorKey($message)
                    || $previousDate !== $currentDate
                );
        @endphp

        @include('chat-field::livewire.partials.message-bubble', [
            'message' => $message,
            'isClient' => $isClient,
            'showDateDivider' => $showDateDivider,
            'showIncomingAvatar' => $showIncomingAvatar,
        ])
    @endforeach

    @if ($this->paginator()->hasMorePages())
        <div x-intersect="$wire.loadMoreMessages" class="h-4">
            <div class="mb-6 w-full text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('chat-field::chat-field.messages.loading_more') }}
            </div>
        </div>
    @endif
</div>

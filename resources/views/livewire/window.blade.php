<div class="flex h-full min-h-[32rem] w-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
    @include('chat-field::livewire.partials.header')

    @include('chat-field::livewire.partials.messages', [
        'threadMessages' => $threadMessages,
    ])

    @include('chat-field::livewire.partials.message-input')

    <x-filament-actions::modals />
    @script
    <script>
        $wire.on('chat-field-scroll-to-bottom', () => {
            const container = document.getElementById('chat-field-window-container');

            if (! container) {
                return;
            }

            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth',
            });

            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 300);
        });
    </script>
    @endscript
</div>

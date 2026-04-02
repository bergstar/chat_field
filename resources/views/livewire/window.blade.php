@php($styleHref = \Filament\Support\Facades\FilamentAsset::getStyleHref('chat-field-styles', package: 'bergstar/chat-field'))
@php($realtimeChannels = $this->realtimeChannels())
@php($realtimeEventName = $this->realtimeEventName())

<div
    x-load-css="[@js($styleHref)]"
    x-data="chatFieldRealtime({
        channels: @js($realtimeChannels),
        eventName: @js($realtimeEventName),
    })"
    class="flex h-[min(72vh,42rem)] min-h-[28rem] w-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 md:min-h-[32rem]"
>
    @include('chat-field::livewire.partials.header')

    @include('chat-field::livewire.partials.messages', [
        'threadMessages' => $threadMessages,
    ])

    @include('chat-field::livewire.partials.message-input')

    <x-filament-actions::modals />
    @script
    <script>
        if (! window.chatFieldRealtime) {
            window.chatFieldRealtime = function (config) {
                return {
                    channels: Array.isArray(config.channels) ? config.channels : [],
                    eventName: config.eventName,
                    subscriptions: [],
                    pollIntervalId: null,
                    pollIntervalMs: 5000,

                    init() {
                        this.startPollingFallback();

                        if (! window.Echo || ! this.eventName || this.channels.length === 0) {
                            return;
                        }

                        const eventName = `.${this.eventName}`;

                        this.channels.forEach((channelName) => {
                            const subscription = window.Echo.private(channelName);
                            const handler = (payload) => this.$wire.handleBroadcast(payload);

                            subscription.listen(eventName, handler);

                            this.subscriptions.push({
                                channelName,
                                eventName,
                                handler,
                                subscription,
                            });
                        });
                    },

                    startPollingFallback() {
                        if (this.pollIntervalId) {
                            return;
                        }

                        this.pollIntervalId = window.setInterval(() => {
                            if (this.isRealtimeConnected()) {
                                return;
                            }

                            this.$wire.handleBroadcast({
                                action: 'poll.refresh',
                            });
                        }, this.pollIntervalMs);
                    },

                    isRealtimeConnected() {
                        const state = window.Echo?.connector?.pusher?.connection?.state;

                        return state === 'connected';
                    },

                    destroy() {
                        if (this.pollIntervalId) {
                            window.clearInterval(this.pollIntervalId);
                            this.pollIntervalId = null;
                        }

                        if (! window.Echo) {
                            this.subscriptions = [];

                            return;
                        }

                        this.subscriptions.forEach(({ channelName, eventName, handler, subscription }) => {
                            subscription.stopListening(eventName, handler);
                            window.Echo.leaveChannel(`private-${channelName}`);
                        });

                        this.subscriptions = [];
                    },
                };
            };
        }

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

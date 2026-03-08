@php($ownerRecord = $getResolvedOwnerRecord())

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @if ($ownerRecord instanceof \Illuminate\Database\Eloquent\Model)
        @livewire(
            'toolborg-chat-field-window',
            ['ownerRecord' => $ownerRecord],
            key($getChatComponentKey())
        )
    @else
        <div class="overflow-hidden rounded-2xl border border-dashed border-gray-300 bg-gray-50 dark:border-white/10 dark:bg-gray-900/60">
            <div class="flex min-h-[20rem] flex-col items-center justify-center gap-3 px-6 py-10 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-m-chat-bubble-left-right" class="h-6 w-6" />
                </div>

                <div class="space-y-1">
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('Save this record before using chat.') }}
                    </p>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('The chat thread is attached to the current persisted record or the deepest saved singular relationship in this form.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-dynamic-component>

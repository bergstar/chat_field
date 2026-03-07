@php($downloadName = $this->originalAttachmentName($attachment, $message->original_attachment_file_names))

<button
    type="button"
    x-on:click.prevent="$wire.downloadAttachment({{ \Illuminate\Support\Js::from($attachment) }}, {{ \Illuminate\Support\Js::from($downloadName) }})"
    class="{{ $buttonClasses }}"
>
    <span class="{{ $iconWrapperClasses }}">
        <x-filament::icon :icon="$this->attachmentIcon($attachment)" class="h-4 w-4" />
    </span>
    <span class="{{ $labelClasses }}">{{ $downloadName }}</span>
</button>

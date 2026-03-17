@php($downloadName = $this->originalAttachmentName($attachment, $message->original_attachment_file_names))
@php($previewUrl = $this->attachmentPreviewUrl($message, $attachment))
@php($downloadUrl = $this->attachmentDownloadUrl($message, $attachment))

@if ($previewUrl || $downloadUrl)
    <a
        href="{{ $previewUrl ?: $downloadUrl }}"
        @if ($previewUrl)
            target="_blank"
            rel="noopener noreferrer"
        @endif
        class="{{ $buttonClasses }}"
    >
        <span class="{{ $iconWrapperClasses }}">
            <x-filament::icon :icon="$this->attachmentIcon($attachment)" class="h-4 w-4" />
        </span>
        <span class="{{ $labelClasses }}">{{ $downloadName }}</span>
    </a>
@else
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
@endif

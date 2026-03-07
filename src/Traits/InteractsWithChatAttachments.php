<?php

namespace Toolborg\ChatField\Traits;

trait InteractsWithChatAttachments
{
    protected array $imageExtensions = ['gif', 'jpeg', 'jpg', 'png', 'webp'];

    protected array $documentExtensions = ['csv', 'doc', 'docx', 'pdf', 'ppt', 'pptx', 'txt', 'xls', 'xlsx'];

    protected array $videoExtensions = ['avi', 'flv', 'mkv', 'mov', 'mp4', 'mpeg', 'mpg', 'webm'];

    protected array $audioExtensions = ['aac', 'flac', 'm4a', 'midi', 'mp3', 'ogg', 'wav'];

    public function attachmentIcon(string $path): string
    {
        $extension = $this->attachmentExtension($path);

        if (in_array($extension, $this->imageExtensions, true)) {
            return 'heroicon-m-photo';
        }

        if (in_array($extension, $this->videoExtensions, true)) {
            return 'heroicon-m-video-camera';
        }

        if (in_array($extension, $this->audioExtensions, true)) {
            return 'heroicon-m-speaker-wave';
        }

        if (in_array($extension, $this->documentExtensions, true)) {
            return 'heroicon-m-paper-clip';
        }

        return 'heroicon-m-document';
    }

    public function originalAttachmentName(string $path, ?array $originalNames = null): string
    {
        return $originalNames[$path] ?? basename($path);
    }

    protected function attachmentExtension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }
}

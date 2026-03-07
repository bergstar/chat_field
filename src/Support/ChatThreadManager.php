<?php

namespace Toolborg\ChatField\Support;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Toolborg\ChatField\Models\ChatMessage;
use Toolborg\ChatField\Models\ChatThread;

class ChatThreadManager
{
    public function findThreadForOwner(Model $owner): ?ChatThread
    {
        return $this->threadModel()::query()
            ->forOwner($owner)
            ->first();
    }

    public function findOrCreateThreadForOwner(Model $owner): ChatThread
    {
        return $this->threadModel()::query()->firstOrCreate([
            'ownerable_type' => $owner->getMorphClass(),
            'ownerable_id' => (string) $owner->getKey(),
        ]);
    }

    public function sendMessageForOwner(
        Model $owner,
        Model $author,
        ?string $body = null,
        array $attachments = [],
        array $originalAttachmentFileNames = [],
    ): ChatMessage {
        $thread = $this->findOrCreateThreadForOwner($owner);

        return $this->sendMessage(
            $thread,
            $author,
            $body,
            $attachments,
            $originalAttachmentFileNames,
        );
    }

    public function sendMessage(
        ChatThread $thread,
        Model $author,
        ?string $body = null,
        array $attachments = [],
        array $originalAttachmentFileNames = [],
    ): ChatMessage {
        $body = trim((string) $body);

        if ($body === '' && $attachments === []) {
            throw new InvalidArgumentException('A message or at least one attachment is required.');
        }

        return $this->messageModel()::query()->create([
            'chat_thread_id' => $thread->getKey(),
            'body' => $body !== '' ? $body : null,
            'attachments' => $attachments !== [] ? $attachments : null,
            'original_attachment_file_names' => $originalAttachmentFileNames !== [] ? $originalAttachmentFileNames : null,
            'authorable_id' => (string) $author->getKey(),
            'authorable_type' => $author->getMorphClass(),
        ])->loadMissing(['authorable', 'thread']);
    }

    protected function threadModel(): string
    {
        return config('chat-field.models.thread', ChatThread::class);
    }

    protected function messageModel(): string
    {
        return config('chat-field.models.message', ChatMessage::class);
    }
}

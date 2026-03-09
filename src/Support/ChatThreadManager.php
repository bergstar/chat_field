<?php

namespace Toolborg\ChatField\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use Toolborg\ChatField\Models\ChatMessage;
use Toolborg\ChatField\Models\ChatThread;

class ChatThreadManager
{
    public function findThreadForOwner(Model $owner): ?ChatThread
    {
        $model = $this->threadModel();

        return $model::query()
            ->forOwner($owner)
            ->first();
    }

    public function findOrCreateThreadForOwner(Model $owner): ChatThread
    {
        $model = $this->threadModel();

        return $model::query()->createOrFirst([
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
        $model = $this->messageModel();
        $body = trim((string) $body);

        if ($body === '' && $attachments === []) {
            throw new InvalidArgumentException(__('chat-field::chat-field.messages.message_or_attachment_required'));
        }

        return $model::query()->create([
            'chat_thread_id' => $thread->getKey(),
            'body' => $body !== '' ? $body : null,
            'attachments' => $attachments !== [] ? $attachments : null,
            'original_attachment_file_names' => $originalAttachmentFileNames !== [] ? $originalAttachmentFileNames : null,
            'authorable_id' => (string) $author->getKey(),
            'authorable_type' => $author->getMorphClass(),
        ])->loadMissing(['authorable', 'thread']);
    }

    public function paginateMessages(ChatThread $thread, int $page, int $perPage): LengthAwarePaginator
    {
        return $thread->messages()
            ->with('authorable')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function messageAuthorKey(ChatMessage $message): string
    {
        return implode(':', [
            (string) $message->authorable_type,
            (string) $message->authorable_id,
        ]);
    }

    public function isMessageAuthoredBy(ChatMessage $message, Model $author): bool
    {
        return $message->authorable_type === $author->getMorphClass()
            && (string) $message->authorable_id === (string) $author->getKey();
    }

    /**
     * @return class-string<ChatThread>
     */
    protected function threadModel(): string
    {
        return config('chat-field.models.thread', ChatThread::class);
    }

    /**
     * @return class-string<ChatMessage>
     */
    protected function messageModel(): string
    {
        return config('chat-field.models.message', ChatMessage::class);
    }
}

<?php

namespace Toolborg\ChatField\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $chat_thread_id
 * @property string|null $body
 * @property array<int, string>|null $attachments
 * @property array<string, string>|null $original_attachment_file_names
 * @property string $authorable_id
 * @property string $authorable_type
 * @property-read \Illuminate\Database\Eloquent\Model|null $authorable
 * @property-read \Toolborg\ChatField\Models\ChatThread|null $thread
 */
class ChatMessage extends Model
{
    protected $table = 'chat_field_messages';

    protected $fillable = [
        'chat_thread_id',
        'body',
        'attachments',
        'original_attachment_file_names',
        'authorable_id',
        'authorable_type',
    ];

    protected $casts = [
        'attachments' => 'array',
        'original_attachment_file_names' => 'array',
    ];

    protected $touches = ['thread'];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(config('chat-field.models.thread', ChatThread::class), 'chat_thread_id');
    }

    public function authorable(): MorphTo
    {
        return $this->morphTo();
    }
}

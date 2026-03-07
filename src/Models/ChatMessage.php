<?php

namespace Toolborg\ChatField\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

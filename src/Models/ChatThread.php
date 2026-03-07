<?php

namespace Toolborg\ChatField\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatThread extends Model
{
    protected $table = 'chat_field_threads';

    protected $fillable = [
        'ownerable_id',
        'ownerable_type',
    ];

    public function ownerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(config('chat-field.models.message', ChatMessage::class), 'chat_thread_id');
    }

    public function scopeForOwner(Builder $query, Model $owner): Builder
    {
        return $query
            ->where('ownerable_type', $owner->getMorphClass())
            ->where('ownerable_id', (string) $owner->getKey());
    }
}

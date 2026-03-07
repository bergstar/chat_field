<?php

namespace Toolborg\ChatField;

use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;
use Toolborg\ChatField\Support\OwnerRecordResolver;

class ChatField extends Field
{
    protected string $view = 'chat-field::forms.components.chat-field';

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull();
        $this->dehydrated(false);
    }

    public function getResolvedOwnerRecord(): ?Model
    {
        return app(OwnerRecordResolver::class)->resolve($this);
    }

    public function getChatComponentKey(): string
    {
        $ownerRecord = $this->getResolvedOwnerRecord();

        if (! $ownerRecord instanceof Model) {
            return 'chat-field.' . sha1((string) $this->getStatePath() . '|empty');
        }

        return 'chat-field.' . sha1(implode('|', [
            (string) $this->getStatePath(),
            $ownerRecord->getMorphClass(),
            (string) $ownerRecord->getKey(),
        ]));
    }
}

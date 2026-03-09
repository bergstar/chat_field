<?php

namespace Toolborg\ChatField\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Toolborg\ChatField\Models\ChatMessage;
use Toolborg\ChatField\Models\ChatThread;
use Toolborg\ChatField\Support\ChatThreadManager;
use Toolborg\ChatField\Traits\InteractsWithChatAttachments;

/**
 * @property Schema $form
 * @property-read LengthAwarePaginator $paginator
 */
class ChatWindow extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithChatAttachments;
    use InteractsWithForms;

    public ?Model $ownerRecord = null;

    public ?ChatThread $thread = null;

    public Collection $threadMessages;

    public ?array $data = [];

    public bool $readOnly = false;

    public ?string $readOnlyNotice = null;

    public ?string $title = null;

    public ?string $meta = null;

    public bool $showUpload = false;

    public int $currentPage = 1;

    public ?string $sendError = null;

    public function mount(
        ?Model $ownerRecord = null,
        bool $readOnly = false,
        ?string $readOnlyNotice = null,
        ?string $title = null,
        ?string $meta = null,
    ): void
    {
        $this->form->fill();
        $this->threadMessages = collect();
        $this->ownerRecord = $ownerRecord;
        $this->readOnly = $readOnly;
        $this->readOnlyNotice = $readOnlyNotice;
        $this->title = $title;
        $this->meta = $meta;

        if (! $this->hasOwnerRecord()) {
            return;
        }

        $this->thread = $this->chatManager()->findThreadForOwner($this->ownerRecord);

        if ($this->thread instanceof ChatThread) {
            $this->loadMoreMessages();
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('attachments')
                    ->hiddenLabel()
                    ->multiple()
                    ->storeFileNamesIn('original_attachment_file_names')
                    ->fetchFileInformation()
                    ->disk($this->uploadDisk())
                    ->directory(fn (): string => $this->uploadDirectory())
                    ->visibility(fn (): string => $this->uploadVisibility())
                    ->acceptedFileTypes(config('chat-field.uploads.mime_types', []))
                    ->maxSize(config('chat-field.uploads.max_file_size', 12288))
                    ->minSize(config('chat-field.uploads.min_file_size', 1))
                    ->maxFiles(config('chat-field.uploads.max_files', 10))
                    ->minFiles(config('chat-field.uploads.min_files', 0))
                    ->panelLayout('grid')
                    ->extraAttributes([
                        'class' => 'chat-field-filepond',
                    ])
                    ->visible(fn (): bool => $this->showUpload),
                Forms\Components\Textarea::make('message')
                    ->hiddenLabel()
                    ->rows(1)
                    ->autosize()
                    ->placeholder(__('chat-field::chat-field.placeholders.write_message'))
                    ->required(function (Get $get): bool {
                        return count($get('attachments') ?? []) === 0;
                    }),
            ])
            ->columns(1)
            ->extraAttributes([
                'class' => 'p-1',
            ])
            ->statePath('data');
    }

    public function toggleUpload(): void
    {
        $this->showUpload = ! $this->showUpload;
    }

    public function sendMessage(): void
    {
        if ($this->readOnly || ! $this->hasOwnerRecord()) {
            return;
        }

        $state = $this->form->getState();

        try {
            $message = $this->chatManager()->sendMessageForOwner(
                $this->ownerRecord,
                $this->currentUserOrFail(),
                $state['message'] ?? null,
                $state['attachments'] ?? [],
                $state['original_attachment_file_names'] ?? [],
            );

            $thread = $message->thread;

            if (! $thread instanceof ChatThread) {
                throw new InvalidArgumentException(__('chat-field::chat-field.messages.thread_resolution_failed'));
            }

            $this->thread = $thread;
            $this->threadMessages->prepend($message);
            $this->showUpload = false;
            $this->sendError = null;
            $this->form->fill();

            $this->dispatch('chat-field-scroll-to-bottom');
        } catch (InvalidArgumentException $exception) {
            $this->sendError = $exception->getMessage();
        }
    }

    public function loadMoreMessages(): void
    {
        if (! $this->thread instanceof ChatThread) {
            return;
        }

        $this->threadMessages->push(...$this->paginator()->getCollection());
        $this->currentPage++;
    }

    #[Computed]
    public function paginator(): LengthAwarePaginator
    {
        if (! $this->thread instanceof ChatThread) {
            return new LengthAwarePaginator([], 0, config('chat-field.messages_per_page', 10), $this->currentPage);
        }

        return $this->chatManager()->paginateMessages(
            $this->thread,
            $this->currentPage,
            config('chat-field.messages_per_page', 10),
        );
    }

    public function downloadAttachment(string $path, string $originalFileName)
    {
        if (Storage::disk($this->uploadDisk())->exists($path)) {
            return Storage::disk($this->uploadDisk())->download($path, $originalFileName);
        }

        abort(404, __('chat-field::chat-field.messages.file_not_found'));
    }

    public function displayName(?Model $author): string
    {
        $column = config('chat-field.author_name_column', 'name');

        if (! is_string($column) || $column === '') {
            $column = 'name';
        }

        if (! $author instanceof Model) {
            return __('chat-field::chat-field.labels.unknown');
        }

        $value = $author->getAttribute($column);

        return filled($value) ? (string) $value : __('chat-field::chat-field.labels.unknown');
    }

    public function initials(?Model $author): string
    {
        $name = $this->displayName($author);

        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => $this->firstLetter($segment))
            ->implode('');
    }

    public function ownerLabel(): string
    {
        if (! $this->ownerRecord instanceof Model) {
            return __('chat-field::chat-field.messages.save_record_first');
        }

        foreach (['title', 'name', 'reference', 'subject_reference_snapshot'] as $column) {
            $value = $this->ownerRecord->getAttribute($column);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return class_basename($this->ownerRecord) . ' #' . $this->ownerRecord->getKey();
    }

    public function ownerMetaLabel(): string
    {
        if (! $this->ownerRecord instanceof Model) {
            return __('chat-field::chat-field.labels.record_chat');
        }

        return class_basename($this->ownerRecord);
    }

    public function chatTitle(): string
    {
        return filled($this->title) ? (string) $this->title : __('chat-field::chat-field.labels.chat');
    }

    public function chatMeta(): string
    {
        if (filled($this->meta)) {
            return (string) $this->meta;
        }

        return $this->ownerMetaLabel() . ': ' . $this->ownerLabel();
    }

    public function headerInitials(): string
    {
        return $this->ownerInitials();
    }

    public function ownerInitials(): string
    {
        return $this->initials($this->ownerRecord);
    }

    public function isClientMessage(ChatMessage $message): bool
    {
        $authorType = $message->getAttribute('author_type');

        if (filled($authorType)) {
            return $authorType === 'account';
        }

        $authorableType = $message->getAttribute('authorable_type');

        return is_string($authorableType) && str_ends_with($authorableType, '\\Account');
    }

    public function messageAuthorKey(ChatMessage $message): string
    {
        return $this->chatManager()->messageAuthorKey($message);
    }

    public function messageDisplayName(ChatMessage $message): string
    {
        $snapshotName = $message->getAttribute('author_name_snapshot');

        if (filled($snapshotName)) {
            return (string) $snapshotName;
        }

        $author = $message->getRelationValue('authorable');

        if ($author instanceof Model) {
            return $this->displayName($author);
        }

        return __('chat-field::chat-field.labels.unknown');
    }

    public function messageAuthorInitials(ChatMessage $message): string
    {
        return $this->initialsFromName($this->messageDisplayName($message));
    }

    public function messageMetaLabel(ChatMessage $message): string
    {
        return $this->abbreviateName($this->messageDisplayName($message))
            . ' · '
            . $this->formatMessageTimestamp($message->created_at);
    }

    public function formatDividerDate($value): string
    {
        return Carbon::parse($value)
            ->setTimezone($this->configuredTimezone())
            ->format('d.m.Y');
    }

    public function formatMessageTimestamp($value): string
    {
        $date = Carbon::parse($value)->setTimezone($this->configuredTimezone());

        return $date->isToday()
            ? $date->format('H:i')
            : $date->format('d.m.Y H:i');
    }

    protected function hasOwnerRecord(): bool
    {
        return $this->ownerRecord instanceof Model && $this->ownerRecord->exists;
    }

    protected function currentUserOrFail(): Model
    {
        $user = Filament::auth()->user() ?? auth()->user();

        if (! $user instanceof Model) {
            abort(403);
        }

        return $user;
    }

    protected function chatManager(): ChatThreadManager
    {
        return app(config('chat-field.manager', ChatThreadManager::class));
    }

    protected function uploadDisk(): string
    {
        return config('chat-field.uploads.disk', 'public');
    }

    protected function uploadDirectory(): string
    {
        if ($this->uploadDisk() === 's3') {
            return config('chat-field.uploads.s3.directory', 'chat-field-attachments');
        }

        return config('chat-field.uploads.directory', 'chat-field-attachments');
    }

    protected function uploadVisibility(): string
    {
        if ($this->uploadDisk() === 's3') {
            return config('chat-field.uploads.s3.visibility', 'private');
        }

        return config('chat-field.uploads.visibility', 'public');
    }

    protected function configuredTimezone(): string
    {
        $timezone = config('chat-field.timezone');

        if (is_string($timezone) && $timezone !== '') {
            return $timezone;
        }

        $appTimezone = config('app.timezone');

        if (is_string($appTimezone) && $appTimezone !== '') {
            return $appTimezone;
        }

        return 'UTC';
    }

    protected function initialsFromName(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => $this->firstLetter($segment))
            ->implode('');
    }

    protected function abbreviateName(string $name): string
    {
        $segments = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->values();

        $firstName = $segments->get(0);

        if (! is_string($firstName) || $firstName === '') {
            return __('chat-field::chat-field.labels.unknown');
        }

        $secondName = $segments->get(1);

        if (! is_string($secondName) || $secondName === '') {
            return $firstName;
        }

        return $firstName . ' ' . $this->firstLetter($secondName) . '.';
    }

    protected function firstLetter(string $segment): string
    {
        $letter = mb_substr(trim($segment), 0, 1);

        return $letter === '' ? '' : mb_strtoupper($letter);
    }

    public function render()
    {
        return view('chat-field::livewire.window');
    }
}

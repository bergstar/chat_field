<?php

namespace Toolborg\ChatField\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Flex;
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

    public bool $showUpload = false;

    public int $currentPage = 1;

    public ?string $sendError = null;

    public function mount(?Model $ownerRecord = null): void
    {
        $this->form->fill();
        $this->threadMessages = collect();
        $this->ownerRecord = $ownerRecord;

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
                Flex::make([
                    Actions::make([
                        Action::make('toggle_upload')
                            ->hiddenLabel()
                            ->icon('heroicon-m-plus')
                            ->color('gray')
                            ->tooltip(__('Upload files'))
                            ->action(function (): void {
                                $this->showUpload = ! $this->showUpload;
                            }),
                    ])->grow(false),
                    Forms\Components\Textarea::make('message')
                        ->hiddenLabel()
                        ->rows(1)
                        ->autosize()
                        ->grow(true)
                        ->placeholder(__('Write a message...'))
                        ->required(function (Get $get): bool {
                            return count($get('attachments') ?? []) === 0;
                        }),
                ])->verticallyAlignEnd(),
            ])
            ->columns(1)
            ->extraAttributes([
                'class' => 'p-1',
            ])
            ->statePath('data');
    }

    public function sendMessage(): void
    {
        if (! $this->hasOwnerRecord()) {
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
                throw new InvalidArgumentException('The chat thread could not be resolved for the new message.');
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

        return $this->thread->messages()
            ->with('authorable')
            ->latest()
            ->paginate(
                config('chat-field.messages_per_page', 10),
                ['*'],
                'page',
                $this->currentPage,
            );
    }

    public function downloadAttachment(string $path, string $originalFileName)
    {
        if (Storage::disk($this->uploadDisk())->exists($path)) {
            return Storage::disk($this->uploadDisk())->download($path, $originalFileName);
        }

        abort(404, __('File not found.'));
    }

    public function displayName(?Model $author): string
    {
        $column = config('chat-field.author_name_column', 'name');

        if (! is_string($column) || $column === '') {
            $column = 'name';
        }

        if (! $author instanceof Model) {
            return __('Unknown');
        }

        $value = $author->getAttribute($column);

        return filled($value) ? (string) $value : __('Unknown');
    }

    public function initials(?Model $author): string
    {
        $name = $this->displayName($author);

        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => strtoupper(substr($segment, 0, 1)))
            ->implode('');
    }

    public function ownerLabel(): string
    {
        if (! $this->ownerRecord instanceof Model) {
            return __('Save this record before using chat.');
        }

        foreach (['title', 'name'] as $column) {
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
            return __('Record chat');
        }

        return class_basename($this->ownerRecord);
    }

    public function ownerInitials(): string
    {
        return $this->initials($this->ownerRecord);
    }

    public function isMine(ChatMessage $message): bool
    {
        $currentUser = $this->currentUserOrFail();

        return $message->authorable_type === $currentUser->getMorphClass()
            && (string) $message->authorable_id === (string) $currentUser->getKey();
    }

    public function formatDividerDate($value): string
    {
        return Carbon::parse($value)
            ->setTimezone($this->configuredTimezone())
            ->format('F j, Y');
    }

    public function formatMessageTimestamp($value): string
    {
        $date = Carbon::parse($value)->setTimezone($this->configuredTimezone());

        return $date->isToday()
            ? $date->format('g:i A')
            : $date->format('M d, Y g:i A');
    }

    protected function hasOwnerRecord(): bool
    {
        return $this->ownerRecord instanceof Model && $this->ownerRecord->exists;
    }

    protected function currentUserOrFail(): Model
    {
        $user = auth()->user();

        if (! $user instanceof Model) {
            abort(403);
        }

        return $user;
    }

    protected function chatManager(): ChatThreadManager
    {
        return app(ChatThreadManager::class);
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

    public function render()
    {
        return view('chat-field::livewire.window');
    }
}

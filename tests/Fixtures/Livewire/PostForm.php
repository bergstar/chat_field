<?php

namespace Toolborg\ChatField\Tests\Fixtures\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Toolborg\ChatField\ChatField;
use Toolborg\ChatField\Tests\Fixtures\Models\Post;

class PostForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public array $submittedData = [];

    public Post $record;

    public function mount(Post $record): void
    {
        $this->record = $record;
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->model($this->record)
            ->schema([
                ChatField::make('chat'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $this->submittedData = $this->form->getState();
    }

    public function render(): View
    {
        return view('form-host');
    }
}

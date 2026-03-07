<?php

use Livewire\Livewire;
use Toolborg\ChatField\Livewire\ChatWindow;
use Toolborg\ChatField\Models\ChatMessage;
use Toolborg\ChatField\Models\ChatThread;
use Toolborg\ChatField\Tests\Fixtures\Models\Post;
use Toolborg\ChatField\Tests\Fixtures\Models\User;

it('creates a thread and stores a message when sending from the chat window', function () {
    $user = User::query()->create([
        'name' => 'Window User',
        'email' => 'window@example.com',
    ]);

    $post = Post::query()->create([
        'title' => 'Window Post',
    ]);

    $this->actingAs($user);

    Livewire::test(ChatWindow::class, ['ownerRecord' => $post])
        ->set('data.message', 'Hello from the chat window')
        ->call('sendMessage')
        ->assertSet('sendError', null)
        ->assertSee('Hello from the chat window');

    expect(ChatThread::query()->forOwner($post)->exists())->toBeTrue()
        ->and(ChatMessage::query()->count())->toBe(1);
});

it('shows an error when neither text nor attachments are provided', function () {
    $user = User::query()->create([
        'name' => 'Window User',
        'email' => 'window-empty@example.com',
    ]);

    $post = Post::query()->create([
        'title' => 'Window Post',
    ]);

    $this->actingAs($user);

    Livewire::test(ChatWindow::class, ['ownerRecord' => $post])
        ->call('sendMessage')
        ->assertHasErrors(['data.message']);
});

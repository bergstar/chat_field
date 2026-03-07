<?php

use Livewire\Livewire;
use Toolborg\ChatField\Livewire\ChatWindow;
use Toolborg\ChatField\Tests\Fixtures\Livewire\NestedPostMetaForm;
use Toolborg\ChatField\Tests\Fixtures\Livewire\PostForm;
use Toolborg\ChatField\Tests\Fixtures\Models\Post;
use Toolborg\ChatField\Tests\Fixtures\Models\PostMeta;
use Toolborg\ChatField\Tests\Fixtures\Models\User;

it('renders the root record thread on a standard form', function () {
    $user = User::query()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
    ]);

    $post = Post::query()->create([
        'title' => 'Root Post',
    ]);

    $this->actingAs($user);

    Livewire::test(ChatWindow::class, ['ownerRecord' => $post])
        ->set('data.message', 'Root message')
        ->call('sendMessage');

    Livewire::test(PostForm::class, ['record' => $post])
        ->assertSee('Root message')
        ->assertSee('Chat');
});

it('uses the deepest existing singular relationship record for the thread', function () {
    $user = User::query()->create([
        'name' => 'Admin User',
        'email' => 'nested@example.com',
    ]);

    $post = Post::query()->create([
        'title' => 'Parent Post',
    ]);

    $meta = PostMeta::query()->create([
        'post_id' => $post->getKey(),
        'name' => 'Nested Meta',
    ]);

    $this->actingAs($user);

    Livewire::test(ChatWindow::class, ['ownerRecord' => $post])
        ->set('data.message', 'Parent thread message')
        ->call('sendMessage');

    Livewire::test(ChatWindow::class, ['ownerRecord' => $meta])
        ->set('data.message', 'Nested thread message')
        ->call('sendMessage');

    Livewire::test(NestedPostMetaForm::class, ['record' => $post])
        ->assertSee('Nested thread message')
        ->assertDontSee('Parent thread message');
});

it('shows the save-first state when the deepest singular relationship record is missing', function () {
    $post = Post::query()->create([
        'title' => 'Missing Meta',
    ]);

    Livewire::test(NestedPostMetaForm::class, ['record' => $post])
        ->assertSee('Save this record before using chat.');
});

it('does not dehydrate the chat field into form state', function () {
    $post = Post::query()->create([
        'title' => 'State Test',
    ]);

    Livewire::test(PostForm::class, ['record' => $post])
        ->call('submit')
        ->assertSet('submittedData', []);
});

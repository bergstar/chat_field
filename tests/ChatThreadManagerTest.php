<?php

use Toolborg\ChatField\Models\ChatMessage;
use Toolborg\ChatField\Models\ChatThread;
use Toolborg\ChatField\Support\ChatThreadManager;
use Toolborg\ChatField\Tests\Fixtures\Models\Post;
use Toolborg\ChatField\Tests\Fixtures\Models\User;

it('creates isolated threads per owner record', function () {
    $manager = app(ChatThreadManager::class);

    $author = User::query()->create([
        'name' => 'Author',
        'email' => 'author@example.com',
    ]);

    $firstPost = Post::query()->create(['title' => 'First']);
    $secondPost = Post::query()->create(['title' => 'Second']);

    $manager->sendMessageForOwner($firstPost, $author, 'First post message');
    $manager->sendMessageForOwner($secondPost, $author, 'Second post message');

    expect(ChatThread::query()->count())->toBe(2)
        ->and(ChatMessage::query()->count())->toBe(2)
        ->and($manager->findThreadForOwner($firstPost)?->messages()->count())->toBe(1)
        ->and($manager->findThreadForOwner($secondPost)?->messages()->count())->toBe(1);
});

it('allows attachment only messages', function () {
    $manager = app(ChatThreadManager::class);

    $author = User::query()->create([
        'name' => 'Author',
        'email' => 'attachments@example.com',
    ]);

    $post = Post::query()->create(['title' => 'Attachment Post']);

    $message = $manager->sendMessageForOwner(
        $post,
        $author,
        null,
        ['chat-field-attachments/file.pdf'],
        ['chat-field-attachments/file.pdf' => 'file.pdf'],
    );

    expect($message->body)->toBeNull()
        ->and($message->attachments)->toBe(['chat-field-attachments/file.pdf'])
        ->and($message->original_attachment_file_names)->toBe(['chat-field-attachments/file.pdf' => 'file.pdf']);
});

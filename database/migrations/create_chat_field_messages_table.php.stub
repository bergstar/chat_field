<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_field_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_thread_id')
                ->constrained('chat_field_threads')
                ->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->json('attachments')->nullable();
            $table->json('original_attachment_file_names')->nullable();
            $table->string('authorable_id');
            $table->string('authorable_type');
            $table->timestamps();

            $table->index(['authorable_type', 'authorable_id'], 'chat_field_messages_author_index');
            $table->index(['chat_thread_id', 'created_at'], 'chat_field_messages_thread_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_field_messages');
    }
};

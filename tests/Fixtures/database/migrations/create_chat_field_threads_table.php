<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_field_threads', function (Blueprint $table) {
            $table->id();
            $table->string('ownerable_id');
            $table->string('ownerable_type');
            $table->timestamps();

            $table->unique(['ownerable_type', 'ownerable_id'], 'chat_field_threads_owner_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_field_threads');
    }
};

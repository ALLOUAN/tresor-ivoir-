<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('subject', 255)->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview', 180)->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'status']);
            $table->index('last_message_at');
        });

        Schema::create('provider_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('provider_conversations')
                ->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'read_at']);
            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_conversation_messages');
        Schema::dropIfExists('provider_conversations');
    }
};


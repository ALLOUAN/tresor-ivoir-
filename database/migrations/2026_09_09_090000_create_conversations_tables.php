<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('context_type', 40)->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->string('subject', 255)->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('client_blocked_at')->nullable();
            $table->timestamp('provider_blocked_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview', 180)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index('last_message_at');
        });

        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_flagged')->default(false);
            $table->string('flagged_reason', 255)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'read_at']);
            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
    }
};

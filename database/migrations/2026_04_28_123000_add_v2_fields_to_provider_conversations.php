<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_conversations', function (Blueprint $table) {
            $table->string('priority', 20)->default('normal')->after('status');
            $table->foreignId('assigned_admin_id')
                ->nullable()
                ->after('provider_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::create('provider_conversation_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')
                ->constrained('provider_conversation_messages')
                ->cascadeOnDelete();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();

            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_conversation_attachments');

        Schema::table('provider_conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_admin_id');
            $table->dropColumn('priority');
        });
    }
};


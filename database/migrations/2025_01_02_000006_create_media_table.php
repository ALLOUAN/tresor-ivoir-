<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('mediable_type', 100);
            $table->unsignedBigInteger('mediable_id');
            $table->string('collection', 80);
            $table->enum('type', ['image', 'video', 'document']);
            $table->string('mime_type', 100);
            $table->string('original_name', 255);
            $table->string('file_path', 500);
            $table->string('url', 500);
            $table->string('thumb_url', 500)->nullable();
            $table->unsignedInteger('size_bytes');
            $table->smallInteger('width')->nullable();
            $table->smallInteger('height')->nullable();
            $table->smallInteger('duration_sec')->nullable();
            $table->string('alt_text', 300)->nullable();
            $table->string('caption', 500)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mediable_type', 'mediable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

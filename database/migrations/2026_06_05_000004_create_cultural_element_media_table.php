<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cultural_element_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('element_id')->constrained('cultural_elements')->cascadeOnDelete();
            $table->enum('type', ['photo', 'video', 'audio']);
            $table->string('url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('caption', 200)->nullable();
            $table->string('alt_text', 200)->nullable();
            $table->unsignedSmallInteger('duree_secondes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultural_element_media');
    }
};

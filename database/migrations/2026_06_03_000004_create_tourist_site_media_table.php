<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tourist_site_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('tourist_sites')->cascadeOnDelete();
            $table->enum('type', ['photo', 'video']);
            $table->string('url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('caption', 200)->nullable();
            $table->string('alt_text', 200)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tourist_site_media');
    }
};

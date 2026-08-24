<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prestation_settings', function (Blueprint $table) {
            $table->id();

            // Bannière
            $table->string('banner_image_url', 500)->nullable();
            $table->string('banner_title')->nullable();
            $table->longText('banner_content')->nullable();

            // Vidéo de présentation (upload OU lien externe, au choix de l'admin)
            $table->enum('video_source', ['upload', 'embed'])->default('upload');
            $table->string('video_url', 500)->nullable();
            $table->string('video_embed_url', 500)->nullable();
            $table->string('video_poster_url', 500)->nullable();

            // Catalogue interactif (DearFlip)
            $table->string('catalog_title')->nullable();
            $table->string('catalog_file_url', 500)->nullable();
            $table->boolean('catalog_enabled')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestation_settings');
    }
};

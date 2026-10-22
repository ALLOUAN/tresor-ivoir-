<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Médiathèque — vidéos.
 * Deux sources : envoi direct (MP4/WebM court, format vertical) ou lien YouTube / Vimeo.
 * Les vidéos des prestataires sont « pending » jusqu'à validation par l'administration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title', 160);
            $table->string('slug', 190)->unique();
            $table->text('description')->nullable();
            $table->string('category', 30)->default('destinations')->index();
            $table->string('source', 10)->default('upload'); // upload | youtube | vimeo
            $table->string('file_path', 500)->nullable();     // fichier envoyé (disque public)
            $table->string('video_url', 500)->nullable();     // URL publique du fichier
            $table->string('external_id', 64)->nullable();    // identifiant YouTube / Vimeo
            $table->string('poster_url', 500)->nullable();
            $table->unsignedInteger('duration_sec')->nullable();
            $table->string('orientation', 10)->default('portrait'); // portrait | landscape
            $table->foreignId('city_id')->nullable()->constrained('tourist_cities')->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('links')->nullable();                // clés de fiches « type:id » (Voir et réserver)
            $table->string('status', 12)->default('pending')->index(); // pending | published | rejected
            $table->string('rejection_reason', 500)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            $table->unsignedInteger('booking_clicks_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('video_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('videos')->cascadeOnDelete();
            $table->string('liker_key', 80);
            $table->timestamp('created_at')->nullable();
            $table->unique(['video_id', 'liker_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_likes');
        Schema::dropIfExists('videos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_media_items', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('is_featured');
        });

        Schema::create('gallery_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('site_media_items')->cascadeOnDelete();
            // "user:{id}" pour un visiteur connecté, "guest:{uuid}" pour un visiteur
            // anonyme (identifié via un cookie longue durée) — la galerie reste
            // consultable sans compte, le like doit donc fonctionner sans compte aussi.
            $table->string('liker_key', 120);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['media_id', 'liker_key']);
            $table->index('liker_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_likes');

        Schema::table('site_media_items', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });
    }
};

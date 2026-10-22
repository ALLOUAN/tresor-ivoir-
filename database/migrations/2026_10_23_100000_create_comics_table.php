<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * « Les Astuces de Mian » — bandes dessinées (planches en images, pas de vidéo).
 * Les planches sont une liste ordonnée de chemins sur le disque public.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('slug', 190)->unique();
            $table->text('summary')->nullable();
            $table->string('category', 20)->default('educatif')->index(); // educatif | ludique | divertissant
            $table->string('age_label', 40)->nullable();                  // « Dès 7 ans », « Tout public »…
            $table->string('cover_path', 500)->nullable();
            $table->json('pages')->nullable();                            // ["comics/12/p1.jpg", …]
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comics');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Image de fond de la section « Art & Créations » (accueil) (même principe que plans_section_images). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_section_images', function (Blueprint $table) {
            $table->id();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_section_images');
    }
};

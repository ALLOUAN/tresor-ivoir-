<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bannières de l'accueil : plusieurs visuels qui défilent (liste ordonnée d'URL /storage/...). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_promo_banners', function (Blueprint $table) {
            $table->json('images')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('home_promo_banners', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};

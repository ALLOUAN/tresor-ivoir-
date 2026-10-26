<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bannières supplémentaires d'une ville (diaporama du haut de la page ville), en plus de cover_image. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tourist_cities', function (Blueprint $table) {
            $table->json('banners')->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('tourist_cities', function (Blueprint $table) {
            $table->dropColumn('banners');
        });
    }
};

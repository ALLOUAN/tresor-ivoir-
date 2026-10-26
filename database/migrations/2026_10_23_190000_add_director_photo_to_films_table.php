<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** « Cinéma d'Ivoire » : photo du réalisateur ou de la réalisatrice (portrait affiché sur la fiche film). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('films', function (Blueprint $table) {
            $table->string('director_photo_path')->nullable()->after('director_bio');
        });
    }

    public function down(): void
    {
        Schema::table('films', function (Blueprint $table) {
            $table->dropColumn('director_photo_path');
        });
    }
};

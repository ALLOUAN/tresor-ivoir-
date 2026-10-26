<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audience du site : une ligne par visiteur et par jour (empreinte anonyme, sans cookie ni IP stockée).
 * Sert aux graphiques du tableau de bord admin (en ligne, aujourd'hui, 7 jours, mois, année).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visitors', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->char('visitor_hash', 64);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('hits')->default(1);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at')->index();

            $table->unique(['date', 'visitor_hash']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visitors');
    }
};

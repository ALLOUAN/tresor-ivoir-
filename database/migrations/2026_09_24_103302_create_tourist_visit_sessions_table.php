<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vraie table (pas un JSON, contrairement à accommodations.room_types) : le
        // décompte de places sous concurrence a besoin de lockForUpdate() sur une ligne
        // réelle, ce qu'un élément de tableau JSON ne permet pas.
        Schema::create('tourist_visit_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tourist_experience_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->string('period_label')->nullable(); // ex: "10h00" ou "Matinée" — libre, pas de calendrier de dispo à calculer
            $table->unsignedSmallInteger('capacity');
            $table->unsignedInteger('price_per_person_xof')->nullable(); // null = session gratuite
            $table->text('conditions')->nullable();
            $table->boolean('is_active')->default(true); // fermeture manuelle par le prestataire, indépendante du taux de remplissage
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tourist_experience_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tourist_visit_sessions');
    }
};

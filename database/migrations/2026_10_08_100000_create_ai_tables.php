<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Signaux de comportement (consultations) pour le filtrage collaboratif.
        // Les favoris et les réservations sont lus directement dans leurs tables d'origine.
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('session_key', 40)->nullable()->index();   // acteur anonyme (empreinte de session)
            $table->string('item_type', 24);
            $table->unsignedBigInteger('item_id');
            $table->string('action', 16)->default('view');
            $table->boolean('is_simulated')->default(false)->index(); // données de démonstration, signalées comme telles
            $table->timestamp('created_at')->useCurrent();

            $table->index(['item_type', 'item_id']);
        });

        // Profil déclaré (questionnaire de 3 questions) des visiteurs connectés.
        Schema::create('ai_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->json('interests')->nullable();
            $table->string('budget', 12)->nullable();       // eco | moyen | confort
            $table->string('travel_type', 16)->nullable();  // solo | couple | famille | amis | affaires
            $table->unsignedBigInteger('home_city_id')->nullable();
            $table->timestamps();
        });

        // Séjours générés par le planificateur.
        Schema::create('trip_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->json('params');
            $table->json('plan');
            $table->text('narrative')->nullable();
            $table->string('engine', 24)->default('template');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_plans');
        Schema::dropIfExists('ai_profiles');
        Schema::dropIfExists('ai_interactions');
    }
};

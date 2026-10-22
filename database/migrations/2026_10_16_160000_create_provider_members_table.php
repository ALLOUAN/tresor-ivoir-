<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comptes équipe : employés (réception, salle, accueil d'un site) rattachés à un prestataire.
 * Ils gèrent les opérations du quotidien sans accès à l'argent, à l'abonnement ni à la fiche.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('job_title', 80)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_members');
    }
};

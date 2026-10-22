<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demandes de réservation hors hébergement (table au restaurant, activité de loisir, circuit
 * d'agence) : le client choisit une date et un nombre de personnes, l'établissement confirme
 * ou refuse. Pas de paiement en ligne à ce stade.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bookable_type', 20); // restaurant | leisure | tour
            $table->unsignedBigInteger('bookable_id');
            $table->string('bookable_name');
            $table->string('item_name')->nullable(); // ex. circuit choisi
            $table->date('booking_date');
            $table->string('booking_time', 5)->nullable();
            $table->unsignedSmallInteger('party_size');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new');
            $table->string('provider_note', 500)->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'status']);
            $table->index(['bookable_type', 'bookable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};

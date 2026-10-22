<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Départs programmés des circuits (date, places, prix éventuel) et identifiant précis de
 * l'élément réservé (circuit, activité, offre de transport) sur les demandes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->date('departure_date');
            $table->unsignedSmallInteger('seats_total');
            $table->unsignedInteger('price_xof')->nullable(); // prix par personne propre à ce départ
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tour_package_id', 'departure_date']);
        });

        Schema::table('booking_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->after('item_name');
            $table->boolean('is_custom_quote')->default(false)->after('item_id');
            $table->index(['bookable_type', 'item_id', 'booking_date']);
        });
    }

    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropIndex(['bookable_type', 'item_id', 'booking_date']);
            $table->dropColumn(['item_id', 'is_custom_quote']);
        });
        Schema::dropIfExists('tour_departures');
    }
};

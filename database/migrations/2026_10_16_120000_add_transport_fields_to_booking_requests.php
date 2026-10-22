<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Réservation de transport (transfert aéroport, trajet, location avec chauffeur) : lieux de prise en charge et de dépose. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->string('pickup_location', 255)->nullable()->after('item_name');
            $table->string('dropoff_location', 255)->nullable()->after('pickup_location');
        });
    }

    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropColumn(['pickup_location', 'dropoff_location']);
        });
    }
};

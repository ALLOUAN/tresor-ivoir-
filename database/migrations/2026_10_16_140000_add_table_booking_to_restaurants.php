<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réservation de tables : couverts disponibles par créneau, durée d'un créneau, confirmation
 * instantanée, et plat du jour. Sans capacité renseignée, le fonctionnement reste « sur demande ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->unsignedSmallInteger('table_capacity')->nullable();      // couverts par créneau
            $table->unsignedTinyInteger('slot_minutes')->default(30);
            $table->boolean('auto_confirm')->default(false);
            $table->unsignedSmallInteger('max_party_size')->nullable();
            $table->string('daily_special', 200)->nullable();
            $table->unsignedInteger('daily_special_price_xof')->nullable();
            $table->date('daily_special_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['table_capacity', 'slot_minutes', 'auto_confirm', 'max_party_size', 'daily_special', 'daily_special_price_xof', 'daily_special_date']);
        });
    }
};

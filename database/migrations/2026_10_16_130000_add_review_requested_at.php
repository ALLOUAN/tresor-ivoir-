<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Date d'envoi de la demande d'avis après la prestation (une seule demande par réservation). */
return new class extends Migration
{
    private const TABLES = ['reservations', 'booking_requests', 'tourist_visits'];

    public function up(): void
    {
        foreach (self::TABLES as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->timestamp('review_requested_at')->nullable());
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->dropColumn('review_requested_at'));
        }
    }
};

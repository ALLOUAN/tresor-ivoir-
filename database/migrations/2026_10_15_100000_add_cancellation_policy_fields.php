<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Politique d'annulation chiffrée : délai d'annulation gratuite par hébergement, et trace
 * de qui a annulé une réservation et du montant à rembourser au client.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Nombre de jours avant l'arrivée jusqu'auquel l'acompte est remboursé en totalité.
            // null = politique par défaut de la plateforme.
            $table->unsignedSmallInteger('free_cancellation_days')->nullable()->after('cancellation_policy');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('cancelled_by', 20)->nullable()->after('status'); // client | provider | admin | system
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->unsignedInteger('refund_due_xof')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['cancelled_by', 'cancelled_at', 'refund_due_xof']);
        });
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn('free_cancellation_days');
        });
    }
};

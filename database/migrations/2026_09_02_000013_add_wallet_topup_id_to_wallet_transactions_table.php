<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Pas de contrainte FK DB (même raison que payout_request_id : évite une
            // dépendance d'ordre de migration, relation applicative via Eloquent).
            $table->unsignedBigInteger('wallet_topup_id')->nullable()->after('payout_request_id');

            // Verrou d'idempotence pour les recharges (pas de reservation_id sur ces
            // lignes, donc la contrainte (reservation_id, type) existante ne les protège
            // pas contre un double traitement webhook + retour navigateur).
            $table->unique(['wallet_topup_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['wallet_topup_id', 'type']);
            $table->dropColumn('wallet_topup_id');
        });
    }
};

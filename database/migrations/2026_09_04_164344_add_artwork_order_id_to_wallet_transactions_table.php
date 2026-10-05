<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Pas de contrainte FK DB (même raison que payout_request_id/wallet_topup_id :
            // évite une dépendance d'ordre de migration, relation applicative via Eloquent).
            $table->unsignedBigInteger('artwork_order_id')->nullable()->after('wallet_topup_id');

            // Verrou d'idempotence pour les ventes d'œuvres (mêmes principe que
            // (reservation_id, type) et (wallet_topup_id, type)).
            $table->unique(['artwork_order_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['artwork_order_id', 'type']);
            $table->dropColumn('artwork_order_id');
        });
    }
};

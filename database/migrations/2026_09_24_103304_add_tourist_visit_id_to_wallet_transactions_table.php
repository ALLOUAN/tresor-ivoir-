<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Pas de contrainte FK DB, même raison que artwork_order_id/wallet_topup_id :
            // évite une dépendance d'ordre de migration, relation applicative via Eloquent.
            $table->unsignedBigInteger('tourist_visit_id')->nullable()->after('artwork_order_id');

            // Verrou d'idempotence pour les ventes de visites, même principe que
            // (reservation_id, type) et (artwork_order_id, type).
            $table->unique(['tourist_visit_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['tourist_visit_id', 'type']);
            $table->dropColumn('tourist_visit_id');
        });
    }
};

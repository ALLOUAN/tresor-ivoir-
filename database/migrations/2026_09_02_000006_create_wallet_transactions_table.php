<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('wallet_id')->constrained();
            $table->string('type'); // deposit_collected, commission, provider_credit_pending, provider_credit_available, payout, refund_debit, refund_credit, adjustment
            $table->string('status')->default('completed'); // pending, completed, reversed
            $table->bigInteger('amount_xof'); // signé : positif = crédit, négatif = débit
            $table->unsignedBigInteger('balance_before_xof');
            $table->unsignedBigInteger('balance_after_xof');
            $table->foreignId('reservation_id')->nullable()->constrained();
            // Pas de contrainte FK DB sur payout_request_id : la table payout_requests
            // est créée après celle-ci (elle référence wallets), la relation reste
            // applicative (Eloquent belongsTo) pour éviter une dépendance d'ordre de migration.
            $table->unsignedBigInteger('payout_request_id')->nullable();
            $table->foreignId('reversed_by_transaction_id')->nullable()->constrained('wallet_transactions');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Verrou d'idempotence réel : une seule ligne d'un type donné par réservation.
            // Une violation lors d'un webhook/retour navigateur quasi simultanés est
            // interceptée par WalletService et traitée comme "déjà traité" (no-op).
            $table->unique(['reservation_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

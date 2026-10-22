<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paiement en ligne des demandes de réservation (restaurant, loisir, circuit, offre groupée) :
 * l'établissement fixe le montant en confirmant, le client paie par CinetPay ou son solde.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->unsignedInteger('amount_due_xof')->nullable()->after('party_size');
            $table->decimal('commission_percent', 5, 2)->nullable()->after('amount_due_xof');
            $table->unsignedInteger('commission_amount_xof')->nullable()->after('commission_percent');
            $table->string('payment_status', 20)->default('unpaid')->after('status'); // unpaid | pending | paid | failed | refunded
            $table->string('payment_method', 20)->nullable()->after('payment_status');
            $table->string('gateway_txn_id', 100)->nullable()->index()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('gateway_txn_id');
            $table->unsignedInteger('refund_due_xof')->nullable()->after('paid_at');
            $table->timestamp('cancelled_at')->nullable()->after('refund_due_xof');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('booking_request_id')->nullable()->after('tourist_visit_id')->constrained()->nullOnDelete();
            $table->unique(['booking_request_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['booking_request_id', 'type']);
            $table->dropConstrainedForeignId('booking_request_id');
        });
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropColumn(['amount_due_xof', 'commission_percent', 'commission_amount_xof', 'payment_status',
                'payment_method', 'gateway_txn_id', 'paid_at', 'refund_due_xof', 'cancelled_at']);
        });
    }
};

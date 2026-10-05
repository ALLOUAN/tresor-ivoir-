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
        Schema::create('artwork_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('artwork_id')->constrained();
            $table->foreignId('provider_id')->constrained(); // dénormalisé depuis artwork.provider_id — requêtes "mes commandes" bon marché
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('unit_price_xof');
            $table->unsignedInteger('amount_total_xof');
            // Figés à la création de la commande — jamais recalculés depuis payment_settings
            // à la confirmation (même règle que Reservation.commission_amount_xof) : un
            // changement de taux entre-temps ne doit pas modifier rétroactivement ce que
            // l'acheteur a accepté de payer.
            $table->decimal('commission_percent', 5, 2);
            $table->unsignedInteger('commission_amount_xof');
            $table->unsignedInteger('artist_net_amount_xof');
            $table->string('currency', 3)->default('XOF');
            $table->string('status')->default('pending_payment'); // pending_payment, paid, shipped, delivered, cancelled, refunded
            $table->string('gateway')->default('cinetpay');
            $table->string('gateway_txn_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artwork_orders');
    }
};

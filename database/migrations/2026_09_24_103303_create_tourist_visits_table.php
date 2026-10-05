<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Calquée sur artwork_orders : paiement intégral immédiat, un seul flag de
        // statut, pas de sous-table de paiements (contrairement à reservations/
        // reservation_payments, qui gèrent un acompte + solde sur place).
        Schema::create('tourist_visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tourist_experience_id')->constrained();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete(); // dénormalisé depuis experience->provider_id
            $table->foreignId('tourist_visit_session_id')->nullable()->constrained()->nullOnDelete(); // mode groupé uniquement
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('experience_name'); // snapshot, comme reservations.accommodation_name
            $table->string('visit_type', 20); // individual | guided | group
            $table->boolean('with_guide')->default(false);
            $table->unsignedSmallInteger('participants_count')->default(1);
            $table->date('desired_date')->nullable(); // individuel/guidé — toujours facultatif
            $table->date('session_date')->nullable(); // mode groupé — snapshot figé de la session, jamais recalculé après coup
            $table->string('session_time_label')->nullable();

            $table->unsignedInteger('unit_price_xof')->default(0);
            $table->unsignedInteger('guide_supplement_xof')->default(0);
            $table->unsignedInteger('amount_total_xof')->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->unsignedInteger('commission_amount_xof')->default(0);
            $table->unsignedInteger('provider_net_amount_xof')->default(0);
            $table->string('currency', 3)->default('XOF');

            $table->string('status')->default('pending_payment'); // pending_payment, paid, cancelled, refunded, failed
            $table->string('gateway')->nullable(); // cinetpay | wallet | null (visite gratuite)
            $table->string('gateway_txn_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Requête de décompte de places (bookedSeats) — clé du verrou de capacité.
            $table->index(['tourist_visit_session_id', 'status']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tourist_visits');
    }
};

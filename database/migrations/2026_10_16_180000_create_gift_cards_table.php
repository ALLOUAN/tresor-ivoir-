<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bons cadeaux : achetés en ligne pour un établissement, envoyés au bénéficiaire par e-mail,
 * validés par l'établissement avec leur code. La part de l'établissement reste en attente
 * jusqu'à l'utilisation du bon (ou son expiration).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('code', 16)->unique();
            $table->unsignedInteger('amount_xof');
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->unsignedInteger('commission_amount_xof')->default(0);
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('recipient_name');
            $table->string('recipient_email');
            $table->string('message', 500)->nullable();
            $table->string('status', 20)->default('pending'); // pending | active | redeemed | expired
            $table->string('payment_method', 20)->nullable();
            $table->string('gateway_txn_id', 100)->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('redeemed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->boolean('gift_cards_enabled')->default(false);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('gift_card_id')->nullable()->after('booking_request_id')->constrained()->nullOnDelete();
            $table->unique(['gift_card_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['gift_card_id', 'type']);
            $table->dropConstrainedForeignId('gift_card_id');
        });
        Schema::table('providers', fn (Blueprint $table) => $table->dropColumn('gift_cards_enabled'));
        Schema::dropIfExists('gift_cards');
    }
};

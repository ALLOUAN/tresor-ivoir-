<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('provider_id')->constrained();
            $table->foreignId('wallet_id')->constrained();
            $table->unsignedBigInteger('amount_xof');
            $table->string('method'); // mobile_money, bank_transfer
            $table->string('payout_destination'); // numéro/compte figé au moment de la demande
            $table->string('status')->default('pending'); // pending, approved, rejected, paid
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};

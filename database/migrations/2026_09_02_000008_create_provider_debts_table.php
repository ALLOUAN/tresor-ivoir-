<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained();
            $table->foreignId('wallet_id')->constrained();
            $table->unsignedBigInteger('amount_xof');
            $table->string('reason');
            $table->foreignId('reservation_id')->nullable()->constrained();
            $table->string('status')->default('outstanding'); // outstanding, settled
            $table->timestamp('settled_at')->nullable();
            $table->foreignId('settled_via_payout_request_id')->nullable()->constrained('payout_requests')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_debts');
    }
};

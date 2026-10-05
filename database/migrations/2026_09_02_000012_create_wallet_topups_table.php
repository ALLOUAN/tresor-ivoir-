<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_topups', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('wallet_id')->constrained();
            $table->unsignedBigInteger('amount_xof');
            $table->string('gateway')->default('cinetpay');
            $table->string('gateway_txn_id')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_topups');
    }
};

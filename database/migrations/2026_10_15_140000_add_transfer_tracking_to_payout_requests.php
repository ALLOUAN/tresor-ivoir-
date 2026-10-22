<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Suivi des virements automatiques (API de transfert CinetPay) sur les demandes de retrait. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('transfer_status', 20)->nullable()->after('status'); // sent | failed
            $table->string('transfer_reference', 100)->nullable()->after('transfer_status');
            $table->unsignedSmallInteger('transfer_attempts')->default(0)->after('transfer_reference');
            $table->timestamp('transfer_attempted_at')->nullable()->after('transfer_attempts');
            $table->string('transfer_error', 500)->nullable()->after('transfer_attempted_at');
        });
    }

    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropColumn(['transfer_status', 'transfer_reference', 'transfer_attempts', 'transfer_attempted_at', 'transfer_error']);
        });
    }
};

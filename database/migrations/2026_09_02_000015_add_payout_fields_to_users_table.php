<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('payout_method')->nullable()->after('terms_accepted_at'); // mobile_money, bank_transfer
            $table->string('payout_account_number')->nullable()->after('payout_method');
            $table->string('payout_account_name')->nullable()->after('payout_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payout_method', 'payout_account_number', 'payout_account_name']);
        });
    }
};

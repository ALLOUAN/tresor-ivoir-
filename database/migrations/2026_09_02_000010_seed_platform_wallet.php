<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('wallets')->updateOrInsert(
            ['provider_id' => null],
            ['balance_available_xof' => 0, 'balance_pending_xof' => 0, 'currency' => 'XOF', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('wallets')->whereNull('provider_id')->delete();
    }
};

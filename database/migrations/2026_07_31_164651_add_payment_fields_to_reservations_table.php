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
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('provider_id')->nullable()->after('accommodation_id')
                ->constrained('providers')->nullOnDelete();
            $table->string('payment_status', 20)->default('unpaid')->index()->after('status');
            $table->unsignedInteger('deposit_amount_xof')->nullable()->after('payment_status');
            $table->decimal('commission_rate_percent', 5, 2)->nullable()->after('deposit_amount_xof');
            $table->unsignedInteger('commission_amount_xof')->nullable()->after('commission_rate_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_id');
            $table->dropColumn(['payment_status', 'deposit_amount_xof', 'commission_rate_percent', 'commission_amount_xof']);
        });
    }
};

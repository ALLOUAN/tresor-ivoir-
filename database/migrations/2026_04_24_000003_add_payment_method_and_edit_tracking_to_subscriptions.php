<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('payment_method', ['orange_money', 'mtn_momo', 'wave', 'moov_money', 'card', 'paypal'])
                ->nullable()
                ->after('billing_cycle');

            $table->foreignId('last_edited_by_user_id')
                ->nullable()
                ->after('upgraded_from_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('last_edited_at')
                ->nullable()
                ->after('last_edited_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_edited_by_user_id');
            $table->dropColumn(['payment_method', 'last_edited_at']);
        });
    }
};

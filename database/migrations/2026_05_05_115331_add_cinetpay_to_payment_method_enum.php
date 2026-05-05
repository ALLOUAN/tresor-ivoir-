<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `subscriptions` MODIFY `payment_method` ENUM('orange_money','mtn_momo','wave','moov_money','card','paypal','cinetpay','free') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `subscriptions` MODIFY `payment_method` ENUM('orange_money','mtn_momo','wave','moov_money','card','paypal') NULL");
    }
};

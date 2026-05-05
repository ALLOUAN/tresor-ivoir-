<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `payments` MODIFY `method` ENUM('orange_money','mtn_momo','wave','moov_money','card','paypal','cinetpay','free') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `payments` MODIFY `method` ENUM('orange_money','mtn_momo','wave','moov_money','card','paypal') NOT NULL");
    }
};

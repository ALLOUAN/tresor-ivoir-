<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `subscriptions` MODIFY `status` ENUM('pending','active','suspended','cancelled','expired') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `subscriptions` MODIFY `status` ENUM('active','suspended','cancelled','expired') NOT NULL");
    }
};

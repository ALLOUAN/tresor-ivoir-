<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->text('maintenance_message')->nullable()->after('maintenance_mode');
            $table->text('maintenance_allowed_ips')->nullable()->after('maintenance_message');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['maintenance_message', 'maintenance_allowed_ips']);
        });
    }
};

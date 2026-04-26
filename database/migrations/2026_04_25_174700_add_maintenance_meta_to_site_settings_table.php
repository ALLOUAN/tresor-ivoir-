<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('maintenance_progress')->nullable()->after('maintenance_allowed_ips');
            $table->string('maintenance_eta', 120)->nullable()->after('maintenance_progress');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['maintenance_progress', 'maintenance_eta']);
        });
    }
};

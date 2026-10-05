<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('risk_score')->nullable()->after('payout_destination');
            $table->string('risk_level', 10)->nullable()->after('risk_score');
            $table->json('risk_flags')->nullable()->after('risk_level');
            $table->string('decision', 20)->nullable()->after('risk_flags');
            $table->string('ip_address', 45)->nullable()->after('decision');
            $table->string('user_agent', 255)->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropColumn(['risk_score', 'risk_level', 'risk_flags', 'decision', 'ip_address', 'user_agent']);
        });
    }
};

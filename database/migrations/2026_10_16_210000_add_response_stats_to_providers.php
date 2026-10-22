<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->unsignedInteger('response_minutes')->nullable();
            $table->unsignedTinyInteger('response_rate')->nullable();
            $table->timestamp('response_stats_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('providers', fn (Blueprint $table) => $table->dropColumn(['response_minutes', 'response_rate', 'response_stats_at']));
    }
};

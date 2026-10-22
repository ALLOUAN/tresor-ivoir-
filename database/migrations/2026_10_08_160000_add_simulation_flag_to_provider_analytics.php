<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('provider_analytics')) {
            return;
        }
        Schema::table('provider_analytics', function (Blueprint $table) {
            // Historique de démonstration (commande ai:seed-analytics), distinguable des vraies mesures.
            if (! Schema::hasColumn('provider_analytics', 'is_simulated')) {
                $table->boolean('is_simulated')->default(false)->after('search_appearances');
            }
        });
        Schema::table('provider_analytics', function (Blueprint $table) {
            try {
                $table->index(['provider_id', 'date'], 'provider_analytics_provider_date_idx');
            } catch (\Throwable) {
                // index déjà présent
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('provider_analytics') && Schema::hasColumn('provider_analytics', 'is_simulated')) {
            Schema::table('provider_analytics', function (Blueprint $table) {
                $table->dropColumn('is_simulated');
            });
        }
    }
};

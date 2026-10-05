<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tourist_experiences', function (Blueprint $table) {
            $table->boolean('visit_individual_enabled')->default(false)->after('amenities');
            $table->boolean('visit_guided_enabled')->default(false)->after('visit_individual_enabled');
            $table->boolean('visit_group_enabled')->default(false)->after('visit_guided_enabled');
            // Prix par participant, utilisé pour les modes individuel ET guidé (null = visite gratuite).
            $table->unsignedInteger('visit_individual_price_xof')->nullable()->after('visit_group_enabled');
            // Supplément forfaitaire (par réservation, pas par participant) appliqué uniquement en mode guidé.
            $table->unsignedInteger('visit_guide_supplement_xof')->nullable()->after('visit_individual_price_xof');
        });
    }

    public function down(): void
    {
        Schema::table('tourist_experiences', function (Blueprint $table) {
            $table->dropColumn([
                'visit_individual_enabled',
                'visit_guided_enabled',
                'visit_group_enabled',
                'visit_individual_price_xof',
                'visit_guide_supplement_xof',
            ]);
        });
    }
};

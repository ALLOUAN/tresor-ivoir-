<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // null = forfait générique visible par tous les prestataires (comportement
            // actuel préservé pour bronze/silver/gold) ; renseigné = forfait dédié à une
            // catégorie racine de prestataire (ex. Art & Créations).
            $table->foreignId('provider_category_id')->nullable()->after('group_target')
                ->constrained('provider_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_category_id');
        });
    }
};

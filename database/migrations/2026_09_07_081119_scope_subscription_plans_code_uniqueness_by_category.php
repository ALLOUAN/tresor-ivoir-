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
        // L'unicité globale sur `code` (bronze/silver/gold) limitait le site à 3
        // forfaits au total, tous secteurs confondus — impossible de créer un 4e
        // forfait dédié à Art & Créations en réutilisant ces mêmes codes. On rend
        // l'unicité relative à la catégorie de prestataire ciblée : "gold" peut
        // exister une fois pour les forfaits génériques (provider_category_id NULL)
        // et une fois pour Art & Créations, mais jamais deux fois dans la même
        // catégorie (le contrôle applicatif dans PlanManagementController gère
        // correctement le cas NULL, contrairement à l'unicité SQL composite classique).
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropUnique('subscription_plans_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unique('code');
        });
    }
};

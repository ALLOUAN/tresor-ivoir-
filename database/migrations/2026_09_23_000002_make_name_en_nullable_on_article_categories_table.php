<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le formulaire de rubriques (admin/articles/categories) présente déjà
     * "Nom (EN)" comme facultatif (pas d'attribut required, validation
     * 'nullable' côté ArticleManagementController::storeCategory) — mais la
     * colonne elle-même était NOT NULL sans valeur par défaut, donc créer
     * une rubrique sans remplir ce champ faisait planter l'insertion en base
     * (erreur SQL 1364) au lieu de simplement l'enregistrer sans traduction.
     */
    public function up(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->string('name_en', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->string('name_en', 150)->nullable(false)->change();
        });
    }
};

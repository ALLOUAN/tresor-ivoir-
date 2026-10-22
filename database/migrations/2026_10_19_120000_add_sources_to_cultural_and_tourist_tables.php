<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sources documentaires des fiches de référence (peuples, éléments culturels, sites, villes) :
 * liste de ['label' => …, 'url' => …|null], affichée en bas des fiches publiques.
 */
return new class extends Migration
{
    private const TABLES = ['cultural_peoples', 'cultural_elements', 'tourist_sites', 'tourist_cities'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasColumn($table, 'sources')) {
                Schema::table($table, fn (Blueprint $t) => $t->json('sources')->nullable());
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasColumn($table, 'sources')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('sources'));
            }
        }
    }
};

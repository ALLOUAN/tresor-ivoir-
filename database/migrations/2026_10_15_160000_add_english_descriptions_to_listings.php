<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Version anglaise des descriptions des fiches (hébergements, restaurants, loisirs, sites, villes,
 * peuples…). Colonnes facultatives : tant qu'elles sont vides, la version française est affichée.
 */
return new class extends Migration
{
    private const TABLES = [
        'accommodations' => ['short_description', 'description'],
        'restaurants' => ['short_description', 'description'],
        'leisure_venues' => ['short_description', 'description'],
        'tourist_experiences' => ['short_description', 'description'],
        'tourist_cities' => ['description'],
        'cultural_peoples' => ['description'],
        'cultural_elements' => ['short_description', 'description'],
        'travel_agencies' => ['short_description', 'description'],
        'transport_companies' => ['short_description', 'description'],
        'tourist_sites' => ['short_description', 'description'],
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $fields) {
            Schema::table($table, function (Blueprint $t) use ($table, $fields) {
                foreach ($fields as $field) {
                    if (! Schema::hasColumn($table, $field.'_en')) {
                        $field === 'short_description'
                            ? $t->string($field.'_en', 500)->nullable()->after($field)
                            : $t->text($field.'_en')->nullable()->after($field);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table => $fields) {
            Schema::table($table, function (Blueprint $t) use ($fields) {
                $t->dropColumn(array_map(fn ($f) => $f.'_en', $fields));
            });
        }
    }
};

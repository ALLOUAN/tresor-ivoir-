<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Complète le référentiel ivoirien (régions, sites, peuples, éléments culturels), avec sources.
 * Sans risque sur une base en production : rien d'existant n'est écrasé.
 *
 *   php artisan db:seed --class=IvorianReferenceSeeder --force
 */
class IvorianReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TouristRegionSeeder::class,           // villes de rattachement d'abord
            TouristSiteCompletionSeeder::class,
            TouristSiteByCitySeeder::class,       // sites par ville et par catégorie (villes et catégories vides)
            CulturalPeopleCompletionSeeder::class, // peuples avant les éléments (peuples d'origine)
            CulturalElementCompletionSeeder::class,
            CulturalPeopleProfileSeeder::class,    // symboles, sources et éléments de chaque peuple
        ]);
    }
}

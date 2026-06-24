<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Données de référence (sans dépendances)
            SubscriptionPlanSeeder::class,
            ProviderCategorySeeder::class,
            ArticleCategorySeeder::class,
            EventCategorySeeder::class,
            TagSeeder::class,

            // Tourisme (référentiels indépendants)
            TouristCategorySeeder::class,
            TouristCitySeeder::class,
            TouristSiteSeeder::class,
            AccommodationSeeder::class,

            // Cultures Ivoiriennes (référentiels indépendants)
            CulturalPeopleSeeder::class,
            CulturalDomainSeeder::class,
            CulturalElementSeeder::class,

            // Utilisateurs
            UserSeeder::class,
            PartnerSeeder::class,

            // Contenu métier (dépend des référentiels et users)
            ProviderSeeder::class,
            ArticleSeeder::class,
            EventSeeder::class,
            ReviewSeeder::class,

            // Pages centre d'information (légal, FAQ, etc.)
            InformationPageSeeder::class,
        ]);
    }
}

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

            // Utilisateurs
            UserSeeder::class,

            // Contenu métier (dépend des référentiels et users)
            ProviderSeeder::class,
            ArticleSeeder::class,
            EventSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}

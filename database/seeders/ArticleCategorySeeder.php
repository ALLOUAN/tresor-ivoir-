<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'destination-du-mois',
                'name_fr' => 'Destination du mois',
                'name_en' => 'Destination of the Month',
                'description_fr' => 'Chaque mois, TRÉSORS D\'IVOIRE met à l\'honneur une destination exceptionnelle de Côte d\'Ivoire.',
                'description_en' => 'Each month, TRÉSORS D\'IVOIRE highlights an exceptional destination in Ivory Coast.',
                'color_hex' => '#E8A838',
                'icon' => 'fa-map-pin',
                'sort_order' => 1,
            ],
            [
                'slug' => 'culture-traditions',
                'name_fr' => 'Culture & Traditions',
                'name_en' => 'Culture & Traditions',
                'description_fr' => 'Art, musique, danse, artisanat et patrimoine immatériel des peuples de Côte d\'Ivoire.',
                'description_en' => 'Art, music, dance, crafts and intangible heritage of the peoples of Ivory Coast.',
                'color_hex' => '#8E44AD',
                'icon' => 'fa-masks-theater',
                'sort_order' => 2,
            ],
            [
                'slug' => 'gastronomie',
                'name_fr' => 'Gastronomie',
                'name_en' => 'Gastronomy',
                'description_fr' => 'Cuisine ivoirienne, recettes traditionnelles, restaurants à découvrir et chefs inspirants.',
                'description_en' => 'Ivorian cuisine, traditional recipes, restaurants to discover and inspiring chefs.',
                'color_hex' => '#C0392B',
                'icon' => 'fa-utensils',
                'sort_order' => 3,
            ],
            [
                'slug' => 'portraits',
                'name_fr' => 'Portraits',
                'name_en' => 'Portraits',
                'description_fr' => 'Rencontres avec des personnalités qui font rayonner la Côte d\'Ivoire.',
                'description_en' => 'Encounters with personalities who make Ivory Coast shine.',
                'color_hex' => '#2980B9',
                'icon' => 'fa-user-tie',
                'sort_order' => 4,
            ],
            [
                'slug' => 'nature-aventure',
                'name_fr' => 'Nature & Aventure',
                'name_en' => 'Nature & Adventure',
                'description_fr' => 'Parcs nationaux, forêts tropicales, plages et activités outdoor en Côte d\'Ivoire.',
                'description_en' => 'National parks, tropical forests, beaches and outdoor activities in Ivory Coast.',
                'color_hex' => '#27AE60',
                'icon' => 'fa-tree',
                'sort_order' => 5,
            ],
            [
                'slug' => 'art-de-vivre',
                'name_fr' => 'Art de vivre',
                'name_en' => 'Lifestyle',
                'description_fr' => 'Mode, décoration, bien-être et toute la douceur de vivre à l\'ivoirienne.',
                'description_en' => 'Fashion, decoration, wellness and the sweet life à l\'ivoirienne.',
                'color_hex' => '#E74C3C',
                'icon' => 'fa-gem',
                'sort_order' => 6,
            ],
            [
                'slug' => 'agenda',
                'name_fr' => 'Agenda & Événements',
                'name_en' => 'Events & Agenda',
                'description_fr' => 'Festivals, expositions, concerts et événements culturels à ne pas manquer.',
                'description_en' => 'Festivals, exhibitions, concerts and cultural events not to miss.',
                'color_hex' => '#E67E22',
                'icon' => 'fa-calendar-days',
                'sort_order' => 7,
            ],
            [
                'slug' => 'pratique',
                'name_fr' => 'Guide Pratique',
                'name_en' => 'Practical Guide',
                'description_fr' => 'Conseils de voyage, visas, santé, budget et tout ce qu\'il faut savoir avant de partir.',
                'description_en' => 'Travel tips, visas, health, budget and everything you need to know before leaving.',
                'color_hex' => '#1ABC9C',
                'icon' => 'fa-circle-info',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            $category['is_active'] = true;
            ArticleCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}

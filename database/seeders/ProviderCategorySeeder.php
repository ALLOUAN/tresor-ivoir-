<?php

namespace Database\Seeders;

use App\Models\ProviderCategory;
use Illuminate\Database\Seeder;

class ProviderCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'hotels',
                'name_fr' => 'Hôtels & Hébergements',
                'name_en' => 'Hotels & Accommodations',
                'icon' => 'fa-bed',
                'color_hex' => '#E8A838',
                'sort_order' => 1,
                'children' => [
                    ['slug' => 'hotels-luxe',     'name_fr' => 'Hôtels de luxe',    'name_en' => 'Luxury Hotels',   'icon' => 'fa-star',        'sort_order' => 1],
                    ['slug' => 'hotels-boutique',  'name_fr' => 'Hôtels boutique',   'name_en' => 'Boutique Hotels', 'icon' => 'fa-home',        'sort_order' => 2],
                    ['slug' => 'auberges',         'name_fr' => 'Auberges & Lodges', 'name_en' => 'Lodges & Inns',   'icon' => 'fa-campground',  'sort_order' => 3],
                    ['slug' => 'apartments',       'name_fr' => 'Appartements',      'name_en' => 'Apartments',      'icon' => 'fa-building',    'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'restaurants',
                'name_fr' => 'Restaurants & Gastronomie',
                'name_en' => 'Restaurants & Gastronomy',
                'icon' => 'fa-utensils',
                'color_hex' => '#C0392B',
                'sort_order' => 2,
                'children' => [
                    ['slug' => 'restaurants-gastronomiques', 'name_fr' => 'Gastronomiques',   'name_en' => 'Fine Dining',     'icon' => 'fa-wine-glass', 'sort_order' => 1],
                    ['slug' => 'cuisine-locale',              'name_fr' => 'Cuisine locale',   'name_en' => 'Local Cuisine',   'icon' => 'fa-bowl-food',  'sort_order' => 2],
                    ['slug' => 'maquis-terrasses',            'name_fr' => 'Maquis & Terrasses', 'name_en' => 'Maquis & Bars',  'icon' => 'fa-beer-mug-empty', 'sort_order' => 3],
                    ['slug' => 'street-food',                 'name_fr' => 'Street food',      'name_en' => 'Street Food',    'icon' => 'fa-hamburger',  'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'sites-touristiques',
                'name_fr' => 'Sites Touristiques',
                'name_en' => 'Tourist Sites',
                'icon' => 'fa-landmark',
                'color_hex' => '#27AE60',
                'sort_order' => 3,
                'children' => [
                    ['slug' => 'parcs-naturels',  'name_fr' => 'Parcs & Réserves',  'name_en' => 'Parks & Reserves',    'icon' => 'fa-tree',       'sort_order' => 1],
                    ['slug' => 'plages',          'name_fr' => 'Plages & Lagunes',  'name_en' => 'Beaches & Lagoons',   'icon' => 'fa-umbrella-beach', 'sort_order' => 2],
                    ['slug' => 'monuments',       'name_fr' => 'Monuments & Musées', 'name_en' => 'Monuments & Museums', 'icon' => 'fa-museum',     'sort_order' => 3],
                    ['slug' => 'villages-culturels', 'name_fr' => 'Villages culturels', 'name_en' => 'Cultural Villages',  'icon' => 'fa-city',       'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'agences-voyages',
                'name_fr' => 'Agences de Voyages & Tours',
                'name_en' => 'Travel Agencies & Tours',
                'icon' => 'fa-plane',
                'color_hex' => '#2980B9',
                'sort_order' => 4,
                'children' => [
                    ['slug' => 'agences-receptives', 'name_fr' => 'Agences réceptives', 'name_en' => 'Inbound Agencies', 'icon' => 'fa-handshake', 'sort_order' => 1],
                    ['slug' => 'circuits-safaris',   'name_fr' => 'Circuits & Safaris', 'name_en' => 'Tours & Safaris',  'icon' => 'fa-route',     'sort_order' => 2],
                    ['slug' => 'guides-touristiques', 'name_fr' => 'Guides touristiques', 'name_en' => 'Tour Guides',      'icon' => 'fa-map',       'sort_order' => 3],
                ],
            ],
            [
                'slug' => 'loisirs-culture',
                'name_fr' => 'Loisirs & Culture',
                'name_en' => 'Leisure & Culture',
                'icon' => 'fa-masks-theater',
                'color_hex' => '#8E44AD',
                'sort_order' => 5,
                'children' => [
                    ['slug' => 'salles-spectacles', 'name_fr' => 'Salles de spectacles', 'name_en' => 'Concert Halls',    'icon' => 'fa-music',      'sort_order' => 1],
                    ['slug' => 'galeries-art',      'name_fr' => 'Galeries d\'art',       'name_en' => 'Art Galleries',    'icon' => 'fa-palette',    'sort_order' => 2],
                    ['slug' => 'spas-bien-etre',    'name_fr' => 'Spas & Bien-être',      'name_en' => 'Spas & Wellness',  'icon' => 'fa-spa',        'sort_order' => 3],
                    ['slug' => 'sports-aventure',   'name_fr' => 'Sports & Aventure',     'name_en' => 'Sports & Adventure', 'icon' => 'fa-person-hiking', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'transports',
                'name_fr' => 'Transports & Mobilité',
                'name_en' => 'Transport & Mobility',
                'icon' => 'fa-car',
                'color_hex' => '#16A085',
                'sort_order' => 6,
                'children' => [
                    ['slug' => 'location-voitures', 'name_fr' => 'Location de voitures', 'name_en' => 'Car Rental',        'icon' => 'fa-car-side', 'sort_order' => 1],
                    ['slug' => 'transferts',        'name_fr' => 'Transferts & Navettes', 'name_en' => 'Transfers & Shuttles', 'icon' => 'fa-shuttle-van', 'sort_order' => 2],
                ],
            ],
        ];

        foreach ($categories as $data) {
            $children = $data['children'] ?? [];
            unset($data['children']);
            $data['is_active'] = true;

            $parent = ProviderCategory::updateOrCreate(['slug' => $data['slug']], $data);

            foreach ($children as $child) {
                $child['parent_id'] = $parent->id;
                $child['is_active'] = true;
                $child['color_hex'] = $data['color_hex'] ?? null;
                ProviderCategory::updateOrCreate(['slug' => $child['slug']], $child);
            }
        }
    }
}

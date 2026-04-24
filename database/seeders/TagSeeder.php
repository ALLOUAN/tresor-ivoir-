<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Provider tags (équipements & services)
            ['slug' => 'wifi',            'name_fr' => 'WiFi gratuit',      'name_en' => 'Free WiFi',         'type' => 'provider'],
            ['slug' => 'piscine',         'name_fr' => 'Piscine',           'name_en' => 'Swimming Pool',     'type' => 'provider'],
            ['slug' => 'parking',         'name_fr' => 'Parking',           'name_en' => 'Parking',           'type' => 'provider'],
            ['slug' => 'spa',             'name_fr' => 'Spa',               'name_en' => 'Spa',               'type' => 'provider'],
            ['slug' => 'restaurant',      'name_fr' => 'Restaurant',        'name_en' => 'Restaurant',        'type' => 'provider'],
            ['slug' => 'climatisation',   'name_fr' => 'Climatisation',     'name_en' => 'Air Conditioning',  'type' => 'provider'],
            ['slug' => 'salle-conference', 'name_fr' => 'Salle conférence',  'name_en' => 'Conference Room',   'type' => 'provider'],
            ['slug' => 'navette-aeroport', 'name_fr' => 'Navette aéroport',  'name_en' => 'Airport Shuttle',   'type' => 'provider'],
            ['slug' => 'bar',             'name_fr' => 'Bar',               'name_en' => 'Bar',               'type' => 'provider'],
            ['slug' => 'gym',             'name_fr' => 'Salle de sport',    'name_en' => 'Gym',               'type' => 'provider'],
            ['slug' => 'accessible',      'name_fr' => 'Accès PMR',         'name_en' => 'Accessible',        'type' => 'provider'],
            ['slug' => 'animaux',         'name_fr' => 'Animaux acceptés',  'name_en' => 'Pet Friendly',      'type' => 'provider'],
            ['slug' => 'halal',           'name_fr' => 'Cuisine Halal',     'name_en' => 'Halal Food',        'type' => 'provider'],
            ['slug' => 'vue-mer',         'name_fr' => 'Vue mer / lagune',  'name_en' => 'Sea / Lagoon View', 'type' => 'provider'],
            ['slug' => 'terrasse',        'name_fr' => 'Terrasse',          'name_en' => 'Terrace',           'type' => 'provider'],

            // Article tags
            ['slug' => 'abidjan',         'name_fr' => 'Abidjan',           'name_en' => 'Abidjan',           'type' => 'article'],
            ['slug' => 'grand-bassam',    'name_fr' => 'Grand-Bassam',      'name_en' => 'Grand-Bassam',      'type' => 'article'],
            ['slug' => 'yamoussoukro',    'name_fr' => 'Yamoussoukro',      'name_en' => 'Yamoussoukro',      'type' => 'article'],
            ['slug' => 'san-pedro',       'name_fr' => 'San-Pédro',         'name_en' => 'San-Pédro',         'type' => 'article'],
            ['slug' => 'man',             'name_fr' => 'Man & Ouest',       'name_en' => 'Man & West',        'type' => 'article'],
            ['slug' => 'safari',          'name_fr' => 'Safari',            'name_en' => 'Safari',            'type' => 'article'],
            ['slug' => 'patrimoine',      'name_fr' => 'Patrimoine',        'name_en' => 'Heritage',          'type' => 'article'],
            ['slug' => 'plage',           'name_fr' => 'Plage',             'name_en' => 'Beach',             'type' => 'article'],
            ['slug' => 'musique',         'name_fr' => 'Musique',           'name_en' => 'Music',             'type' => 'article'],
            ['slug' => 'masques',         'name_fr' => 'Masques & Arts',    'name_en' => 'Masks & Arts',      'type' => 'article'],

            // Shared tags
            ['slug' => 'nature',          'name_fr' => 'Nature',            'name_en' => 'Nature',            'type' => 'both'],
            ['slug' => 'famille',         'name_fr' => 'En famille',        'name_en' => 'Family',            'type' => 'both'],
            ['slug' => 'romantique',      'name_fr' => 'Romantique',        'name_en' => 'Romantic',          'type' => 'both'],
            ['slug' => 'luxe',            'name_fr' => 'Luxe',              'name_en' => 'Luxury',            'type' => 'both'],
            ['slug' => 'eco-tourisme',    'name_fr' => 'Éco-tourisme',      'name_en' => 'Eco-Tourism',       'type' => 'both'],
            ['slug' => 'coup-de-coeur',   'name_fr' => 'Coup de cœur',      'name_en' => 'Must-See',          'type' => 'both'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(['slug' => $tag['slug']], $tag);
        }
    }
}

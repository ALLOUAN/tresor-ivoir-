<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'festivals',          'name_fr' => 'Festivals',                   'name_en' => 'Festivals',              'icon' => 'fa-star',            'color_hex' => '#E8A838', 'sort_order' => 1],
            ['slug' => 'concerts',           'name_fr' => 'Concerts & Musique',          'name_en' => 'Concerts & Music',       'icon' => 'fa-music',           'color_hex' => '#8E44AD', 'sort_order' => 2],
            ['slug' => 'expositions',        'name_fr' => 'Expositions & Art',           'name_en' => 'Exhibitions & Art',      'icon' => 'fa-palette',         'color_hex' => '#2980B9', 'sort_order' => 3],
            ['slug' => 'evenements-culturels', 'name_fr' => 'Événements culturels',         'name_en' => 'Cultural Events',        'icon' => 'fa-masks-theater',   'color_hex' => '#C0392B', 'sort_order' => 4],
            ['slug' => 'evenements-sportifs', 'name_fr' => 'Événements sportifs',          'name_en' => 'Sports Events',          'icon' => 'fa-trophy',          'color_hex' => '#27AE60', 'sort_order' => 5],
            ['slug' => 'gastronomie-events', 'name_fr' => 'Gastronomie & Foires',        'name_en' => 'Food & Fairs',           'icon' => 'fa-utensils',        'color_hex' => '#E67E22', 'sort_order' => 6],
            ['slug' => 'conferences',        'name_fr' => 'Conférences & Forums',        'name_en' => 'Conferences & Forums',   'icon' => 'fa-microphone',      'color_hex' => '#16A085', 'sort_order' => 7],
            ['slug' => 'traditions',         'name_fr' => 'Cérémonies traditionnelles',  'name_en' => 'Traditional Ceremonies', 'icon' => 'fa-drum',            'color_hex' => '#D35400', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            EventCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}

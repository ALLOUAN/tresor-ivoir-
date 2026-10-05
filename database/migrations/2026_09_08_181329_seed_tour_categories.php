<?php

use App\Models\TourCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const CATEGORIES = [
        ['slug' => 'decouverte', 'name_fr' => 'Circuits découverte', 'name_en' => 'Discovery tours', 'icon' => 'fas fa-map-location-dot', 'sort_order' => 1],
        ['slug' => 'aventure-nature', 'name_fr' => 'Aventure & Nature', 'name_en' => 'Adventure & Nature', 'icon' => 'fas fa-person-hiking', 'sort_order' => 2],
        ['slug' => 'culture-patrimoine', 'name_fr' => 'Culture & Patrimoine', 'name_en' => 'Culture & Heritage', 'icon' => 'fas fa-landmark', 'sort_order' => 3],
        ['slug' => 'excursions-journee', 'name_fr' => "Excursions d'une journée", 'name_en' => 'Day trips', 'icon' => 'fas fa-sun', 'sort_order' => 4],
    ];

    public function up(): void
    {
        foreach (self::CATEGORIES as $category) {
            TourCategory::query()->firstOrCreate(['slug' => $category['slug']], $category);
        }
    }

    public function down(): void
    {
        TourCategory::query()->whereIn('slug', array_column(self::CATEGORIES, 'slug'))->delete();
    }
};

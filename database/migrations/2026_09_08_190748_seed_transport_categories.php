<?php

use App\Models\TransportCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const CATEGORIES = [
        ['slug' => 'location-vehicules', 'name_fr' => 'Location de véhicules', 'name_en' => 'Vehicle rental', 'icon' => 'fas fa-car-side', 'sort_order' => 1],
        ['slug' => 'transferts-navettes', 'name_fr' => 'Transferts & Navettes', 'name_en' => 'Transfers & Shuttles', 'icon' => 'fas fa-shuttle-van', 'sort_order' => 2],
        ['slug' => 'chauffeur-prive', 'name_fr' => 'Chauffeur privé', 'name_en' => 'Private driver', 'icon' => 'fas fa-id-card', 'sort_order' => 3],
        ['slug' => 'excursions-vehicule', 'name_fr' => 'Excursions en véhicule', 'name_en' => 'Vehicle excursions', 'icon' => 'fas fa-route', 'sort_order' => 4],
    ];

    public function up(): void
    {
        foreach (self::CATEGORIES as $category) {
            TransportCategory::query()->firstOrCreate(['slug' => $category['slug']], $category);
        }
    }

    public function down(): void
    {
        TransportCategory::query()->whereIn('slug', array_column(self::CATEGORIES, 'slug'))->delete();
    }
};

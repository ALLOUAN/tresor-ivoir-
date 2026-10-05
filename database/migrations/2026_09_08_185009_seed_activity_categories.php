<?php

use App\Models\ActivityCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const CATEGORIES = [
        ['slug' => 'spectacles', 'name_fr' => 'Spectacles', 'name_en' => 'Shows', 'icon' => 'fas fa-masks-theater', 'sort_order' => 1],
        ['slug' => 'bien-etre-spa', 'name_fr' => 'Bien-être & Spa', 'name_en' => 'Wellness & Spa', 'icon' => 'fas fa-spa', 'sort_order' => 2],
        ['slug' => 'sport-aventure', 'name_fr' => 'Sport & Aventure', 'name_en' => 'Sport & Adventure', 'icon' => 'fas fa-person-hiking', 'sort_order' => 3],
        ['slug' => 'ateliers-decouverte', 'name_fr' => 'Ateliers & Découverte', 'name_en' => 'Workshops & Discovery', 'icon' => 'fas fa-palette', 'sort_order' => 4],
    ];

    public function up(): void
    {
        foreach (self::CATEGORIES as $category) {
            ActivityCategory::query()->firstOrCreate(['slug' => $category['slug']], $category);
        }
    }

    public function down(): void
    {
        ActivityCategory::query()->whereIn('slug', array_column(self::CATEGORIES, 'slug'))->delete();
    }
};

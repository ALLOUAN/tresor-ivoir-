<?php

use App\Models\MenuCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const CATEGORIES = [
        ['slug' => 'entrees', 'name_fr' => 'Entrées', 'name_en' => 'Starters', 'icon' => 'fas fa-seedling', 'sort_order' => 1],
        ['slug' => 'plats', 'name_fr' => 'Plats', 'name_en' => 'Main courses', 'icon' => 'fas fa-utensils', 'sort_order' => 2],
        ['slug' => 'desserts', 'name_fr' => 'Desserts', 'name_en' => 'Desserts', 'icon' => 'fas fa-ice-cream', 'sort_order' => 3],
        ['slug' => 'boissons', 'name_fr' => 'Boissons', 'name_en' => 'Drinks', 'icon' => 'fas fa-martini-glass-citrus', 'sort_order' => 4],
    ];

    public function up(): void
    {
        foreach (self::CATEGORIES as $category) {
            MenuCategory::query()->firstOrCreate(['slug' => $category['slug']], $category);
        }
    }

    public function down(): void
    {
        MenuCategory::query()->whereIn('slug', array_column(self::CATEGORIES, 'slug'))->delete();
    }
};

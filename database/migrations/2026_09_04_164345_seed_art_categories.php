<?php

use App\Models\ArtworkCategory;
use App\Models\ProviderCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const PROVIDER_CATEGORY_SLUGS = ['artiste-peintre', 'galerie-art', 'artisan-art'];

    private const ARTWORK_CATEGORY_SLUGS = ['peintures', 'sculptures', 'photographies', 'artisanat-art'];

    public function up(): void
    {
        $providerCategories = [
            ['slug' => 'artiste-peintre', 'name_fr' => 'Artiste peintre', 'name_en' => 'Painter', 'icon' => 'fas fa-palette', 'sort_order' => 90],
            ['slug' => 'galerie-art', 'name_fr' => 'Galerie d\'art', 'name_en' => 'Art gallery', 'icon' => 'fas fa-images', 'sort_order' => 91],
            ['slug' => 'artisan-art', 'name_fr' => 'Artisan d\'art', 'name_en' => 'Art craftsman', 'icon' => 'fas fa-hammer', 'sort_order' => 92],
        ];

        foreach ($providerCategories as $category) {
            ProviderCategory::query()->firstOrCreate(['slug' => $category['slug']], $category);
        }

        $artworkCategories = [
            ['slug' => 'peintures', 'name_fr' => 'Peintures', 'name_en' => 'Paintings', 'icon' => 'fas fa-palette', 'sort_order' => 1],
            ['slug' => 'sculptures', 'name_fr' => 'Sculptures', 'name_en' => 'Sculptures', 'icon' => 'fas fa-chess-rook', 'sort_order' => 2],
            ['slug' => 'photographies', 'name_fr' => 'Photographies', 'name_en' => 'Photographs', 'icon' => 'fas fa-camera', 'sort_order' => 3],
            ['slug' => 'artisanat-art', 'name_fr' => 'Artisanat d\'exception', 'name_en' => 'Exceptional craftsmanship', 'icon' => 'fas fa-hammer', 'sort_order' => 4],
        ];

        foreach ($artworkCategories as $category) {
            ArtworkCategory::query()->firstOrCreate(['slug' => $category['slug']], $category);
        }
    }

    public function down(): void
    {
        ProviderCategory::query()->whereIn('slug', self::PROVIDER_CATEGORY_SLUGS)->delete();
        ArtworkCategory::query()->whereIn('slug', self::ARTWORK_CATEGORY_SLUGS)->delete();
    }
};

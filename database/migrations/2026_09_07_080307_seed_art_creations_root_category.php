<?php

use App\Models\ArtworkCategory;
use App\Models\ProviderCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const ART_CHILD_SLUGS = ['artiste-peintre', 'galerie-art', 'artisan-art'];

    /**
     * Regroupe les 3 catégories artistes (créées comme racines indépendantes en
     * Phase 1) sous une nouvelle catégorie racine « Art & Créations », suivant
     * exactement le motif déjà établi par ProviderCategorySeeder (ex. « Hôtels &
     * Hébergements » racine avec 4 enfants) — nécessaire pour que le ciblage des
     * forfaits d'abonnement et le gating d'accès puissent se faire par « racine »,
     * comme c'est déjà le cas pour les hôtels.
     */
    public function up(): void
    {
        $root = ProviderCategory::query()->updateOrCreate(
            ['slug' => 'art-creations'],
            [
                'name_fr' => 'Art & Créations',
                'name_en' => 'Art & Creations',
                'icon' => 'fa-palette',
                'color_hex' => '#F2790F',
                'sort_order' => 7,
                'is_active' => true,
            ]
        );

        ProviderCategory::query()
            ->whereIn('slug', self::ART_CHILD_SLUGS)
            ->update(['parent_id' => $root->id]);

        $missingArtworkCategories = [
            ['slug' => 'illustrations', 'name_fr' => 'Illustrations', 'name_en' => 'Illustrations', 'icon' => 'fas fa-pen-nib', 'sort_order' => 5],
            ['slug' => 'objets-art', 'name_fr' => "Objets d'art", 'name_en' => 'Art objects', 'icon' => 'fas fa-vase', 'sort_order' => 6],
            ['slug' => 'oeuvres-contemporaines', 'name_fr' => 'Œuvres contemporaines', 'name_en' => 'Contemporary works', 'icon' => 'fas fa-shapes', 'sort_order' => 7],
        ];

        foreach ($missingArtworkCategories as $category) {
            ArtworkCategory::query()->firstOrCreate(['slug' => $category['slug']], $category);
        }
    }

    public function down(): void
    {
        ProviderCategory::query()
            ->whereIn('slug', self::ART_CHILD_SLUGS)
            ->update(['parent_id' => null]);

        ProviderCategory::query()->where('slug', 'art-creations')->delete();

        ArtworkCategory::query()
            ->whereIn('slug', ['illustrations', 'objets-art', 'oeuvres-contemporaines'])
            ->delete();
    }
};

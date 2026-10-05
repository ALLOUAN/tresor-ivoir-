<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Restaurant;
use App\Models\TouristCity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crée la fiche riche « Restaurant » (établissement, page publique dédiée) pour chaque
 * prestataire de la catégorie Restaurants & Gastronomie qui n'en a pas encore — sans
 * quoi son contenu (déjà peuplé par ProviderOfferingsSeeder) ne remonte que sur
 * l'annuaire générique, jamais sur /restaurants. N'ajoute rien à un prestataire qui a
 * déjà sa fiche (seed idempotent, comme ProviderOfferingsSeeder::seedRoomTypes()).
 */
class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCityId = TouristCity::where('slug', 'abidjan')->value('id');

        Provider::with('category', 'restaurant')->get()->each(function (Provider $provider) use ($defaultCityId) {
            $category = $provider->category;
            $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;

            if ($rootSlug !== 'restaurants' || $provider->restaurant) {
                return;
            }

            $cityId = TouristCity::where('name', $provider->city)->value('id') ?? $defaultCityId;

            Restaurant::create([
                'provider_id' => $provider->id,
                'city_id' => $cityId,
                'name' => $provider->name,
                'slug' => $this->uniqueSlug($provider->name),
                'short_description' => $provider->description_fr
                    ? Str::limit($provider->description_fr, 150)
                    : null,
                'description' => $provider->description_fr,
                'adresse' => $provider->address,
                'phone' => $provider->phone,
                'email' => $provider->email,
                'website' => $provider->website,
                'is_active' => true,
                'is_featured' => false,
            ]);
        });

        $this->command->info('✓ Fiches Restaurant créées pour les prestataires « Restaurants & Gastronomie » qui n\'en avaient pas.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'restaurant';
        $slug = $base;
        $i = 1;
        while (Restaurant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}

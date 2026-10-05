<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\TouristCity;
use App\Models\TransportCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crée la fiche riche « TransportCompany » (établissement, page publique dédiée) pour
 * chaque prestataire de la catégorie Transports & Mobilité qui n'en a pas encore —
 * sans quoi son contenu (déjà peuplé par ProviderOfferingsSeeder) ne remonte que sur
 * l'annuaire générique, jamais sur /transports. N'ajoute rien à un prestataire qui a
 * déjà sa fiche (seed idempotent, comme RestaurantSeeder).
 */
class TransportCompanySeeder extends Seeder
{
    public function run(): void
    {
        $defaultCityId = TouristCity::where('slug', 'abidjan')->value('id');

        Provider::with('category', 'transportCompany')->get()->each(function (Provider $provider) use ($defaultCityId) {
            $category = $provider->category;
            $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;

            if ($rootSlug !== 'transports' || $provider->transportCompany) {
                return;
            }

            $cityId = TouristCity::where('name', $provider->city)->value('id') ?? $defaultCityId;

            TransportCompany::create([
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

        $this->command->info('✓ Fiches TransportCompany créées pour les prestataires « Transports & Mobilité » qui n\'en avaient pas.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'transport';
        $slug = $base;
        $i = 1;
        while (TransportCompany::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}

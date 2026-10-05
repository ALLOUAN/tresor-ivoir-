<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\MenuItem;
use App\Models\Provider;
use App\Models\TourPackage;
use App\Models\TransportOffer;
use Illuminate\Database\Seeder;

/**
 * Peuple chaque prestataire de démonstration avec du contenu réaliste correspondant
 * à sa catégorie (plats pour les restaurants, activités pour Loisirs & Culture /
 * Sites Touristiques, circuits pour les agences de voyages, offres pour le
 * transport, chambres pour les hôtels déjà reliés à une fiche hébergement) —
 * afin que chaque fiche annuaire ait quelque chose à afficher. N'ajoute rien à un
 * prestataire qui a déjà du contenu du type correspondant (seed idempotent).
 */
class ProviderOfferingsSeeder extends Seeder
{
    public function run(): void
    {
        Provider::with('category', 'accommodation')->get()->each(function (Provider $provider) {
            $category = $provider->category;
            $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;

            match ($rootSlug) {
                'restaurants' => $this->seedMenuItems($provider),
                'sites-touristiques', 'loisirs-culture' => $this->seedActivities($provider),
                'agences-voyages' => $this->seedTours($provider),
                'transports' => $this->seedTransportOffers($provider),
                'hotels' => $this->seedRoomTypes($provider),
                default => null,
            };
        });
    }

    private function seedMenuItems(Provider $provider): void
    {
        if ($provider->menuItems()->exists()) {
            return;
        }

        $items = [
            ['category_id' => 1, 'name' => 'Alloco aux crevettes', 'price_xof' => 2500],
            ['category_id' => 2, 'name' => 'Poulet braisé sauce graine', 'price_xof' => 6000],
            ['category_id' => 2, 'name' => 'Poisson braisé à l\'attiéké', 'price_xof' => 5500],
            ['category_id' => 3, 'name' => 'Beignets de banane plantain', 'price_xof' => 2000],
            ['category_id' => 4, 'name' => 'Jus de bissap frais', 'price_xof' => 1000],
        ];

        foreach ($items as $i => $item) {
            MenuItem::create([
                'provider_id' => $provider->id,
                'category_id' => $item['category_id'],
                'name' => $item['name'],
                'price_xof' => $item['price_xof'],
                'is_available' => true,
                'sort_order' => $i,
            ]);
        }
    }

    private function seedActivities(Provider $provider): void
    {
        if ($provider->activities()->exists()) {
            return;
        }

        $activities = match (true) {
            str_contains(mb_strtolower($provider->name), 'parc national') => [
                ['category_id' => 3, 'name' => 'Randonnée guidée en forêt primaire', 'duration' => 180, 'max' => 12, 'price' => 15000],
                ['category_id' => 3, 'name' => 'Observation de la faune (chimpanzés, éléphants)', 'duration' => 150, 'max' => 8, 'price' => 20000],
                ['category_id' => 4, 'name' => 'Atelier sensibilisation à la biodiversité', 'duration' => 60, 'max' => 25, 'price' => 3000],
            ],
            str_contains(mb_strtolower($provider->name), 'basilique') => [
                ['category_id' => 4, 'name' => 'Visite guidée de la basilique', 'duration' => 90, 'max' => 20, 'price' => 5000],
                ['category_id' => 4, 'name' => 'Ascension du dôme panoramique', 'duration' => 45, 'max' => 10, 'price' => 3500],
            ],
            str_contains(mb_strtolower($provider->name), 'cascade') => [
                ['category_id' => 3, 'name' => 'Randonnée vers les cascades', 'duration' => 120, 'max' => 15, 'price' => 8000],
                ['category_id' => 3, 'name' => 'Baignade encadrée en cascade', 'duration' => 60, 'max' => 10, 'price' => 5000],
            ],
            default => [
                ['category_id' => 2, 'name' => 'Séance de relaxation & spa', 'duration' => 60, 'max' => 4, 'price' => 12000],
                ['category_id' => 1, 'name' => 'Soirée spectacle live', 'duration' => 120, 'max' => 50, 'price' => 5000],
                ['category_id' => 4, 'name' => 'Atelier de danse traditionnelle', 'duration' => 90, 'max' => 15, 'price' => 4000],
            ],
        };

        foreach ($activities as $i => $activity) {
            Activity::create([
                'provider_id' => $provider->id,
                'category_id' => $activity['category_id'],
                'name' => $activity['name'],
                'duration_minutes' => $activity['duration'],
                'max_participants' => $activity['max'],
                'price_xof' => $activity['price'],
                'is_available' => true,
                'sort_order' => $i,
            ]);
        }
    }

    private function seedTours(Provider $provider): void
    {
        if ($provider->tourPackages()->exists()) {
            return;
        }

        $tours = [
            ['category_id' => 1, 'name' => 'Circuit découverte d\'Abidjan', 'days' => 1, 'max' => 15, 'price' => 25000],
            ['category_id' => 4, 'name' => 'Excursion à Grand-Bassam', 'days' => 1, 'max' => 20, 'price' => 18000],
            ['category_id' => 2, 'name' => 'Aventure nature dans l\'Ouest montagneux', 'days' => 3, 'max' => 10, 'price' => 95000],
        ];

        foreach ($tours as $i => $tour) {
            TourPackage::create([
                'provider_id' => $provider->id,
                'category_id' => $tour['category_id'],
                'name' => $tour['name'],
                'duration_days' => $tour['days'],
                'max_participants' => $tour['max'],
                'price_xof' => $tour['price'],
                'is_available' => true,
                'sort_order' => $i,
            ]);
        }
    }

    private function seedTransportOffers(Provider $provider): void
    {
        if ($provider->transportOffers()->exists()) {
            return;
        }

        $offers = [
            ['category_id' => 2, 'name' => 'Transfert aéroport', 'max_passengers' => 3, 'price' => 15000],
            ['category_id' => 3, 'name' => 'Location avec chauffeur privé (journée)', 'max_passengers' => 4, 'price' => 60000],
            ['category_id' => 1, 'name' => 'Location de véhicule sans chauffeur', 'max_passengers' => 5, 'price' => 40000],
        ];

        foreach ($offers as $i => $offer) {
            TransportOffer::create([
                'provider_id' => $provider->id,
                'category_id' => $offer['category_id'],
                'name' => $offer['name'],
                'max_passengers' => $offer['max_passengers'],
                'price_xof' => $offer['price'],
                'is_available' => true,
                'sort_order' => $i,
            ]);
        }
    }

    /** Chambres : uniquement pour un prestataire hôtel déjà relié à une fiche hébergement (Accommodation). */
    private function seedRoomTypes(Provider $provider): void
    {
        $accommodation = $provider->accommodation;
        if (! $accommodation || ! empty($accommodation->room_types)) {
            return;
        }

        $accommodation->update([
            'room_types' => [
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => 'Chambre Deluxe Vue Lagune',
                    'max_adults' => 2,
                    'max_children' => 1,
                    'area_m2' => 32,
                    'price_xof' => 85000,
                    'price_eur' => null,
                    'amenities' => ['Climatisation', 'Wifi', 'Vue lagune'],
                    'photos' => [],
                ],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => 'Suite Junior',
                    'max_adults' => 2,
                    'max_children' => 2,
                    'area_m2' => 48,
                    'price_xof' => 140000,
                    'price_eur' => null,
                    'amenities' => ['Climatisation', 'Wifi', 'Salon séparé', 'Vue lagune'],
                    'photos' => [],
                ],
            ],
        ]);
    }
}

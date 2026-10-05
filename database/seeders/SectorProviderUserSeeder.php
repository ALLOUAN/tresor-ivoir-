<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderHour;
use App\Models\ProviderTag;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Un compte prestataire dédié par secteur d'activité (un seul login = un seul secteur),
 * pour pouvoir tester/administrer chaque tableau de bord métier isolément — contrairement
 * au compte partagé de ProviderSeeder qui gère plusieurs fiches tous secteurs confondus.
 */
class SectorProviderUserSeeder extends Seeder
{
    public function run(): void
    {
        $goldPlan = SubscriptionPlan::where('code', 'gold')->whereNull('provider_category_id')->first();

        $sectors = [
            [
                'category_slug' => 'hotels',
                'plan' => $goldPlan,
                'user' => [
                    'email' => 'hotelier@example.ci',
                    'password_hash' => 'Hotelier@2025!',
                    'first_name' => 'Yves',
                    'last_name' => 'Kacou',
                    'phone' => '+22507000010',
                ],
                'provider' => [
                    'name' => 'Résidence Le Baobab — Cocody',
                    'slug' => 'residence-le-baobab-cocody',
                    'description_fr' => 'Résidence hôtelière familiale au cœur de Cocody, 18 chambres climatisées et appartements meublés pour séjours courts et longs.',
                    'short_desc_fr' => 'Résidence hôtelière familiale, 18 chambres, Cocody.',
                    'city' => 'Abidjan',
                    'region' => 'District Autonome d\'Abidjan',
                    'address' => 'Rue du Canal, Cocody, Abidjan',
                    'latitude' => 5.35800000,
                    'longitude' => -3.98600000,
                    'phone' => '+22527210010',
                    'price_range' => 'mid',
                    'price_min' => 25000.00,
                    'price_max' => 60000.00,
                ],
                'tags' => ['wifi', 'climatisation', 'parking', 'cuisine-equipee'],
                'hours' => 'full',
            ],
            [
                'category_slug' => 'restaurants',
                'plan' => $goldPlan,
                'user' => [
                    'email' => 'restaurateur@example.ci',
                    'password_hash' => 'Resto@2025!',
                    'first_name' => 'Fatou',
                    'last_name' => 'Camara',
                    'phone' => '+22507000011',
                ],
                'provider' => [
                    'name' => 'Chez Fatou — Maquis du Plateau',
                    'slug' => 'chez-fatou-maquis-plateau',
                    'description_fr' => 'Maquis populaire du Plateau réputé pour son attiéké-poisson braisé et son ambiance conviviale en terrasse, midi et soir.',
                    'short_desc_fr' => 'Maquis convivial, attiéké-poisson braisé, terrasse.',
                    'city' => 'Abidjan',
                    'region' => 'District Autonome d\'Abidjan',
                    'address' => 'Rue du Commerce, Plateau, Abidjan',
                    'latitude' => 5.32000000,
                    'longitude' => -4.02100000,
                    'phone' => '+22507000012',
                    'price_range' => 'budget',
                    'price_min' => 2000.00,
                    'price_max' => 8000.00,
                ],
                'tags' => ['terrasse', 'climatisation'],
                'hours' => 'lunch-dinner',
            ],
            [
                'category_slug' => 'sites-touristiques',
                'plan' => $goldPlan,
                'user' => [
                    'email' => 'site@example.ci',
                    'password_hash' => 'Site@2025!',
                    'first_name' => 'Adjoua',
                    'last_name' => 'N\'Guessan',
                    'phone' => '+22507000013',
                ],
                'provider' => [
                    'name' => 'Cascades de Man',
                    'slug' => 'cascades-de-man',
                    'description_fr' => 'Site naturel emblématique de la région des 18 Montagnes, chutes d\'eau accessibles par un sentier forestier balisé, point de vue panoramique.',
                    'short_desc_fr' => 'Chutes d\'eau et sentier forestier, région des 18 Montagnes.',
                    'city' => 'Man',
                    'region' => 'Tonkpi',
                    'address' => 'Route des Cascades, Man',
                    'latitude' => 7.40500000,
                    'longitude' => -7.55300000,
                    'phone' => '+22507000014',
                    'price_range' => 'budget',
                    'price_min' => 1000.00,
                    'price_max' => 3000.00,
                ],
                'tags' => ['nature', 'eco-tourisme'],
                'hours' => 'day',
            ],
            [
                'category_slug' => 'agences-voyages',
                'plan' => $goldPlan,
                'user' => [
                    'email' => 'agence@example.ci',
                    'password_hash' => 'Agence@2025!',
                    'first_name' => 'Moussa',
                    'last_name' => 'Traoré',
                    'phone' => '+22507000015',
                ],
                'provider' => [
                    'name' => 'Ivoire Découverte Voyages',
                    'slug' => 'ivoire-decouverte-voyages',
                    'description_fr' => 'Agence réceptive spécialisée dans les circuits sur mesure en Côte d\'Ivoire : parcs nationaux, patrimoine culturel et séjours balnéaires.',
                    'short_desc_fr' => 'Agence réceptive, circuits sur mesure à travers la Côte d\'Ivoire.',
                    'city' => 'Abidjan',
                    'region' => 'District Autonome d\'Abidjan',
                    'address' => 'Avenue Chardy, Plateau, Abidjan',
                    'latitude' => 5.31900000,
                    'longitude' => -4.01900000,
                    'phone' => '+22507000016',
                    'price_range' => 'mid',
                    'price_min' => 30000.00,
                    'price_max' => 350000.00,
                ],
                'tags' => ['wifi', 'climatisation'],
                'hours' => 'office',
            ],
            [
                'category_slug' => 'loisirs-culture',
                'plan' => $goldPlan,
                'user' => [
                    'email' => 'loisirs@example.ci',
                    'password_hash' => 'Loisirs@2025!',
                    'first_name' => 'Aya',
                    'last_name' => 'Bamba',
                    'phone' => '+22507000017',
                ],
                'provider' => [
                    'name' => 'Espace Detente Riviera',
                    'slug' => 'espace-detente-riviera',
                    'description_fr' => 'Centre de loisirs et bien-être à Riviera : spa, ateliers artistiques et soirées spectacles pour tous les âges.',
                    'short_desc_fr' => 'Centre de loisirs et bien-être, spa et ateliers, Riviera.',
                    'city' => 'Abidjan',
                    'region' => 'District Autonome d\'Abidjan',
                    'address' => 'Boulevard Latrille, Riviera, Abidjan',
                    'latitude' => 5.36700000,
                    'longitude' => -3.96300000,
                    'phone' => '+22507000018',
                    'price_range' => 'mid',
                    'price_min' => 5000.00,
                    'price_max' => 40000.00,
                ],
                'tags' => ['wifi', 'climatisation', 'parking'],
                'hours' => 'full',
            ],
            [
                'category_slug' => 'transports',
                'plan' => $goldPlan,
                'user' => [
                    'email' => 'transport@example.ci',
                    'password_hash' => 'Transport@2025!',
                    'first_name' => 'Séry',
                    'last_name' => 'Diabaté',
                    'phone' => '+22507000019',
                ],
                'provider' => [
                    'name' => 'Ivoire Transfert VTC',
                    'slug' => 'ivoire-transfert-vtc',
                    'description_fr' => 'Service de transferts aéroport et location de véhicules avec chauffeur, flotte climatisée disponible 7j/7 sur tout Abidjan.',
                    'short_desc_fr' => 'Transferts aéroport et véhicules avec chauffeur, 7j/7.',
                    'city' => 'Abidjan',
                    'region' => 'District Autonome d\'Abidjan',
                    'address' => 'Boulevard VGE, Zone 4, Abidjan',
                    'latitude' => 5.29400000,
                    'longitude' => -3.98600000,
                    'phone' => '+22507000020',
                    'price_range' => 'mid',
                    'price_min' => 8000.00,
                    'price_max' => 75000.00,
                ],
                'tags' => ['climatisation', 'navette-aeroport'],
                'hours' => 'full',
            ],
            [
                'category_slug' => 'art-creations',
                'plan' => SubscriptionPlan::where('code', 'bronze')->whereHas('providerCategory', fn ($q) => $q->where('slug', 'art-creations'))->first() ?? $goldPlan,
                'user' => [
                    'email' => 'artiste@example.ci',
                    'password_hash' => 'Artiste@2025!',
                    'first_name' => 'Josiane',
                    'last_name' => 'Yao',
                    'phone' => '+22507000021',
                ],
                'provider' => [
                    'name' => 'Atelier Josiane Yao',
                    'slug' => 'atelier-josiane-yao',
                    'description_fr' => 'Atelier d\'artiste peintre et sculptrice ivoirienne, œuvres contemporaines inspirées des traditions Baoulé et Sénoufo.',
                    'short_desc_fr' => 'Peinture et sculpture contemporaine d\'inspiration Baoulé et Sénoufo.',
                    'city' => 'Abidjan',
                    'region' => 'District Autonome d\'Abidjan',
                    'address' => 'Rue des Artistes, Treichville, Abidjan',
                    'latitude' => 5.29500000,
                    'longitude' => -4.01500000,
                    'phone' => '+22507000022',
                    'price_range' => 'mid',
                    'price_min' => 15000.00,
                    'price_max' => 500000.00,
                ],
                'tags' => ['wifi'],
                'hours' => 'office',
            ],
        ];

        foreach ($sectors as $sector) {
            $category = ProviderCategory::where('slug', $sector['category_slug'])->whereNull('parent_id')->first();

            if (! $category) {
                continue;
            }

            $userData = $sector['user'];
            $userData['uuid'] = (string) Str::uuid();
            $userData['role'] = 'provider';
            $userData['is_active'] = true;
            $userData['is_verified'] = true;
            $userData['locale'] = 'fr';
            $userData['email_verified_at'] = now();

            $user = User::updateOrCreate(['email' => $userData['email']], $userData);

            $providerData = $sector['provider'];
            $providerData['uuid'] = (string) Str::uuid();
            $providerData['user_id'] = $user->id;
            $providerData['category_id'] = $category->id;
            $providerData['status'] = 'active';
            $providerData['is_verified'] = true;
            $providerData['is_featured'] = false;
            $providerData['published_at'] = now();

            $provider = Provider::withTrashed()->updateOrCreate(['slug' => $providerData['slug']], $providerData);
            if ($provider->trashed()) {
                $provider->restore();
            }

            if ($sector['plan'] && ! $provider->subscriptions()->where('status', 'active')->exists()) {
                Subscription::create([
                    'uuid' => (string) Str::uuid(),
                    'provider_id' => $provider->id,
                    'plan_id' => $sector['plan']->id,
                    'status' => 'active',
                    'billing_cycle' => 'yearly',
                    'starts_at' => now(),
                    'ends_at' => now()->addYear(),
                    'auto_renew' => true,
                ]);
            }

            $provider->tags()->delete();
            foreach ($sector['tags'] as $tagSlug) {
                ProviderTag::create([
                    'provider_id' => $provider->id,
                    'tag' => $tagSlug,
                ]);
            }

            $provider->hours()->delete();
            foreach ($this->hoursFor($sector['hours']) as $hour) {
                ProviderHour::create(array_merge(['provider_id' => $provider->id], $hour));
            }
        }
    }

    private function hoursFor(string $preset): array
    {
        return match ($preset) {
            'full' => $this->fullWeekHours('07:00', '22:00'),
            'day' => $this->fullWeekHours('08:00', '18:00'),
            'office' => [
                ['day_of_week' => 0, 'is_closed' => true],
                ['day_of_week' => 1, 'open_time' => '08:00', 'close_time' => '17:30', 'is_closed' => false],
                ['day_of_week' => 2, 'open_time' => '08:00', 'close_time' => '17:30', 'is_closed' => false],
                ['day_of_week' => 3, 'open_time' => '08:00', 'close_time' => '17:30', 'is_closed' => false],
                ['day_of_week' => 4, 'open_time' => '08:00', 'close_time' => '17:30', 'is_closed' => false],
                ['day_of_week' => 5, 'open_time' => '08:00', 'close_time' => '17:30', 'is_closed' => false],
                ['day_of_week' => 6, 'open_time' => '09:00', 'close_time' => '13:00', 'is_closed' => false],
            ],
            'lunch-dinner' => [
                ['day_of_week' => 0, 'open_time' => '12:00', 'close_time' => '22:00', 'is_closed' => false],
                ['day_of_week' => 1, 'open_time' => '12:00', 'close_time' => '22:00', 'is_closed' => false],
                ['day_of_week' => 2, 'open_time' => '12:00', 'close_time' => '22:00', 'is_closed' => false],
                ['day_of_week' => 3, 'open_time' => '12:00', 'close_time' => '22:00', 'is_closed' => false],
                ['day_of_week' => 4, 'open_time' => '12:00', 'close_time' => '23:00', 'is_closed' => false],
                ['day_of_week' => 5, 'open_time' => '12:00', 'close_time' => '23:00', 'is_closed' => false],
                ['day_of_week' => 6, 'open_time' => '12:00', 'close_time' => '23:00', 'is_closed' => false],
            ],
            default => $this->fullWeekHours('08:00', '18:00'),
        };
    }

    private function fullWeekHours(string $open, string $close): array
    {
        $hours = [];
        for ($day = 0; $day <= 6; $day++) {
            $hours[] = [
                'day_of_week' => $day,
                'open_time' => $open,
                'close_time' => $close,
                'is_closed' => false,
            ];
        }

        return $hours;
    }
}

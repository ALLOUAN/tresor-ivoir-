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

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providerUser = User::where('role', 'provider')->first();
        $goldPlan = SubscriptionPlan::where('code', 'gold')->first();
        $silverPlan = SubscriptionPlan::where('code', 'silver')->first();

        $hotelCategory = ProviderCategory::where('slug', 'hotels')->first();
        $restaurantCategory = ProviderCategory::where('slug', 'restaurants')->first();
        $siteCategory = ProviderCategory::where('slug', 'sites-touristiques')->first();

        $providers = [
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $providerUser->id,
                    'category_id' => $hotelCategory->id,
                    'name' => 'Sofitel Abidjan Hôtel Ivoire',
                    'slug' => 'sofitel-abidjan-hotel-ivoire',
                    'description_fr' => 'Icône hôtelière d\'Abidjan depuis 1963, le Sofitel Hôtel Ivoire offre 214 chambres et suites avec vue sur la lagune Ébrié. Un complexe unique réunissant hôtel, casino, patinoire et théâtre au cœur du plateau.',
                    'short_desc_fr' => 'L\'hôtel légendaire d\'Abidjan, 214 chambres vue lagune, casino et théâtre.',
                    'city' => 'Abidjan',
                    'region' => 'District Autonome d\'Abidjan',
                    'address' => 'Boulevard Hassan II, Cocody, Abidjan',
                    'latitude' => 5.36300000,
                    'longitude' => -3.99500000,
                    'phone' => '+22527200000',
                    'email' => 'contact@sofitel-abidjan.ci',
                    'website' => 'https://www.sofitel-abidjan.com',
                    'price_range' => 'luxury',
                    'price_min' => 120000.00,
                    'price_max' => 850000.00,
                    'status' => 'active',
                    'is_verified' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ],
                'plan' => $goldPlan,
                'tags' => ['wifi', 'piscine', 'parking', 'spa', 'restaurant', 'climatisation', 'salle-conference', 'navette-aeroport', 'bar', 'gym', 'vue-mer'],
                'hours' => $this->fullWeekHours('07:00', '23:00'),
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $providerUser->id,
                    'category_id' => $restaurantCategory->id,
                    'name' => 'La Table du Chef — Cocody',
                    'slug' => 'la-table-du-chef-cocody',
                    'description_fr' => 'Restaurant gastronomique ivoirien revisité par le chef Gilles Adiko. Une cuisine créative qui sublime les produits locaux dans un cadre tropical raffiné au cœur de Cocody.',
                    'short_desc_fr' => 'Gastronomie ivoirienne créative, produits locaux, cadre tropical.',
                    'city' => 'Abidjan',
                    'region' => 'District Autonome d\'Abidjan',
                    'address' => 'Rue des Jardins, Cocody Riviera, Abidjan',
                    'latitude' => 5.37500000,
                    'longitude' => -3.97200000,
                    'phone' => '+22507111222',
                    'price_range' => 'premium',
                    'price_min' => 15000.00,
                    'price_max' => 65000.00,
                    'status' => 'active',
                    'is_verified' => true,
                    'is_featured' => false,
                    'published_at' => now(),
                ],
                'plan' => $silverPlan,
                'tags' => ['wifi', 'climatisation', 'terrasse', 'halal'],
                'hours' => [
                    ['day_of_week' => 0, 'is_closed' => true],
                    ['day_of_week' => 1, 'open_time' => '12:00', 'close_time' => '22:30', 'is_closed' => false],
                    ['day_of_week' => 2, 'open_time' => '12:00', 'close_time' => '22:30', 'is_closed' => false],
                    ['day_of_week' => 3, 'open_time' => '12:00', 'close_time' => '22:30', 'is_closed' => false],
                    ['day_of_week' => 4, 'open_time' => '12:00', 'close_time' => '23:00', 'is_closed' => false],
                    ['day_of_week' => 5, 'open_time' => '12:00', 'close_time' => '23:00', 'is_closed' => false],
                    ['day_of_week' => 6, 'open_time' => '12:00', 'close_time' => '23:00', 'is_closed' => false],
                ],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $providerUser->id,
                    'category_id' => $siteCategory->id,
                    'name' => 'Parc National de Taï',
                    'slug' => 'parc-national-de-tai',
                    'description_fr' => 'Classé au Patrimoine mondial de l\'UNESCO depuis 1982, le Parc National de Taï est l\'une des dernières grandes forêts tropicales humides d\'Afrique de l\'Ouest. Refuge des chimpanzés qui utilisent des outils, il protège une biodiversité exceptionnelle sur 536 000 hectares.',
                    'short_desc_fr' => 'Forêt primaire UNESCO, chimpanzés sauvages, 536 000 hectares de biodiversité.',
                    'city' => 'Taï',
                    'region' => 'Sud-Ouest',
                    'address' => 'Zone forestière de Taï, Sud-Ouest',
                    'latitude' => 5.83330000,
                    'longitude' => -7.45000000,
                    'phone' => '+22507222333',
                    'price_range' => 'budget',
                    'price_min' => 5000.00,
                    'price_max' => 25000.00,
                    'status' => 'active',
                    'is_verified' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ],
                'plan' => $goldPlan,
                'tags' => ['nature', 'eco-tourisme', 'parking', 'accessible'],
                'hours' => $this->fullWeekHours('06:00', '18:00'),
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $providerUser->id,
                    'category_id' => $hotelCategory->id,
                    'name' => 'Hôtel Le Wafou — Grand-Bassam',
                    'slug' => 'hotel-le-wafou-grand-bassam',
                    'description_fr' => 'Niché entre océan et lagune à Grand-Bassam, premier site classé UNESCO de Côte d\'Ivoire, le Wafou propose 32 bungalows en bordure de plage dans un jardin tropical luxuriant.',
                    'short_desc_fr' => '32 bungalows bord de plage, Grand-Bassam UNESCO, jardin tropical.',
                    'city' => 'Grand-Bassam',
                    'region' => 'Sud-Comoé',
                    'address' => 'Quartier France, Grand-Bassam',
                    'latitude' => 5.19980000,
                    'longitude' => -3.73000000,
                    'phone' => '+22527300100',
                    'price_range' => 'mid',
                    'price_min' => 30000.00,
                    'price_max' => 95000.00,
                    'status' => 'active',
                    'is_verified' => true,
                    'is_featured' => false,
                    'published_at' => now(),
                ],
                'plan' => $silverPlan,
                'tags' => ['wifi', 'piscine', 'restaurant', 'bar', 'plage', 'vue-mer', 'parking', 'animaux'],
                'hours' => $this->fullWeekHours('00:00', '00:00'),
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $providerUser->id,
                    'category_id' => $siteCategory->id,
                    'name' => 'Basilique Notre-Dame de la Paix — Yamoussoukro',
                    'slug' => 'basilique-notre-dame-de-la-paix-yamoussoukro',
                    'description_fr' => 'La plus grande basilique chrétienne du monde selon le Livre Guinness des Records. Inaugurée en 1990 par le pape Jean-Paul II, elle peut accueillir 18 000 fidèles sous sa coupole de 158 mètres de hauteur.',
                    'short_desc_fr' => 'Plus grande basilique du monde, 158m de hauteur, classée UNESCO.',
                    'city' => 'Yamoussoukro',
                    'region' => 'Lacs',
                    'address' => 'Avenue Jean-Paul II, Yamoussoukro',
                    'latitude' => 6.80500000,
                    'longitude' => -5.27640000,
                    'phone' => '+22530640000',
                    'price_range' => 'budget',
                    'price_min' => 0.00,
                    'price_max' => 5000.00,
                    'status' => 'active',
                    'is_verified' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ],
                'plan' => $goldPlan,
                'tags' => ['parking', 'accessible', 'patrimoine'],
                'hours' => [
                    ['day_of_week' => 0, 'open_time' => '07:00', 'close_time' => '18:00', 'is_closed' => false],
                    ['day_of_week' => 1, 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day_of_week' => 2, 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day_of_week' => 3, 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day_of_week' => 4, 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day_of_week' => 5, 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day_of_week' => 6, 'open_time' => '08:00', 'close_time' => '18:00', 'is_closed' => false],
                ],
            ],
        ];

        foreach ($providers as $entry) {
            $provider = Provider::updateOrCreate(['slug' => $entry['data']['slug']], $entry['data']);

            // Subscription
            if ($entry['plan'] && ! $provider->subscriptions()->exists()) {
                Subscription::create([
                    'uuid' => (string) Str::uuid(),
                    'provider_id' => $provider->id,
                    'plan_id' => $entry['plan']->id,
                    'status' => 'active',
                    'billing_cycle' => 'yearly',
                    'starts_at' => now(),
                    'ends_at' => now()->addYear(),
                    'auto_renew' => true,
                ]);
            }

            // Tags
            $provider->tags()->delete();
            foreach ($entry['tags'] as $tagSlug) {
                ProviderTag::create([
                    'provider_id' => $provider->id,
                    'tag' => $tagSlug,
                ]);
            }

            // Hours
            $provider->hours()->delete();
            foreach ($entry['hours'] as $hour) {
                ProviderHour::create(array_merge(['provider_id' => $provider->id], $hour));
            }
        }
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

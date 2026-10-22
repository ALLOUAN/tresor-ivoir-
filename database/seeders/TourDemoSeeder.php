<?php

namespace Database\Seeders;

use App\Models\TourBooking;
use App\Models\TourDeparture;
use App\Models\TourPackage;
use App\Models\TouristSite;
use App\Models\TouristSiteMedia;
use App\Models\TravelAgency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Démonstration des circuits « marché » sur une agence EXISTANTE : programme jour par jour, inclus,
 * prise en charge, tarifs, acompte, minimum, barème d'annulation, départs programmés et réservations.
 *
 *   php artisan db:seed --class=TourDemoSeeder --force
 *   DEMO_AGENCY=mon-agence php artisan db:seed --class=TourDemoSeeder --force
 *
 * Seuls les champs vides des circuits sont remplis ; les départs sont ajoutés s'il n'y en a aucun à venir.
 * Les réservations de démonstration (e-mails @demo.tresorsivoire.ci, sans paiement réel) sont remplacées
 * à chaque passage.
 */
class TourDemoSeeder extends Seeder
{
    public const DEMO_DOMAIN = '@demo.tresorsivoire.ci';

    public function run(): void
    {
        $agency = TravelAgency::query()->where('is_active', true)->whereNotNull('provider_id')
            ->when(env('DEMO_AGENCY'), fn ($q, $slug) => $q->where('slug', $slug))->orderBy('id')->first();
        if (! $agency) {
            $this->command?->warn('Aucune agence active rattachée à un prestataire.');

            return;
        }
        $tours = TourPackage::where('provider_id', $agency->provider_id)->orderBy('id')->get();
        if ($tours->isEmpty()) {
            $this->command?->warn("L'agence {$agency->name} n'a aucun circuit.");

            return;
        }
        $this->command?->info("Agence de démonstration : {$agency->name}");

        $pickupsAbidjan = [
            ['label' => 'Plateau — Place de la République', 'time' => '07:00', 'note' => 'Devant la Pyramide'],
            ['label' => 'Cocody — Carrefour de la Vie', 'time' => '07:20', 'note' => 'Station Total'],
            ['label' => 'Marcory — Zone 4', 'time' => '07:40', 'note' => 'Devant le Cap Sud'],
            ['label' => 'Aéroport Félix-Houphouët-Boigny', 'time' => '08:00', 'note' => 'Hall des arrivées'],
        ];
        $policy = [['days' => 30, 'percent' => 90], ['days' => 14, 'percent' => 50], ['days' => 0, 'percent' => 0]];
        $treichville = TouristSiteMedia::whereIn('site_id', TouristSite::where('slug', 'marche-treichville')->pluck('id'))->pluck('url')->take(5)->all();

        $profiles = [
            'abidjan' => [
                'summary' => 'Une journée pour comprendre Abidjan : Plateau, marchés, lagune Ébrié et saveurs locales.',
                'itinerary' => [
                    ['title' => 'Abidjan, de la lagune au Plateau', 'description' => "Prise en charge à l'hôtel. Cathédrale Saint-Paul, quartier des affaires du Plateau et vue sur la lagune Ébrié.\nDéjeuner dans un maquis de Treichville, visite du grand marché puis traversée en pinasse jusqu'à Abobo-Doumé.\nRetour à l'hôtel en fin d'après-midi.", 'meals' => 'Déjeuner', 'accommodation' => ''],
                ],
                'included' => "Transport en minibus climatisé\nGuide local francophone\nDéjeuner en maquis\nTraversée en pinasse\nEau minérale",
                'not_included' => "Boissons au déjeuner\nPourboires\nAchats personnels",
                'pickup_points' => $pickupsAbidjan, 'child_price_xof' => 15000, 'child_age_max' => 11,
                'group_prices' => [['min' => 4, 'price_xof' => 22000], ['min' => 8, 'price_xof' => 19000]],
                'extras' => [['label' => 'Photographe professionnel (2 h)', 'price_xof' => 15000, 'per' => 'booking'], ['label' => 'Dîner-croisière sur la lagune', 'price_xof' => 12000, 'per' => 'person']],
                'min_participants' => 4, 'deposit_percent' => null, 'difficulty' => 'facile', 'languages' => ['Français', 'English'],
                'images' => $treichville, 'departures' => ['saturday', 6, 15],
            ],
            'bassam' => [
                'summary' => 'Patrimoine mondial de l\'UNESCO : la première capitale, ses maisons coloniales et sa plage.',
                'itinerary' => [
                    ['title' => 'Grand-Bassam, ville historique', 'description' => "Route vers Grand-Bassam (45 min). Quartier France, ancien palais du gouverneur devenu Musée national du costume.\nDéjeuner de poisson braisé en bord de mer, temps libre à la plage et au village artisanal.", 'meals' => 'Déjeuner', 'accommodation' => ''],
                ],
                'included' => "Transport aller-retour\nGuide\nEntrée au Musée national du costume\nDéjeuner",
                'not_included' => "Boissons\nTransat et parasol à la plage",
                'pickup_points' => array_slice($pickupsAbidjan, 0, 3), 'child_price_xof' => 10000, 'child_age_max' => 11,
                'extras' => [['label' => 'Atelier de tissage du pagne', 'price_xof' => 5000, 'per' => 'person']],
                'min_participants' => 6, 'deposit_percent' => null, 'difficulty' => 'facile', 'languages' => ['Français'],
                'departures' => ['sunday', 6, 20],
            ],
            'ouest' => [
                'summary' => '3 jours dans l\'Ouest montagneux : la Dent de Man, les cascades et les ponts de lianes.',
                'itinerary' => [
                    ['title' => 'Abidjan → Man', 'description' => "Départ matinal, route via Yamoussoukro (arrêt à la basilique Notre-Dame-de-la-Paix). Arrivée à Man en fin de journée.", 'meals' => 'Déjeuner, dîner', 'accommodation' => 'Hôtel 3★ à Man'],
                    ['title' => 'La Dent de Man et les cascades', 'description' => "Randonnée guidée vers la Dent de Man (881 m), panorama sur la région. Après-midi aux cascades de Man et rencontre avec les forgerons.", 'meals' => 'Petit-déjeuner, déjeuner, dîner', 'accommodation' => 'Hôtel 3★ à Man'],
                    ['title' => 'Ponts de lianes et retour', 'description' => "Matinée au pont de lianes de Lieupleu, tradition dan. Route du retour vers Abidjan, arrivée en soirée.", 'meals' => 'Petit-déjeuner, déjeuner', 'accommodation' => ''],
                ],
                'included' => "Transport en 4×4 climatisé\nHébergement 2 nuits en chambre double\nPension complète\nGuide et droits d'accès aux sites",
                'not_included' => "Boissons\nAssurance voyage\nPourboires",
                'pickup_points' => array_slice($pickupsAbidjan, 0, 2), 'child_price_xof' => 65000, 'child_age_max' => 11,
                'single_supplement_xof' => 20000,
                'extras' => [['label' => 'Nuit supplémentaire à Man', 'price_xof' => 30000, 'per' => 'person'], ['label' => 'Transfert depuis Yamoussoukro', 'price_xof' => 10000, 'per' => 'booking']],
                'min_participants' => 6, 'deposit_percent' => 30, 'balance_due_days' => 15, 'difficulty' => 'modere', 'languages' => ['Français', 'English'],
                'departures' => ['dates', [24, 38, 52], 12],
            ],
        ];

        foreach ($tours as $i => $tour) {
            $key = str_contains(mb_strtolower($tour->name), 'bassam') ? 'bassam' : (str_contains(mb_strtolower($tour->name), 'ouest') || $tour->duration_days > 1 ? 'ouest' : 'abidjan');
            $p = $profiles[$key];
            $fill = collect($p)->except('departures')->put('cancellation_policy', $policy)->put('instant_booking', true)
                ->filter(fn ($v, $k) => $k === 'instant_booking' ? ! $tour->exists : empty($tour->{$k}))->all();
            $tour->update($fill);

            if (! $tour->upcomingDepartures()->exists()) {
                [$mode, $arg, $seats] = $p['departures'];
                $dates = $mode === 'dates'
                    ? collect($arg)->map(fn ($d) => now('Africa/Abidjan')->addDays($d)->toDateString())
                    : collect(range(1, $arg))->map(fn ($w) => now('Africa/Abidjan')->next($mode === 'saturday' ? Carbon::SATURDAY : Carbon::SUNDAY)->addWeeks($w - 1)->toDateString());
                foreach ($dates as $d) {
                    TourDeparture::firstOrCreate(['tour_package_id' => $tour->id, 'departure_date' => $d], ['seats_total' => $seats, 'is_active' => true]);
                }
            }
            $this->command?->info("✓ {$tour->name} : ".count($fill).' réglage(s), '.$tour->upcomingDepartures()->count().' départ(s) à venir.');
        }

        // ── Réservations de démonstration (remplacées à chaque passage, sans écriture d'argent) ──
        TourBooking::whereIn('tour_package_id', $tours->pluck('id'))->where('email', 'like', '%'.self::DEMO_DOMAIN)->delete();
        $people = ['Aya Kouassi', 'Koffi Yao', 'Mariam Traoré', 'Jean-Marc Bamba', 'Awa Ouattara', 'Sékou Diabaté', 'Adjoua N\'Guessan'];
        $n = 0;
        foreach ($tours as $tour) {
            $tour->refresh();
            $deps = $tour->upcomingDepartures()->take(2)->get();
            foreach ($deps as $k => $dep) {
                // 1er départ : minimum atteint (garanti) ; 2e départ : quelques voyageurs seulement.
                $groups = $k === 0 ? [[3, 1], [2, 0]] : [[2, 0]];
                foreach ($groups as [$adults, $children]) {
                    if ($dep->seatsLeft() < $adults + $children) {
                        continue;
                    }
                    $q = $tour->quote($dep, $adults, $children, 0);
                    $deposit = $tour->depositAllowedFor($dep->departure_date);
                    $paid = $deposit && $n % 2 === 0 ? $q['deposit'] : $q['total'];
                    $name = $people[$n % count($people)];
                    TourBooking::create([
                        'tour_package_id' => $tour->id, 'tour_departure_id' => $dep->id, 'provider_id' => $tour->provider_id,
                        'tour_name' => $tour->name, 'departure_date' => $dep->departure_date,
                        'adults' => $q['adults'], 'children' => $q['children'], 'travelers_count' => $q['travelers'],
                        'pickup_point' => $tour->pickups()[0]['label'] ?? null, 'price_lines' => $q['lines'],
                        'amount_total_xof' => $q['total'], 'deposit_xof' => $deposit ? $q['deposit'] : 0,
                        'balance_due_on' => $deposit ? $dep->departure_date->copy()->subDays($tour->balance_due_days) : null,
                        'amount_paid_xof' => $paid, 'commission_percent' => 10,
                        'status' => $paid >= $q['total'] ? TourBooking::STATUS_PAID : TourBooking::STATUS_DEPOSIT_PAID,
                        'cancellation_policy' => $tour->cancellationRules(),
                        'full_name' => $name, 'email' => \Illuminate\Support\Str::slug($name).self::DEMO_DOMAIN, 'phone' => '+225 07 00 00 1'.($n % 10),
                        'travelers' => [['name' => $name, 'id_number' => 'CI00'.(4000 + $n), 'phone' => '']],
                    ]);
                    $n++;
                }
                if ($dep->minimum() && $dep->onlineTravelers() >= $dep->minimum()) {
                    $dep->update(['guaranteed_at' => now()]);
                }
            }
        }
        $this->command?->info("✓ {$n} réservation(s) de démonstration. Page : ".($tours->first()->fresh()->publicUrl() ?? '—'));
    }
}

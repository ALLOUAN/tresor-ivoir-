<?php

namespace Database\Seeders;

use App\Models\TransportBooking;
use App\Models\TransportCompany;
use App\Models\TransportDriver;
use App\Models\TransportOffer;
use App\Models\TransportTrip;
use App\Models\TransportVehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Démonstration Transports & Mobilité sur les compagnies EXISTANTES : offres converties (transfert à prix
 * fixe, chauffeur au km, location), une navette à sièges, flotte, chauffeurs et quelques réservations.
 *
 *   php artisan db:seed --class=TransportDemoSeeder --force
 *
 * Seuls les champs vides sont remplis ; les réservations de démonstration (e-mails @demo.tresorsivoire.ci,
 * sans mouvement d'argent) sont remplacées à chaque passage.
 */
class TransportDemoSeeder extends Seeder
{
    public const DEMO_DOMAIN = '@demo.tresorsivoire.ci';

    public function run(): void
    {
        $companies = TransportCompany::where('is_active', true)->whereNotNull('provider_id')->get();
        if ($companies->isEmpty()) {
            $this->command?->warn('Aucune compagnie de transport active rattachée à un prestataire.');

            return;
        }
        $zones = [
            ['label' => 'Port-Bouët / Vridi', 'price_xof' => 8000], ['label' => 'Marcory / Zone 4', 'price_xof' => 10000],
            ['label' => 'Plateau', 'price_xof' => 15000], ['label' => 'Cocody / Riviera', 'price_xof' => 18000],
            ['label' => 'Yopougon / Abobo', 'price_xof' => 20000], ['label' => 'Grand-Bassam', 'price_xof' => 25000],
            ['label' => 'Assinie-Mafia', 'price_xof' => 55000], ['label' => 'Yamoussoukro', 'price_xof' => 110000],
        ];
        $extras = [['label' => 'Siège enfant', 'price_xof' => 2000, 'per' => 'booking'], ['label' => 'Bagage supplémentaire', 'price_xof' => 1000, 'per' => 'passenger'], ['label' => 'Chauffeur bilingue (FR/EN)', 'price_xof' => 5000, 'per' => 'booking']];

        foreach ($companies as $i => $c) {
            $pid = $c->provider_id;
            foreach (TransportOffer::where('provider_id', $pid)->get() as $o) {
                $n = Str::lower($o->name);
                $fill = match (true) {
                    str_contains($n, 'transfert') => ['mode' => 'transfer', 'vehicle_category' => 'berline', 'origin_label' => 'Aéroport FHB (Port-Bouët)', 'zone_prices' => $zones,
                        'round_trip_discount_percent' => 10, 'free_waiting_minutes' => 60, 'trip_minutes' => 45, 'night_surcharge_percent' => 20, 'max_luggage' => 3, 'extras' => $extras],
                    str_contains($n, 'sans chauffeur') || (str_contains($n, 'location') && ! str_contains($n, 'avec chauffeur')) => ['mode' => 'rental', 'vehicle_category' => '4x4', 'weekly_daily_xof' => 32000, 'deposit_xof' => 250000,
                        'included_km' => 200, 'extra_km_xof' => 150, 'min_driver_age' => 23, 'extras' => [['label' => 'GPS', 'price_xof' => 2000, 'per' => 'day'], ['label' => 'Siège enfant', 'price_xof' => 1500, 'per' => 'day'], ['label' => 'Conducteur additionnel', 'price_xof' => 5000, 'per' => 'booking']]],
                    str_contains($n, 'chauffeur') => ['mode' => 'chauffeur', 'vehicle_category' => 'suv', 'base_fare_xof' => 2000, 'per_km_xof' => 350, 'per_hour_xof' => 3000,
                        'minimum_xof' => 8000, 'half_day_xof' => 35000, 'full_day_xof' => 60000, 'included_km' => 150, 'night_surcharge_percent' => 20, 'extras' => $extras],
                    str_contains($n, 'location') => ['mode' => 'rental', 'vehicle_category' => '4x4', 'weekly_daily_xof' => 32000, 'deposit_xof' => 250000,
                        'included_km' => 200, 'extra_km_xof' => 150, 'min_driver_age' => 23, 'extras' => [['label' => 'GPS', 'price_xof' => 2000, 'per' => 'day'], ['label' => 'Siège enfant', 'price_xof' => 1500, 'per' => 'day'], ['label' => 'Conducteur additionnel', 'price_xof' => 5000, 'per' => 'booking']]],
                    default => [],
                };
                $fill = collect($fill)->filter(fn ($v, $k) => $o->{$k} === null || $o->{$k} === [])->all();
                if ($fill) {
                    $o->update($fill + ['free_cancellation_hours' => $o->free_cancellation_hours ?? 24, 'instant_booking' => true]);
                }
            }
            if ($i === 0) {
                $line = TransportOffer::firstOrCreate(['provider_id' => $pid, 'name' => 'Navette Abidjan → Assinie'], [
                    'category_id' => TransportOffer::where('provider_id', $pid)->value('category_id'), 'description' => 'Navette climatisée du week-end vers les plages d\'Assinie, départ de la Riviera.',
                    'mode' => 'line', 'route_from' => 'Abidjan (Riviera 2)', 'route_to' => 'Assinie-Mafia', 'trip_minutes' => 120, 'price_xof' => 7000,
                    'max_passengers' => 18, 'free_cancellation_hours' => 24, 'instant_booking' => true, 'is_available' => true,
                    'extras' => [['label' => 'Bagage supplémentaire', 'price_xof' => 1000, 'per' => 'passenger']],
                ]);
                $bus = TransportVehicle::firstOrCreate(['provider_id' => $pid, 'name' => 'Toyota Coaster'], ['category' => 'minibus', 'plate' => '4512 FG 01', 'seats' => 18, 'luggage' => 20]);
                foreach (range(1, 21) as $d) {
                    $day = now('Africa/Abidjan')->addDays($d);
                    if (! in_array($day->isoWeekday(), [5, 6, 7], true)) {
                        continue;
                    }
                    foreach (['07:30', '16:00'] as $t) {
                        TransportTrip::firstOrCreate(['transport_offer_id' => $line->id, 'departs_at' => $day->copy()->setTimeFromTimeString($t)->utc()],
                            ['seats_total' => 18, 'seats_per_row' => 3, 'transport_vehicle_id' => $bus->id, 'is_active' => true]);
                    }
                }
            }
            foreach ([['Toyota Corolla', 'berline', 'AA-215-CI', 4, 3], ['Hyundai Elantra', 'berline', 'AB-882-CI', 4, 3], ['Toyota RAV4', 'suv', 'AC-104-CI', 5, 4], ['Toyota Land Cruiser Prado', '4x4', 'AD-339-CI', 7, 5]] as [$name, $cat, $plate, $seats, $lug]) {
                TransportVehicle::firstOrCreate(['provider_id' => $pid, 'name' => $name], ['category' => $cat, 'plate' => $plate.($i ? '-'.$i : ''), 'seats' => $seats, 'luggage' => $lug]);
            }
            foreach ([['Moussa Coulibaly', '+225 07 11 22 33 44', 'Français, Dioula'], ['Jean-Paul Kouadio', '+225 05 66 77 88 99', 'Français, English']] as [$name, $phone, $lang]) {
                TransportDriver::firstOrCreate(['provider_id' => $pid, 'name' => $name], ['phone' => $phone, 'languages' => $lang]);
            }

            // Réservations de démonstration
            TransportBooking::where('provider_id', $pid)->where('email', 'like', '%'.self::DEMO_DOMAIN)->delete();
            $transfer = TransportOffer::where('provider_id', $pid)->where('mode', 'transfer')->first();
            $driver = TransportDriver::where('provider_id', $pid)->first();
            $car = TransportVehicle::where('provider_id', $pid)->where('category', 'berline')->first();
            if ($transfer) {
                foreach ([[1, '06:40', 'AF702', 'Claire Martin', 3, 'Cocody / Riviera'], [1, '21:15', 'ET513', 'Kwame Mensah', 2, 'Plateau'], [2, '13:05', 'HF502', 'Aya Kouassi', 6, 'Assinie-Mafia']] as $k => [$d, $t, $flight, $name, $zone, $label]) {
                    $start = now('Africa/Abidjan')->addDays($d)->setTimeFromTimeString($t);
                    TransportBooking::create([
                        'transport_offer_id' => $transfer->id, 'provider_id' => $pid, 'mode' => 'transfer', 'offer_name' => $transfer->name,
                        'transport_vehicle_id' => $k === 0 ? $car?->id : null, 'transport_driver_id' => $k === 0 ? $driver?->id : null, 'trip_status' => $k === 0 ? 'assigned' : null,
                        'pickup_label' => $transfer->origin_label, 'dropoff_label' => $label, 'starts_at' => $start->copy()->utc(), 'ends_at' => $start->copy()->addMinutes(105)->utc(),
                        'flight_number' => $flight, 'passengers' => 2, 'luggage' => 2,
                        'price_lines' => [['label' => 'Transfert '.$transfer->origin_label.' → '.$label, 'amount_xof' => $zones[$zone]['price_xof']]],
                        'amount_total_xof' => $zones[$zone]['price_xof'], 'commission_percent' => 10, 'status' => TransportBooking::STATUS_PAID, 'gateway' => 'demo', 'paid_at' => now(),
                        'cancellable_until' => $start->copy()->subDay()->utc(), 'full_name' => $name, 'email' => Str::slug($name).self::DEMO_DOMAIN, 'phone' => '+225 07 00 00 3'.$k,
                    ]);
                }
            }
            $this->command?->info("✓ {$c->name} : offres converties, flotte et chauffeurs.");
        }
        $first = TransportOffer::where('mode', 'line')->first();
        if ($first) {
            $this->command?->line('   Navette : '.route('transport-offer.show', $first->slug));
        }
    }
}

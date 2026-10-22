<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\LeisurePackage;
use App\Models\LeisurePass;
use App\Models\LeisureResource;
use App\Models\LeisureVenue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Démonstration Loisirs & Culture sur un lieu EXISTANT : créneaux, tarifs, options, conditions, décharge,
 * ressources, formules, cartes, et quelques réservations (créneau complet → liste d'attente possible).
 *
 *   php artisan db:seed --class=LeisureDemoSeeder --force
 *   DEMO_LEISURE=mon-lieu php artisan db:seed --class=LeisureDemoSeeder --force
 *
 * Seuls les champs vides sont remplis ; les réservations de démonstration (e-mails @demo.tresorsivoire.ci,
 * sans mouvement d'argent) sont remplacées à chaque passage.
 */
class LeisureDemoSeeder extends Seeder
{
    public const DEMO_DOMAIN = '@demo.tresorsivoire.ci';

    public function run(): void
    {
        $venue = LeisureVenue::query()->where('is_active', true)->whereNotNull('provider_id')
            ->when(env('DEMO_LEISURE'), fn ($q, $s) => $q->where('slug', $s))->orderBy('id')->first();
        if (! $venue) {
            $this->command?->warn('Aucun lieu de loisirs actif rattaché à un prestataire.');

            return;
        }
        $pid = $venue->provider_id;
        $this->command?->info("Lieu de démonstration : {$venue->name}");

        $studio = LeisureResource::firstOrCreate(['provider_id' => $pid, 'name' => 'Studio de danse'], ['units' => 1]);
        $cabins = LeisureResource::firstOrCreate(['provider_id' => $pid, 'name' => 'Cabines de soin'], ['units' => 3]);
        $hall = LeisureResource::firstOrCreate(['provider_id' => $pid, 'name' => 'Salle des fêtes'], ['units' => 1]);

        $profiles = [
            'spa' => [
                'slot_times' => ['10:00', '11:30', '14:00', '15:30', '17:00'], 'slot_capacity' => 3,
                'price_tiers' => [['label' => 'Adulte', 'price_xof' => 12000], ['label' => 'Duo (par personne)', 'price_xof' => 10000]],
                'addons' => [['label' => 'Gommage au karité', 'price_xof' => 5000, 'per' => 'person'], ['label' => 'Thé et jus de bissap', 'price_xof' => 1500, 'per' => 'person']],
                'offpeak_until' => '12:00', 'offpeak_discount_percent' => 15, 'weekend_adjust_percent' => 10, 'free_cancellation_hours' => 24,
                'min_age' => 16, 'requirements' => "Déconseillé aux femmes enceintes\nSignalez toute allergie", 'to_bring' => 'Maillot de bain',
                'resource' => [$cabins, 1, true],
            ],
            'spectacle' => [
                'slot_times' => ['20:00'], 'slot_capacity' => 50, 'weekdays' => [5, 6],
                'price_tiers' => [['label' => 'Adulte', 'price_xof' => 5000], ['label' => 'Enfant (- 12 ans)', 'price_xof' => 2500], ['label' => 'Étudiant', 'price_xof' => 3500]],
                'addons' => [['label' => 'Place en carré VIP', 'price_xof' => 5000, 'per' => 'person'], ['label' => 'Cocktail de bienvenue', 'price_xof' => 2000, 'per' => 'person']],
                'free_cancellation_hours' => 48, 'to_bring' => 'Billet sur téléphone',
            ],
            'danse' => [
                'slot_times' => ['10:00', '16:00', '18:30'], 'slot_capacity' => 15, 'weekdays' => [2, 4, 6],
                'price_tiers' => [['label' => 'Adulte', 'price_xof' => 4000], ['label' => 'Enfant (8-14 ans)', 'price_xof' => 2500]],
                'addons' => [['label' => 'Location de pagne de scène', 'price_xof' => 1000, 'per' => 'person']],
                'weekend_adjust_percent' => 10, 'free_cancellation_hours' => 12, 'min_age' => 8, 'level' => 'debutant',
                'requirements' => 'Aucune expérience nécessaire', 'to_bring' => "Tenue confortable\nBouteille d'eau",
                'requires_waiver' => true, 'waiver_text' => "Je déclare être apte à la pratique d'une activité physique et dégage l'organisateur de toute responsabilité en cas d'accident résultant du non-respect des consignes.",
                'resource' => [$studio, 1, false],
            ],
        ];

        $activities = Activity::where('provider_id', $pid)->orderBy('id')->get();
        foreach ($activities as $a) {
            $n = Str::lower($a->name);
            $key = str_contains($n, 'spa') || str_contains($n, 'relax') ? 'spa' : (str_contains($n, 'spectacle') || str_contains($n, 'soir') ? 'spectacle' : (str_contains($n, 'danse') ? 'danse' : null));
            if (! $key) {
                continue;
            }
            $p = $profiles[$key];
            $fill = collect($p)->except('resource')->filter(fn ($v, $k) => $a->{$k} === null || $a->{$k} === [] || ($k === 'requires_waiver' && ! $a->requires_waiver))->all();
            $a->update($fill + ['instant_booking' => true]);
            if (isset($p['resource']) && ! $a->resources()->exists()) {
                [$r, $units, $perP] = $p['resource'];
                $a->resources()->attach($r->id, ['units' => $units, 'per_participant' => $perP]);
            }
            $this->command?->info("✓ {$a->name} : ".count($fill).' réglage(s).');
        }

        LeisurePackage::firstOrCreate(['provider_id' => $pid, 'name' => 'Anniversaire Danse & Fun'], [
            'kind' => 'birthday', 'description' => 'Deux heures de fête : initiation à la danse traditionnelle, jeux et goûter, dans une salle rien que pour vous.',
            'includes' => "Salle privatisée 2 h\nAnimateur et initiation à la danse\nGoûter, jus de bissap et gnamakoudji\nGâteau d'anniversaire\nCadeau pour l'enfant fêté",
            'weekday_price_xof' => 75000, 'weekend_price_xof' => 95000, 'included_participants' => 10, 'extra_participant_xof' => 6000,
            'min_participants' => 6, 'max_participants' => 25, 'duration_minutes' => 120, 'deposit_xof' => 30000,
            'slot_times' => ['10:00', '14:00', '16:30'], 'leisure_resource_id' => $hall->id, 'free_cancellation_days' => 7,
        ]);
        LeisurePackage::firstOrCreate(['provider_id' => $pid, 'name' => 'Team building bien-être'], [
            'kind' => 'corporate', 'description' => 'Une demi-journée pour souder vos équipes : atelier de danse, massage express et cocktail.',
            'includes' => "Atelier de danse 1 h\nMassage express 20 min par personne\nCocktail et bouchées\nSalle privatisée",
            'weekday_price_xof' => 250000, 'included_participants' => 15, 'extra_participant_xof' => 15000,
            'min_participants' => 8, 'max_participants' => 40, 'duration_minutes' => 240, 'deposit_xof' => 100000,
            'slot_times' => ['09:00', '14:00'], 'leisure_resource_id' => $hall->id, 'free_cancellation_days' => 14,
        ]);
        $danse = $activities->first(fn ($a) => str_contains(Str::lower($a->name), 'danse'));
        LeisurePass::firstOrCreate(['provider_id' => $pid, 'name' => 'Carte 10 cours de danse'], [
            'description' => 'Économisez 25 % sur vos cours.', 'sessions' => 10, 'validity_days' => 120, 'price_xof' => 30000,
            'activity_ids' => $danse ? [$danse->id] : null,
        ]);
        LeisurePass::firstOrCreate(['provider_id' => $pid, 'name' => 'Abonnement bien-être mensuel'], [
            'description' => 'Accès illimité au spa et aux cours pendant 30 jours.', 'sessions' => null, 'validity_days' => 30, 'price_xof' => 60000,
        ]);

        // ── Réservations de démonstration : le premier créneau de danse est complet ──
        ActivityBooking::where('provider_id', $pid)->where('email', 'like', '%'.self::DEMO_DOMAIN)->delete();
        $people = ['Aya Kouassi', 'Koffi Yao', 'Mariam Traoré', 'Awa Ouattara', 'Sékou Diabaté'];
        $count = 0;
        foreach ($activities->fresh() as $a) {
            $slots = $a->slots();
            if (! $slots) {
                continue;
            }
            $day = collect(range(1, 14))->map(fn ($i) => now('Africa/Abidjan')->addDays($i)->startOfDay())->first(fn ($d) => $a->isOpenOn($d));
            $plan = $a->id === $danse?->id ? [[$slots[0], $a->slot_capacity ?: 15]] : [[$slots[0], 2], [$slots[min(1, count($slots) - 1)], 1]];
            foreach ($plan as $k => [$time, $n]) {
                $q = $a->quote([$n], [], $day, $time);
                $name = $people[$count % count($people)];
                ActivityBooking::create([
                    'activity_id' => $a->id, 'provider_id' => $pid, 'item_name' => $a->name, 'booking_date' => $day->toDateString(),
                    'booking_time' => $time, 'duration_minutes' => $a->duration_minutes ?: 60, 'participants' => $n,
                    'tier_breakdown' => $q['breakdown'], 'amount_total_xof' => $q['total'], 'amount_paid_xof' => $q['total'], 'commission_percent' => 10,
                    'status' => ActivityBooking::STATUS_PAID, 'gateway' => 'demo', 'paid_at' => now(),
                    'cancellable_until' => $a->free_cancellation_hours !== null ? Carbon::parse($day->toDateString().' '.$time, 'Africa/Abidjan')->subHours($a->free_cancellation_hours)->utc() : null,
                    'waiver' => $a->requires_waiver ? ['names' => array_fill(0, $n, $name), 'accepted_at' => now()->toIso8601String()] : null,
                    'full_name' => $name, 'email' => Str::slug($name).self::DEMO_DOMAIN, 'phone' => '+225 07 00 00 2'.($count % 10),
                ]);
                $count++;
            }
        }
        $this->command?->info("✓ 3 ressources, 2 formules, 2 cartes, {$count} réservation(s) de démonstration.");
        if ($danse) {
            $this->command?->line('   Page : '.route('activity.show', $danse->slug).' (premier créneau complet → liste d\'attente).');
        }
    }
}

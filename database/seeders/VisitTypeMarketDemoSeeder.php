<?php

namespace Database\Seeders;

use App\Models\TouristVisitType;
use Illuminate\Database\Seeder;

/**
 * Démonstration des types de visite « marché » sur les types EXISTANTS : créneaux et places, grille de
 * tarifs, annulation gratuite 24 h, paiement sur place, langues, fiche pratique, week-end / heures
 * creuses et options. Seuls les champs encore vides sont remplis : rien de ce que le site a saisi n'est écrasé.
 *
 *   php artisan db:seed --class=VisitTypeMarketDemoSeeder --force
 */
class VisitTypeMarketDemoSeeder extends Seeder
{
    public function run(): void
    {
        $types = TouristVisitType::query()->active()->get();
        if ($types->isEmpty()) {
            $this->command?->warn('Aucun type de visite actif : créez-en depuis le back-office d\'un site.');

            return;
        }

        foreach ($types as $type) {
            $base = max(1000, (int) $type->price_xof);
            $round = fn (int $p) => (int) (round($base * $p / 10000) * 100);
            $guided = $type->includes_guide || str_contains(mb_strtolower($type->name), 'guid');

            $defaults = [
                'price_tiers' => [
                    ['label' => 'Adulte', 'price_xof' => $base],
                    ['label' => 'Enfant (4-12 ans)', 'price_xof' => $round(50)],
                    ['label' => 'Étudiant', 'price_xof' => $round(60)],
                    ['label' => 'Résident ivoirien', 'price_xof' => $round(70)],
                    ['label' => 'Moins de 4 ans', 'price_xof' => 0],
                ],
                'slot_times' => $guided ? ['09:00', '10:30', '14:00', '15:30'] : ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
                'slot_capacity' => $guided ? 15 : 30,
                'free_cancellation_hours' => 24,
                'allow_pay_on_site' => true,
                'languages' => $guided ? ['Français', 'English', 'Baoulé'] : ['Français'],
                'included' => $guided ? "Droit d'entrée\nGuide local certifié\nEau minérale" : "Droit d'entrée\nPlan du site",
                'not_included' => "Transport\nPourboires",
                'meeting_point' => 'Accueil du site, devant la billetterie',
                'accessibility' => 'Parcours principal accessible ; certaines zones en terrain irrégulier',
                'to_bring' => "Chaussures confortables\nChapeau et crème solaire\nPièce d'identité (tarif résident)",
                'weekend_adjust_percent' => 10,
                'offpeak_until' => '10:00',
                'offpeak_discount_percent' => 15,
                'addons' => [
                    ['label' => 'Audioguide (FR / EN)', 'price_xof' => 1000, 'per' => 'person'],
                    ['label' => 'Pack photos souvenir', 'price_xof' => 5000, 'per' => 'booking'],
                    ['label' => 'Déjeuner local (attiéké poisson)', 'price_xof' => 3500, 'per' => 'person'],
                ],
            ];

            $fill = collect($defaults)->filter(fn ($v, $k) => $type->{$k} === null || $type->{$k} === [] || ($k === 'allow_pay_on_site' && ! $type->{$k}))->all();
            if ($fill) {
                $type->update($fill);
            }
            $this->command?->info("✓ {$type->name} ({$type->experience?->name}) : ".count($fill).' réglage(s) ajouté(s).');
        }
    }
}

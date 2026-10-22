<?php

namespace Database\Seeders;

use App\Models\ArtistProfile;
use App\Models\Artwork;
use App\Models\Provider;
use Illuminate\Database\Seeder;

/**
 * Démonstration Art & Créations sur l'artiste EXISTANT : page artiste (bio, démarche, expositions),
 * livraison et assurance, dimensions exactes, offres, paiement en 3 fois, éditions limitées.
 *
 *   php artisan db:seed --class=ArtDemoSeeder --force
 *
 * Seuls les champs vides sont remplis ; aucune œuvre ni commande fictive n'est créée.
 */
class ArtDemoSeeder extends Seeder
{
    public function run(): void
    {
        $providers = Provider::whereIn('id', Artwork::where('status', Artwork::STATUS_PUBLISHED)->distinct()->pluck('provider_id'))->get();
        if ($providers->isEmpty()) {
            $this->command?->warn('Aucun artiste avec une œuvre publiée.');

            return;
        }
        foreach ($providers as $p) {
            $profile = ArtistProfile::forProvider($p);
            $fill = collect([
                'tagline' => 'Peinture contemporaine — la lumière d\'Abidjan',
                'bio' => $profile->bio ?: 'Artiste plasticienne installée à Abidjan, diplômée de l\'INSAAC. Son travail explore la ville, la lagune Ébrié et les couleurs du quotidien ivoirien, entre acrylique, pigments naturels et collages de pagne.',
                'statement' => 'Je peins la ville au moment où elle s\'éveille : la lumière rasante sur la lagune, les marchés qui s\'animent, les silhouettes pressées. Chaque toile est un instant suspendu de la vie abidjanaise.',
                'city' => 'Abidjan',
                'education' => "INSAAC — École des Beaux-Arts d'Abidjan\nRésidence d'artiste, Grand-Bassam",
                'exhibitions' => "2025 — Abidjan Art Week, exposition collective\n2024 — « Lumières lagunaires », Rotonde des Arts, Plateau\n2023 — Nuit des galeries, Cocody",
                'pickup_address' => 'Atelier, Cocody Angré 8e tranche',
                'abidjan_delivery_xof' => 5000, 'country_delivery_xof' => 15000, 'large_supplement_xof' => 10000,
                'commission_from_xof' => 150000,
            ])->filter(fn ($v, $k) => $profile->{$k} === null || $profile->{$k} === '')->all();
            $profile->update($fill);

            foreach (Artwork::where('provider_id', $p->id)->where('status', Artwork::STATUS_PUBLISHED)->get() as $a) {
                if (! $a->width_cm && preg_match('/(\d+)\s*[x×]\s*(\d+)/i', (string) $a->dimensions, $m)) {
                    $a->update(['width_cm' => (int) $m[1], 'height_cm' => (int) $m[2]]);
                }
                if (! $a->accepts_offers && ! $a->layaway_allowed) {
                    $a->update(['accepts_offers' => true, 'min_offer_xof' => (int) round($a->price_xof * 0.8 / 1000) * 1000, 'layaway_allowed' => true]);
                }
                if (! $a->editions()->exists()) {
                    $a->editions()->createMany([
                        ['label' => 'Tirage d\'art A3', 'width_cm' => 30, 'height_cm' => 42, 'price_xof' => 35000, 'edition_size' => 25],
                        ['label' => 'Tirage d\'art A2', 'width_cm' => 42, 'height_cm' => 59, 'price_xof' => 55000, 'edition_size' => 15],
                    ]);
                }
                $this->command?->info("✓ {$a->title} : dimensions, offres, paiement en 3 fois, 2 éditions limitées.");
            }
            $this->command?->line('   Page artiste : '.route('artist.show', $profile->slug));
        }
    }
}

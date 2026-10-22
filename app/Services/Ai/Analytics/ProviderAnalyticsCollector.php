<?php

namespace App\Services\Ai\Analytics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alimente la table provider_analytics (une ligne par prestataire et par jour) que la page
 * « Statistiques » lit déjà. Jusqu'ici rien n'y écrivait les vues : sans historique, ni les
 * statistiques avancées ni les prévisions ne peuvent exister.
 *
 * Une « vue » est une visite de la fiche, comptée une fois par visiteur et par demi-heure
 * (les robots et le signal « Ne pas suivre » sont exclus en amont, par le suivi des consultations).
 */
class ProviderAnalyticsCollector
{
    /** Tables des fiches détaillées qui appartiennent à un prestataire (clé = type de fiche du suivi). */
    private const OWNED = [
        'accommodation' => 'accommodations', 'restaurant' => 'restaurants', 'leisure' => 'leisure_venues',
        'experience' => 'tourist_experiences', 'agency' => 'travel_agencies', 'transport' => 'transport_companies',
    ];

    public function providerIdFor(string $type, int $id): ?int
    {
        if ($type === 'provider') {
            return $id;
        }
        if (! isset(self::OWNED[$type])) {
            return null;
        }
        $pid = DB::table(self::OWNED[$type])->where('id', $id)->value('provider_id');

        return $pid ? (int) $pid : null;
    }

    public function recordView(int $providerId): void
    {
        if (! config('ai.analytics.collect', true) || ! \App\Support\SchemaCache::hasTable('provider_analytics')) {
            return;
        }
        $today = now()->toDateString();
        $updated = DB::table('provider_analytics')->where(['provider_id' => $providerId, 'date' => $today, 'is_simulated' => false])->increment('views_count');
        if (! $updated) {
            DB::table('provider_analytics')->insert([
                'provider_id' => $providerId, 'date' => $today, 'views_count' => 1, 'clicks_phone' => 0, 'clicks_website' => 0,
                'clicks_direction' => 0, 'new_reviews' => 0, 'search_appearances' => 0, 'is_simulated' => false, 'created_at' => now(),
            ]);
        }
    }
}

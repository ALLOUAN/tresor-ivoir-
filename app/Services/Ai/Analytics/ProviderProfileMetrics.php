<?php

namespace App\Services\Ai\Analytics;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Indicateurs mesurables d'une fiche prestataire (communs à la comparaison et aux suggestions). */
class ProviderProfileMetrics
{
    /** Éléments de complétude : [clé, libellé, poids]. La somme des poids vaut 100. */
    public const CHECKS = [
        ['description', 'Description détaillée (150 caractères ou plus)', 18],
        ['short_desc', 'Description courte', 6],
        ['photos', 'Au moins 3 photos', 16],
        ['cover', 'Image de couverture', 10],
        ['logo', 'Logo', 6],
        ['phone', 'Téléphone', 8],
        ['address', 'Adresse localisée sur la carte', 10],
        ['website', 'Site web ou réseau social', 6],
        ['price', 'Gamme de prix', 6],
        ['hours', 'Horaires d\'ouverture', 8],
        ['reviews', 'Au moins un avis de visiteur', 6],
    ];

    /** @return array<string,mixed> */
    public function for(object $p): array
    {
        $descLen = mb_strlen(trim(strip_tags((string) $p->description_fr)));
        $photos = \App\Support\SchemaCache::hasTable('media') ? DB::table('media')->where('mediable_type', Provider::class)->where('mediable_id', $p->id)->where('type', 'image')->count() : 0;
        $hours = \App\Support\SchemaCache::hasTable('provider_hours') ? DB::table('provider_hours')->where('provider_id', $p->id)->where('is_closed', false)->count() : 0;
        $since30 = now()->subDays(30)->toDateString();
        $an = \App\Support\SchemaCache::hasTable('provider_analytics')
            ? DB::table('provider_analytics')->where('provider_id', $p->id)->where('date', '>=', $since30)->selectRaw('COALESCE(SUM(views_count),0) v, COALESCE(SUM(clicks_direction),0) d, COUNT(*) days')->first()
            : null;
        $reviews90 = \App\Support\SchemaCache::hasTable('reviews') ? DB::table('reviews')->where('provider_id', $p->id)->where('created_at', '>=', now()->subDays(90))->count() : 0;

        $has = [
            'description' => $descLen >= 150, 'short_desc' => trim((string) $p->short_desc_fr) !== '', 'photos' => $photos >= 3,
            'cover' => ! empty($p->cover_url), 'logo' => ! empty($p->logo_url), 'phone' => ! empty($p->phone),
            'address' => ! empty($p->address) && $p->latitude !== null, 'website' => ! empty($p->website) || ! empty($p->facebook_url) || ! empty($p->instagram_url),
            'price' => ! empty($p->price_range) || ! empty($p->price_min), 'hours' => $hours > 0, 'reviews' => (int) $p->rating_count > 0,
        ];
        $score = 0;
        foreach (self::CHECKS as [$k, , $w]) {
            $score += $has[$k] ? $w : 0;
        }

        return [
            'description_chars' => $descLen, 'photos' => $photos, 'hours_days' => $hours,
            'views_30d' => (int) ($an->v ?? 0), 'directions_30d' => (int) ($an->d ?? 0), 'analytics_days' => (int) ($an->days ?? 0),
            'rating' => (float) $p->rating_avg, 'rating_count' => (int) $p->rating_count, 'reviews_90d' => $reviews90,
            'has' => $has, 'completeness' => $score,
        ];
    }
}

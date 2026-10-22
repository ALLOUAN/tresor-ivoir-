<?php

namespace App\Services\Ai\Analytics;

/**
 * Suggestions d'amélioration d'une fiche, classées par priorité. Chaque suggestion repose sur un
 * constat chiffré (ce qui manque, l'écart à la médiane de la catégorie, la limite du forfait).
 * Aucun gain chiffré n'est promis : l'impact est une priorité qualitative (élevé, moyen, faible).
 */
class ImprovementAdvisor
{
    /**
     * @param  array<string,mixed>  $m          indicateurs de la fiche (ProviderProfileMetrics)
     * @param  array<string,mixed>  $benchmark  résultat de CategoryBenchmark::compare
     * @param  array<string,mixed>  $forecast   résultat de ProviderForecaster::forecast
     * @param  array<string,mixed>  $plan       résultat de PlanAccess::for
     * @return array<int,array{impact:string,title:string,evidence:string,action:string,link:?string}>
     */
    public function advise(object $p, array $m, array $benchmark, array $forecast, array $plan): array
    {
        $out = [];
        $add = function (string $impact, string $title, string $evidence, string $action, ?string $link = null) use (&$out) {
            $out[] = compact('impact', 'title', 'evidence', 'action', 'link');
        };
        $edit = route('provider.profile.edit');
        $row = fn (string $key) => collect($benchmark['rows'] ?? [])->firstWhere('key', $key);

        // Photos
        $limit = $plan['photos_limit'];
        $photoMedian = $row('photos')['median'] ?? null;
        if ($m['photos'] < 3) {
            $add('high', 'Ajoutez des photos', "Votre fiche compte {$m['photos']} photo".($m['photos'] > 1 ? 's' : '').' ; trois au minimum rendent une fiche crédible'.($photoMedian !== null ? " (médiane de la comparaison : $photoMedian)" : '').'.', 'Ajoutez des photos nettes et lumineuses de l\'établissement, des plats ou des chambres.', $edit);
        } elseif ($photoMedian !== null && $m['photos'] < $photoMedian && ($limit === null || $m['photos'] < $limit)) {
            $add('medium', 'Étoffez votre galerie', "{$m['photos']} photos contre une médiane de $photoMedian".($limit ? " (votre forfait en autorise $limit)" : '').'.', 'Ajoutez quelques photos pour rejoindre le niveau des fiches comparables.', $edit);
        } elseif ($limit !== null && $m['photos'] >= $limit) {
            $add('low', 'Limite de photos atteinte', "Votre forfait autorise $limit photos et vous les utilisez toutes.", 'Choisissez vos meilleures photos, ou passez à un forfait supérieur pour en montrer davantage.', route('provider.billing.plans'));
        }

        // Description
        $dl = $m['description_chars'];
        $descMedian = $row('description_chars')['median'] ?? null;
        $cap = $plan['description_chars'];
        if ($dl < 150) {
            $add('high', 'Rédigez une description complète', "Votre description fait $dl caractères ; en dessous de 150, elle ne rassure ni les visiteurs ni les moteurs de recherche.", 'Présentez l\'établissement, ses atouts, son emplacement et ce qui le distingue.', $edit);
        } elseif ($descMedian !== null && $descMedian > 0 && $dl < 0.6 * $descMedian && ($cap === null || $dl < $cap)) {
            $add('medium', 'Développez votre description', "$dl caractères contre une médiane de ".round($descMedian).' dans la comparaison.', 'Détaillez les services, les équipements, l\'ambiance et l\'accès.', $edit);
        }
        if (! $m['has']['short_desc']) {
            $add('medium', 'Ajoutez une description courte', 'Le résumé affiché dans les listes est vide.', 'Une phrase accrocheuse améliore le clic depuis les listes de l\'annuaire.', $edit);
        }

        // Informations manquantes
        $labels = array_column(ProviderProfileMetrics::CHECKS, 1, 0);
        foreach (['cover' => 'high', 'phone' => 'high', 'address' => 'high', 'hours' => 'medium', 'price' => 'medium', 'website' => 'low', 'logo' => 'low'] as $key => $impact) {
            if (! $m['has'][$key]) {
                $add($impact, 'Complétez : '.mb_strtolower($labels[$key]), 'Information absente de votre fiche.', 'Renseignez-la dans l\'édition de la fiche.', $edit);
            }
        }

        // Avis
        $ratingMedian = $row('rating')['median'] ?? null;
        if ($m['rating_count'] === 0) {
            $add('medium', 'Obtenez vos premiers avis', 'Aucun avis de visiteur pour l\'instant.', 'Invitez vos clients satisfaits à laisser un avis après leur visite ou leur séjour.', null);
        } elseif ($m['reviews_90d'] === 0) {
            $add('low', 'Relancez les avis', "Aucun nouvel avis depuis 90 jours (note actuelle {$m['rating']}/5, {$m['rating_count']} avis).", 'Sollicitez régulièrement vos clients pour garder une note récente.', null);
        } elseif ($ratingMedian !== null && $m['rating'] < $ratingMedian - 0.2) {
            $add('medium', 'Améliorez la note moyenne', "Note de {$m['rating']}/5 contre une médiane de $ratingMedian.", 'Lisez les avis récents, répondez-y et corrigez les points récurrents.', null);
        }

        // Visibilité : fiche complète mais peu vue
        $views = $row('views_30d');
        if ($views && $m['completeness'] >= 80 && $views['median'] > 0 && $views['mine'] < 0.5 * $views['median'] && $m['analytics_days'] >= 14) {
            $add('medium', 'Faites connaître votre fiche', "Votre fiche est complète ({$m['completeness']} %) mais reçoit {$views['mine']} vues en 30 jours contre une médiane de {$views['median']}.", 'Partagez le lien de votre fiche sur vos réseaux, sur votre site et auprès de vos clients.', null);
        }

        // Saison
        if (($forecast['status'] ?? '') === 'ok' && ($forecast['totals']['change'] ?? null) !== null && $forecast['totals']['change'] >= 15) {
            $add('high', 'Préparez la hausse de fréquentation', 'La prévision annonce environ +'.round($forecast['totals']['change']).' % de vues sur les 30 prochains jours.', 'Vérifiez vos photos, vos tarifs et vos horaires avant l\'afflux de visiteurs.', $edit);
        } elseif (($forecast['status'] ?? '') === 'ok' && ($forecast['totals']['change'] ?? null) !== null && $forecast['totals']['change'] <= -15) {
            $add('medium', 'Anticipez la baisse', 'La prévision annonce environ '.round($forecast['totals']['change']).' % de vues sur les 30 prochains jours.', 'Profitez de cette période pour enrichir votre fiche ou lancer une offre.', null);
        }

        $order = ['high' => 0, 'medium' => 1, 'low' => 2];
        usort($out, fn ($a, $b) => $order[$a['impact']] <=> $order[$b['impact']]);

        return $out;
    }
}

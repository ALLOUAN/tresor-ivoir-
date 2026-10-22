<?php

namespace App\Console\Commands;

use App\Services\Ai\Analytics\AnalyticsEvaluator;
use App\Services\Ai\ChatEvaluator;
use App\Services\Ai\Moderation\ModerationEvaluator;
use App\Services\Ai\WritingEvaluator;
use App\Services\Ai\PlannerEvaluator;
use App\Services\Ai\RecommendationEvaluator;
use App\Services\Ai\SearchEvaluator;
use Illuminate\Console\Command;

class AiEvaluate extends Command
{
    protected $signature = 'ai:evaluate {--k=5 : taille des listes recommandées}';

    protected $description = 'Évalue la recommandation hybride (hors ligne) et le planificateur d\'itinéraires';

    public function handle(RecommendationEvaluator $reco, PlannerEvaluator $planner, SearchEvaluator $search, ModerationEvaluator $moderation, ChatEvaluator $chat, WritingEvaluator $writing, AnalyticsEvaluator $analytics): int
    {
        $r = $reco->run((int) $this->option('k'));
        $this->info("Recommandation — {$r['actors']} acteurs évalués (dont ".round($r['simulated_share'] * 100).' % simulés), catalogue de '.$r['catalog'].' fiches, k='.$r['k']);
        $rows = [];
        foreach ($r['methods'] as $name => $m) {
            $rows[] = [$name, $m['precision'], $m['rappel'], $m['ndcg'], $m['diversite'], $m['couverture'], $m['longue_traine']];
        }
        $this->table(['Méthode', 'Précision@k', 'Rappel@k', 'NDCG@k', 'Diversité', 'Couverture', 'Longue traîne'], $rows);

        $p = $planner->run();
        $this->info('Planificateur — '.count($p['scenarios']).' scénarios');
        $this->table(
            ['Scénario', 'Jours OK', 'Budget OK', 'Km/jour', 'Étapes/jour', 'Intérêts couverts', 'Peu connues'],
            array_map(fn ($s) => [$s['name'], $s['days_ok'] ? 'oui' : 'NON', $s['budget_ok'] ? 'oui' : 'NON', $s['km_per_day'], $s['stops_per_day'], round($s['interest_coverage'] * 100).' %', round($s['long_tail'] * 100).' %'], $p['scenarios'])
        );

        foreach ([[false, 'principal (réglage)'], [true, 'validation (hors réglage)']] as [$held, $name]) {
            $s = $search->run(3, $held);
            $this->info('Recherche — jeu '.$name.' : '.$s['queries'].' requêtes');
            $this->table(['Système', 'Bonne fiche 1re', 'Dans les 3', 'MRR', 'Précision@3', 'Sans résultat'], array_map(
                fn ($n, $m) => [$n, round($m['hit1'] * 100).' %', round($m['hit3'] * 100).' %', $m['mrr'], round($m['precision3'] * 100).' %', round($m['sans_resultat'] * 100).' %'],
                array_keys($s['systems']), $s['systems']
            ));
        }

        $m = $moderation->run();
        $t = $m['text'];
        $i = $m['images'];
        $this->info('Modération — textes : '.$t['cases'].' cas, précision '.round($t['precision'] * 100).' %, rappel '.round($t['recall'] * 100).' %, F1 '.$t['f1'].' (faux positifs '.$t['fp'].', manqués '.$t['fn'].')');
        $this->info('Modération — images générées : '.$i['detected'].'/'.($i['detected'] + $i['missed']).' anomalies détectées, '.$i['false_alarms'].'/'.$i['clean_cases'].' fausses alertes');
        $this->info('Modération — contenus réels analysés : '.$m['real']['analysed'].' ('.$m['real']['ok'].' sains, '.$m['real']['review'].' à vérifier, '.$m['real']['likely'].' probables)');

        foreach ([[false, 'principal (réglage)'], [true, 'validation (hors réglage)']] as [$held, $name]) {
            $c = $chat->run($held);
            $this->info('Assistant — jeu '.$name.' : '.$c['answerable'].' questions avec réponse + '.$c['unanswerable'].' hors sujet');
            $this->line('  répondues '.round($c['answered_rate'] * 100).' % · bonne source citée '.round($c['source_cited_rate'] * 100).' % · en 1re position '.round($c['first_source_rate'] * 100).' % · refus hors sujet '.round($c['refusal_rate'] * 100).' %');
            foreach (array_filter($c['rows'], fn ($r) => ! $r['ok']) as $row) {
                $this->warn('  raté : '.$row['q'].' ['.$row['status'].']');
            }
        }
        $g = $chat->guardrails();
        $this->info('Assistant — garde-fous contre les inventions d\'un modèle : '.$g['correct'].'/'.$g['cases']);

        $w = $writing->run();
        $this->info('Rédaction — résumé : limite respectée '.round($w['summary']['within_limit'] * 100).' %, mots clés repris '.round($w['summary']['coverage'] * 100).' % (coupe du début : '.round($w['summary']['baseline_coverage'] * 100).' %) sur '.$w['summary']['articles'].' articles');
        $this->info('Rédaction — SEO dans les limites : '.round($w['seo']['within_limits'] * 100).' % · contrôles des productions d\'un modèle : '.$w['guards']['correct'].'/'.$w['guards']['cases']);

        $a = $analytics->run();
        $this->info('Analytique prédictive — '.$a['providers'].' prestataires, '.$a['holdout'].' derniers jours masqués (historique simulé à '.round($a['simulated_share'] * 100).' %)');
        $this->table(['Modèle', 'MAE (vues/jour)', 'MAPE', 'WAPE'], array_map(fn ($n, $m) => [$n, $m['mae'], $m['mape'].' %', round($m['wape'] * 100).' %'], array_keys($a['metrics']), $a['metrics']));
        $this->line('  intervalle à 80 % : '.round(($a['interval_coverage'] ?? 0) * 100).' % des jours réels à l\'intérieur');

        return self::SUCCESS;
    }
}

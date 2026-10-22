<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/**
 * Évaluation de l'assistant sur des questions annotées :
 *  - questions AVEC réponse dans le site : la bonne source est-elle citée ? en première position ?
 *  - questions SANS réponse dans le site : l'assistant refuse-t-il au lieu d'inventer ?
 *  - garde-fous contre les inventions d'un modèle de langage (réponses simulées à vérifier).
 * Le « jeu principal » a servi à régler l'assistant ; le « jeu de validation » a été écrit après,
 * sans réglage : c'est la mesure la plus fiable.
 */
class ChatEvaluator
{
    public function __construct(private readonly ChatAssistant $assistant) {}

    /** @return array<int,array{q:string,expect:?callable,note:string}> expect = null : la question n'a pas de réponse dans le site */
    public static function questions(bool $validation = false): array
    {
        $t = fn (string $needle) => fn (array $s) => str_contains(Str::lower(Str::ascii($s['title'])), $needle);
        $ty = fn (string $type) => fn (array $s) => $s['type'] === $type;
        $both = fn (callable ...$f) => fn (array $s) => collect($f)->every(fn ($x) => $x($s));

        if (! $validation) {
            return [
                ['q' => 'Comment créer un compte ?', 'expect' => $t('faq'), 'note' => 'FAQ'],
                ['q' => 'Comment devenir prestataire ?', 'expect' => $t('faq'), 'note' => 'FAQ'],
                ['q' => 'Où dormir à Grand-Bassam ?', 'expect' => fn ($s) => $s['type'] === 'Hébergement' && (str_contains($s['title'], 'Bassam') || str_contains($s['title'], 'Coloniale')), 'note' => 'liste par ville'],
                ['q' => 'Combien coûte une chambre au Pullman Abidjan ?', 'expect' => $t('pullman'), 'note' => 'prix'],
                ['q' => 'Quels sont les horaires du musée des civilisations ?', 'expect' => $t('musee des civilisations'), 'note' => 'horaires'],
                ['q' => 'Quand a lieu la Fête des Ignames ?', 'expect' => $t('ignames'), 'note' => 'date'],
                ['q' => 'where can I eat in Abidjan', 'expect' => fn ($s) => $s['type'] === 'Restaurant', 'note' => 'anglais'],
                ['q' => 'Que visiter à Abidjan ?', 'expect' => fn ($s) => $s['type'] === 'Site touristique', 'note' => 'liste par ville'],
                ['q' => 'Où visiter à Man ?', 'expect' => $t('cascades de man'), 'note' => 'liste par ville'],
                ['q' => 'Quels hôtels à Yamoussoukro ?', 'expect' => $both($t('yamoussoukro'), fn ($s) => $s['type'] === 'Hébergement'), 'note' => 'liste par ville'],
                ['q' => 'Quelle est la capitale de la France ?', 'expect' => null, 'note' => 'hors sujet'],
                ['q' => 'Qui a gagné la coupe du monde 2018 ?', 'expect' => null, 'note' => 'hors sujet'],
            ];
        }

        return [
            ['q' => 'Le Parc du Banco est-il ouvert le dimanche ?', 'expect' => $t('banco'), 'note' => 'horaires'],
            ['q' => 'Où manger à Abidjan ?', 'expect' => fn ($s) => $s['type'] === 'Restaurant', 'note' => 'liste par ville'],
            ['q' => 'Quel est le prix d\'une chambre à l\'Assinie Beach Resort ?', 'expect' => $t('assinie beach'), 'note' => 'prix'],
            ['q' => 'Peut-on visiter les Lacs aux Crocodiles Sacrés ?', 'expect' => $t('crocodiles'), 'note' => 'fiche précise'],
            ['q' => 'Y a-t-il un hôtel à Korhogo ?', 'expect' => $t('korhogo'), 'note' => 'existence'],
            ['q' => 'Que savez-vous sur le peuple Dan ?', 'expect' => $t('dan'), 'note' => 'culture'],
            ['q' => 'What are the opening hours of the Banco park?', 'expect' => $t('banco'), 'note' => 'anglais'],
            ['q' => 'Je veux ouvrir un compte sur le site', 'expect' => $t('faq'), 'note' => 'reformulation FAQ'],
            ['q' => 'Où signaler une erreur dans un article ?', 'expect' => $t('faq'), 'note' => 'FAQ'],
            ['q' => 'Combien coûte la Basilique Notre-Dame de la Paix ?', 'expect' => $t('basilique'), 'note' => 'prix'],
            ['q' => 'Quel temps fera-t-il demain à Abidjan ?', 'expect' => null, 'note' => 'hors sujet'],
            ['q' => 'Donne-moi une recette de pizza', 'expect' => null, 'note' => 'hors sujet'],
            ['q' => 'Quel est le meilleur smartphone ?', 'expect' => null, 'note' => 'hors sujet'],
            ['q' => 'Qui est le président de la France ?', 'expect' => null, 'note' => 'hors sujet'],
            ['q' => 'How do I get a visa for Japan?', 'expect' => null, 'note' => 'hors sujet (anglais)'],
        ];
    }

    /** @return array<string,mixed> */
    public function run(bool $validation = false): array
    {
        $ans = ['n' => 0, 'answered' => 0, 'cited_ok' => 0, 'top1_ok' => 0];
        $unans = ['n' => 0, 'refused' => 0];
        $rows = [];
        foreach (self::questions($validation) as $c) {
            $r = $this->assistant->answer($c['q']);
            $row = ['q' => $c['q'], 'note' => $c['note'], 'status' => $r['status'], 'sources' => array_map(fn ($s) => $s['title'], $r['sources'])];
            if ($c['expect'] === null) {
                $unans['n']++;
                $unans['refused'] += in_array($r['status'], ['no_source', 'handoff'], true) ? 1 : 0;
                $row['ok'] = in_array($r['status'], ['no_source', 'handoff'], true);
            } else {
                $ans['n']++;
                $isAnswered = $r['status'] === 'answered';
                $ans['answered'] += $isAnswered ? 1 : 0;
                $cited = $isAnswered && collect($r['sources'])->contains(fn ($s) => ($c['expect'])($s));
                $first = $isAnswered && isset($r['sources'][0]) && ($c['expect'])($r['sources'][0]);
                $ans['cited_ok'] += $cited ? 1 : 0;
                $ans['top1_ok'] += $first ? 1 : 0;
                $row['ok'] = $cited;
            }
            $rows[] = $row;
        }

        return [
            'questions' => $ans['n'] + $unans['n'],
            'answerable' => $ans['n'], 'answered_rate' => $ans['n'] ? round($ans['answered'] / $ans['n'], 3) : null,
            'source_cited_rate' => $ans['n'] ? round($ans['cited_ok'] / $ans['n'], 3) : null,
            'first_source_rate' => $ans['n'] ? round($ans['top1_ok'] / $ans['n'], 3) : null,
            'unanswerable' => $unans['n'], 'refusal_rate' => $unans['n'] ? round($unans['refused'] / $unans['n'], 3) : null,
            'rows' => $rows,
        ];
    }

    /**
     * Garde-fous contre un modèle de langage qui inventerait : on lui soumet des réponses
     * simulées, bonnes ou fautives, et on vérifie que seules les bonnes passent.
     *
     * @return array{cases:int,correct:int,rows:array}
     */
    public function guardrails(): array
    {
        $sources = [
            ['n' => 1, 'title' => 'Pullman Abidjan', 'url' => '/x', 'type' => 'Hébergement', 'image' => null, 'passages' => ['Chambres : Chambre Supérieure (110 000 FCFA la nuit) ; Chambre Deluxe (145 000 FCFA la nuit).']],
            ['n' => 2, 'title' => 'Hôtel du Golf', 'url' => '/y', 'type' => 'Hébergement', 'image' => null, 'passages' => ['Chambre Standard (65 000 FCFA la nuit).']],
        ];
        $cases = [
            ['label' => 'réponse fidèle et citée', 'out' => 'La Chambre Supérieure coûte 110 000 FCFA la nuit [1].', 'accept' => true],
            ['label' => 'deux sources citées', 'out' => 'Pullman : 145 000 FCFA [1] ; Golf : 65 000 FCFA [2].', 'accept' => true],
            ['label' => 'prix inventé', 'out' => 'La Chambre Supérieure coûte 90 000 FCFA la nuit [1].', 'accept' => false],
            ['label' => 'aucune source citée', 'out' => 'La chambre coûte 110 000 FCFA la nuit.', 'accept' => false],
            ['label' => 'source inexistante', 'out' => 'Chambre à 110 000 FCFA [4].', 'accept' => false],
            ['label' => 'réponse vide', 'out' => '   ', 'accept' => false],
            ['label' => 'réponse démesurée', 'out' => str_repeat('Chambre à 110 000 FCFA [1]. ', 60), 'accept' => false],
            ['label' => 'aveu d\'ignorance (NO_ANSWER)', 'out' => 'NO_ANSWER', 'accept' => null],
            ['label' => 'HTML injecté nettoyé', 'out' => '<script>alert(1)</script>Chambre à 110 000 FCFA [1].', 'accept' => true],
        ];
        $rows = [];
        $ok = 0;
        foreach ($cases as $c) {
            $res = $this->assistant->validated($c['out'], $sources);
            $verdict = $res === '__NO_ANSWER__' ? null : ($res !== null);
            $pass = $verdict === $c['accept'] && ($res === null || ! str_contains((string) $res, '<script'));
            $ok += $pass ? 1 : 0;
            $rows[] = ['label' => $c['label'], 'pass' => $pass];
        }

        return ['cases' => count($cases), 'correct' => $ok, 'rows' => $rows];
    }
}

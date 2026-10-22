<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Assistant conversationnel appuyé sur le contenu du site (RAG).
 *
 *  1. Politesses et demande d'un humain : réponses fixes, sans recherche.
 *  2. Récupération des passages les plus proches (Retriever).
 *  3. Sous le seuil de pertinence : « je n'ai pas trouvé », jamais de réponse inventée.
 *  4. Sinon : réponse composée des phrases du site (mode extractif) ou reformulée par un modèle de
 *     langage (mode anthropic) puis VÉRIFIÉE : sources valides citées, chiffres présents dans les sources.
 */
class ChatAssistant
{
    private const EN_WORDS = ['the', 'what', 'where', 'how', 'is', 'are', 'can', 'do', 'does', 'you', 'your', 'in', 'to', 'for', 'of', 'with', 'near', 'best', 'visit', 'stay', 'eat', 'book', 'price', 'cost', 'open', 'hotel', 'beach', 'which', 'there', 'any', 'have'];

    private ?string $cityNorm = null;

    public function __construct(private readonly Retriever $retriever, private readonly LlmClient $llm) {}

    /**
     * @param  array<int,array{role:string,text:string}>  $history  derniers échanges (au plus 4)
     * @return array{status:string,answer:string,sources:array,lang:string,engine:string,suggestions:string[],handoff:bool,score:float}
     */
    public function answer(string $question, array $history = [], ?array $position = null): array
    {
        $question = $this->clean($question);
        $lang = $this->language($question);

        if ($question === '') {
            return $this->reply('smalltalk', $lang === 'en' ? 'Ask me anything about destinations, hotels, restaurants, events or how the site works.' : 'Posez-moi une question sur les destinations, hébergements, restaurants, événements ou le fonctionnement du site.', $lang);
        }
        if ($r = $this->smallTalk($question, $lang)) {
            return $r;
        }
        if (preg_match('/\b(parler a|parler avec|un humain|conseiller|equipe|support|reclamation|plainte|human|agent|complaint|speak to|talk to)\b/', Str::lower(Str::ascii($question)))) {
            return $this->reply('handoff', $lang === 'en' ? 'For a personal request or a complaint, our team will answer you directly.' : 'Pour une demande personnelle ou une réclamation, notre équipe vous répond directement.', $lang, handoff: true);
        }

        // Question de relance très courte : on la rattache à la précédente
        $query = $question;
        if (count(TextVectorizer::tokens($question)) < 3 && $history) {
            $prev = collect($history)->where('role', 'user')->last();
            if ($prev) {
                $query = $this->clean($prev['text']).' '.$question;
            }
        }

        $found = $this->retriever->retrieve($query, (int) config('ai.chat.sources', 3));
        $top = $found['passages'][0]['score'] ?? 0.0;
        $coverage = $found['passages'][0]['coverage'] ?? 0.0;
        // Réponse seulement si la question est couverte par la source (ou si la correspondance est très nette).
        if (! $found['passages'] || $top < (float) config('ai.chat.min_score', 0.2) || ($coverage < 0.6 && $top < 0.5)) {
            // Le site ne suffit pas : réponse ancrée sur Google Maps (si activée), qui privilégie
            // quand même les fiches de la plateforme les plus proches de la question.
            if ($maps = $this->mapsFallback($query, $lang, $found['passages'], $position)) {
                return $maps;
            }

            return $this->reply('no_source', $lang === 'en'
                ? "I couldn't find this on the site, so I'd rather not guess. Try rephrasing, or contact our team."
                : "Je n'ai pas trouvé cette information sur le site, et je préfère ne pas deviner. Reformulez votre question ou contactez notre équipe.", $lang, handoff: true, score: $top, suggestions: $this->suggestions($lang));
        }

        $sources = [];
        foreach ($found['passages'] as $r) {
            $src = $r['passage']['src'];
            if (! isset($sources[$src])) {
                $sources[$src] = ['n' => count($sources) + 1, 'title' => $r['passage']['title'], 'url' => $r['passage']['url'], 'type' => $r['passage']['type_label'], 'image' => $r['passage']['image'], 'passages' => []];
            }
            $sources[$src]['passages'][] = $r['passage']['text'];
        }
        $sources = array_values($sources);

        $engine = 'extractive';
        $text = null;
        if (config('ai.chat.driver') === 'anthropic' && $this->llm->available()) {
            $text = $this->generate($query, $sources, $lang);
            if ($text === '__NO_ANSWER__') {
                return $this->reply('no_source', $lang === 'en' ? "I couldn't find a reliable answer on the site." : "Je n'ai pas trouvé de réponse fiable sur le site.", $lang, handoff: true, score: $top, suggestions: $this->suggestions($lang));
            }
            $engine = $text !== null ? 'anthropic' : 'extractive';
        }
        $this->cityNorm = ! empty($found['analysis']['interp']['city']) ? Str::lower(Str::ascii($found['analysis']['interp']['city']['name'])) : null;
        $text ??= $this->extract($query, $found['tokens'], $found['passages'], $sources, $lang);
        [$text, $sources] = $this->citedOnly($text, $sources);

        return $this->reply('answered', $text, $lang, sources: array_map(fn ($s) => ['n' => $s['n'], 'title' => $s['title'], 'url' => $s['url'], 'type' => $s['type'], 'image' => $s['image']], $sources), engine: $engine, score: $top, suggestions: $this->suggestions($lang));
    }

    // ── Génération ──────────────────────────────────────────────────────────

    /** Réponse du modèle de langage, ou null (échec / réponse refusée par la vérification), ou '__NO_ANSWER__'. */
    private function generate(string $question, array $sources, string $lang): ?string
    {
        $blocks = [];
        foreach ($sources as $s) {
            $blocks[] = '['.$s['n'].'] '.$s['title']." :\n".implode("\n", $s['passages']);
        }
        $system = "Tu es l'assistant du site Trésors d'Ivoire (tourisme en Côte d'Ivoire). Réponds UNIQUEMENT à partir des SOURCES fournies. "
            .'N\'invente aucun lieu, prix, horaire ni chiffre. Cite chaque information avec le numéro de sa source entre crochets, par exemple [1]. '
            ."Si les sources ne permettent pas de répondre, réponds exactement : NO_ANSWER. Réponse brève (4 phrases maximum), dans la langue de la question ({$lang}), sans mise en forme.";
        $out = $this->llm->complete($system, "QUESTION : {$question}\n\nSOURCES :\n".implode("\n\n", $blocks), 500, config('ai.chat.model'));
        if ($out === null) {
            return null;
        }

        return $this->validated($out, $sources);
    }

    /** Refuse toute réponse sans source citée, avec une source inexistante, trop longue, ou avec un chiffre absent des sources. */
    public function validated(string $out, array $sources): ?string
    {
        $out = trim(strip_tags($out));
        if ($out === '') {
            return null;
        }
        if (preg_match('/^\W*NO_ANSWER/i', $out)) {
            return '__NO_ANSWER__';
        }
        if (mb_strlen($out) > 900 || ! preg_match_all('/\[(\d+)\]/', $out, $m)) {
            return null;
        }
        $valid = range(1, count($sources));
        foreach ($m[1] as $n) {
            if (! in_array((int) $n, $valid, true)) {
                return null;
            }
        }
        // Anti-invention : chaque nombre cité doit exister dans les sources.
        $corpus = preg_replace('/\D+/', ' ', implode(' ', array_merge(...array_map(fn ($s) => $s['passages'], $sources))));
        $known = array_flip(array_filter(explode(' ', preg_replace('/\s+/', ' ', $corpus))));
        $joined = preg_replace('/\s+/', '', implode(' ', array_merge(...array_map(fn ($s) => $s['passages'], $sources))));
        $answerNums = preg_replace('/\[\d+\]/', ' ', $out);
        preg_match_all('/\d[\d\s.,]*\d|\d/', $answerNums, $nums);
        foreach ($nums[0] as $num) {
            $digits = preg_replace('/\D/', '', $num);
            if ($digits !== '' && ! isset($known[$digits]) && ! str_contains(preg_replace('/\D/', '', $joined), $digits)) {
                return null;
            }
        }

        return $out;
    }

    /** Réponse sans modèle : les phrases les plus pertinentes des meilleurs passages. */
    private function extract(string $question, array $tokens, array $passages, array $sources, string $lang): string
    {
        $want = array_flip($tokens);
        foreach ($tokens as $t) {
            foreach (SmartSearch::SYNONYMS as $g) {
                if (in_array($t, $g, true)) {
                    foreach ($g as $s) {
                        $want[$s] = true;
                    }
                }
            }
        }

        $first = $passages[0]['passage'];
        // FAQ : la réponse est la partie qui suit la question
        if ($first['type'] === 'faq' && str_contains($first['text'], "\n")) {
            $parts = preg_split("/\n+/", $first['text'], 2);
            $lead = trim($parts[1] ?? $first['text']);
            $note = $lang === 'en' ? ' (answer from the site help, in French)' : '';

            return $lead.' ['.$this->sourceNumber($sources, $first['title']).']'.$note;
        }

        // « Où manger / dormir… » : une liste d'adresses du type demandé vaut mieux qu'un paragraphe.
        if ($listing = $this->listing($question, $passages, $sources, $lang)) {
            return $listing;
        }

        $cands = [];
        $topScore = $passages[0]['score'];
        $qn = Str::lower(Str::ascii($question));
        foreach ($passages as $rank => $r) {
            if ($r['score'] < 0.6 * $topScore) {
                continue;   // passages bien moins proches que le meilleur : bruit
            }
            if ($this->cityNorm && ! ($r['located'] ?? false) && ($passages[0]['located'] ?? false)) {
                continue;   // la question vise une ville : on ne mélange pas avec les fiches d'ailleurs
            }
            $n = $this->sourceNumber($sources, $r['passage']['title']);
            foreach (preg_split('/(?<=[.!?])\s+/u', $r['passage']['text']) as $pos => $s) {
                $s = trim($s);
                if (str_word_count(Str::ascii($s)) < 3) {
                    continue;
                }
                // Les lignes de faits (« Hébergement : Pullman Abidjan. ») n'apprennent rien : on les écarte.
                if (str_starts_with($s, $r['passage']['type_label'].' : ') && str_word_count(Str::ascii($s)) <= 10) {
                    continue;
                }
                $hits = count(array_filter(TextVectorizer::tokens($s), fn ($w) => isset($want[$w])));
                $sn = Str::lower(Str::ascii($s));
                // Intention de la question : date, prix, horaires, adresse.
                if (preg_match('/\b(quand|date|when|jour)\b/', $qn) && str_starts_with($s, 'Date :')) {
                    $hits += 3;
                }
                if (preg_match('/\b(combien|prix|cout|coute|tarif|how much|price|cost)\b/', $qn) && preg_match('/fcfa|gratuit/', $sn)) {
                    $hits += 3;
                }
                if (preg_match('/\b(horaire|horaires|ouvert|ouvre|ferme|open|hours)\b/', $qn) && str_contains($sn, 'horaires')) {
                    $hits += 3;
                }
                if (preg_match('/\b(adresse|situe|trouve|located|address)\b/', $qn) && preg_match('/ville :|adresse|situe/', $sn)) {
                    $hits += 2;
                }
                $cands[] = ['s' => $s, 'n' => $n, 'score' => $hits + ($rank === 0 ? 0.5 : 0) - $pos * 0.05];
            }
        }
        usort($cands, fn ($a, $b) => $b['score'] <=> $a['score']);
        // Au plus 3 phrases, et seulement celles qui touchent à la question (au moins une en tout cas).
        $relevant = array_values(array_filter($cands, fn ($c) => $c['score'] >= 1.0));
        $chosen = array_slice($relevant ?: $cands, 0, 3);
        // Ordre de lecture : par source puis par apparition
        usort($chosen, fn ($a, $b) => $a['n'] <=> $b['n']);
        $text = implode(' ', array_map(fn ($c) => rtrim($c['s']).' ['.$c['n'].']', $chosen));

        return ($lang === 'en' ? 'Here is what the site says (content is in French): ' : '').$text;
    }

    /** Liste d'adresses : au moins deux fiches du même type (hébergement, restaurant…) parmi les meilleures sources. */
    private function listing(string $question, array $passages, array $sources, string $lang): ?string
    {
        $qn = Str::lower(Str::ascii($question));
        if (! preg_match('/\b(ou|where|que|quoi|quel|quels|quelle|quelles|which|what)\b/', $qn)) {
            return null;
        }
        $listable = ['accommodation', 'restaurant', 'leisure', 'experience', 'agency', 'transport', 'site'];
        $visit = (bool) preg_match('/\b(visiter|visit|voir|see|decouvrir|discover|activites?)\b/', $qn);
        $top = $passages[0]['score'];
        $byType = [];
        foreach ($passages as $r) {
            $p = $r['passage'];
            if ($this->cityNorm && ! ($r['located'] ?? false)) {
                continue;   // la question vise une ville : on ne liste que ce qui s'y trouve
            }
            // « Que visiter… » : sites, expériences et loisirs forment une seule liste.
            $group = $visit && in_array($p['type'], ['site', 'experience', 'leisure'], true) ? 'visit' : $p['type'];
            if (in_array($p['type'], $listable, true) && $r['score'] >= 0.35 * $top && ! isset($byType[$group][$p['title']])) {
                $byType[$group][$p['title']] = $p;
            }
        }
        if (! $byType) {
            return null;
        }
        uasort($byType, fn ($a, $b) => count($b) <=> count($a));
        $type = array_key_first($byType);
        $items = array_values($byType[$type]);
        if (count($items) < 2) {
            return null;
        }
        $parts = [];
        foreach (array_slice($items, 0, 4) as $p) {
            $price = preg_match('/(Prix : dès [\d\s]+ FCFA la nuit|Repas moyen : environ [\d\s]+ FCFA|Entrée : [\d\s]+ FCFA|Entrée gratuite)/u', $p['text'], $m) ? ' ('.lcfirst($m[1]).')' : '';
            $parts[] = $p['title'].$price.' ['.$this->sourceNumber($sources, $p['title']).']';
        }

        return ($lang === 'en' ? 'Here are some options on the site (content in French): ' : 'Voici quelques adresses : ').implode(' ; ', $parts).'.';
    }

    /** Ne garde que les sources citées dans la réponse et les renumérote dans l'ordre d'apparition. */
    private function citedOnly(string $text, array $sources): array
    {
        preg_match_all('/\[(\d+)\]/', $text, $m);
        $order = array_values(array_unique(array_map('intval', $m[1])));
        if (! $order) {
            return [$text, array_slice($sources, 0, 1)];
        }
        $map = [];
        $kept = [];
        foreach ($order as $i => $old) {
            if (isset($sources[$old - 1])) {
                $map[$old] = $i + 1;
                $kept[] = ['n' => $i + 1] + $sources[$old - 1];
            }
        }
        $text = preg_replace_callback('/\[(\d+)\]/', fn ($x) => '['.($map[(int) $x[1]] ?? $x[1]).']', $text);

        return [$text, $kept];
    }

    private function sourceNumber(array $sources, string $title): int
    {
        foreach ($sources as $s) {
            if ($s['title'] === $title) {
                return $s['n'];
            }
        }

        return 1;
    }

    // ── Utilitaires ─────────────────────────────────────────────────────────

    private function clean(string $q): string
    {
        $q = trim(preg_replace('/\s+/', ' ', strip_tags($q)));

        return mb_substr($q, 0, (int) config('ai.chat.max_question', 300));
    }

    public function language(string $q): string
    {
        $words = preg_split('/[^a-z]+/', Str::lower(Str::ascii($q)), -1, PREG_SPLIT_NO_EMPTY);
        $en = count(array_filter($words, fn ($w) => in_array($w, self::EN_WORDS, true)));
        $fr = count(array_filter($words, fn ($w) => in_array($w, ['le', 'la', 'les', 'un', 'une', 'des', 'du', 'de', 'est', 'quel', 'quelle', 'comment', 'ou', 'pour', 'je', 'vous', 'sur', 'dans', 'puis', 'peut', 'quand', 'combien', 'ou'], true)));

        return $en >= 2 && $en > $fr ? 'en' : 'fr';
    }

    private function smallTalk(string $q, string $lang): ?array
    {
        $n = trim(preg_replace('/[^a-z ]+/', ' ', Str::lower(Str::ascii($q))));
        $n = preg_replace('/\s+/', ' ', $n);
        if (preg_match('/^(bonjour|bonsoir|salut|coucou|hello|hi|hey|good morning|good evening)( .*)?$/', $n) && str_word_count($n) <= 4) {
            return $this->reply('smalltalk', $lang === 'en' ? 'Hello! Ask me about destinations, hotels, restaurants, events or how the site works.' : 'Bonjour ! Posez-moi une question sur les destinations, hébergements, restaurants, événements ou le fonctionnement du site.', $lang, suggestions: $this->suggestions($lang));
        }
        if (preg_match('/^(merci|thanks|thank you|super merci|ok merci)( .*)?$/', $n) && str_word_count($n) <= 4) {
            return $this->reply('smalltalk', $lang === 'en' ? 'You are welcome! Anything else?' : 'Avec plaisir ! Autre chose ?', $lang);
        }
        if (preg_match('/(qui es tu|tu es qui|who are you|what are you|tu es un robot|es tu un robot)/', $n)) {
            return $this->reply('smalltalk', $lang === 'en' ? 'I am the Trésors d\'Ivoire assistant, an automated tool: I answer from the content published on this site and cite my sources.' : "Je suis l'assistant de Trésors d'Ivoire, un outil automatique : je réponds à partir des contenus publiés sur ce site et je cite mes sources.", $lang);
        }

        return null;
    }

    private function suggestions(string $lang): array
    {
        return $lang === 'en'
            ? ['Where to stay in Grand-Bassam?', 'How do I book a room?', 'What events are coming up?']
            : ['Où dormir à Grand-Bassam ?', 'Comment réserver une chambre ?', 'Quels événements arrivent bientôt ?'];
    }

    /** Réponse Gemini + Google Maps, au format de l'assistant (sources du site puis lieux Google). */
    private function mapsFallback(string $question, string $lang, array $passages, ?array $position): ?array
    {
        $maps = app(\App\Services\Maps\MapsGroundedAnswer::class);
        if (! $maps->enabled()) {
            return null;
        }

        $context = collect($passages)->map(fn ($r) => ['title' => $r['passage']['title'], 'url' => $r['passage']['url'], 'text' => $r['passage']['text']])->unique('url')->values()->all();
        $res = $maps->answer($question, $lang, $context, $position);
        if (! $res) {
            return null;
        }

        $sources = collect($context)->filter(fn ($s) => str_contains($res['answer'], $s['title']) || str_contains($res['answer'], $s['url']))
            ->map(fn ($s) => ['title' => $s['title'], 'url' => $s['url'], 'type' => 'Trésors d\'Ivoire', 'image' => null])
            ->merge(collect($res['places'])->map(fn ($p) => ['title' => $p['title'], 'url' => $p['url'], 'type' => 'Google Maps', 'image' => null, 'maps' => true]))
            ->values()
            ->map(fn ($s, $i) => ['n' => $i + 1] + $s)
            ->all();

        $r = $this->reply('answered', $res['answer'], $lang, sources: $sources, engine: 'gemini_maps', score: 0.0, suggestions: $this->suggestions($lang));
        $r['maps_widget'] = $res['widget_token'];

        return $r;
    }

    private function reply(string $status, string $answer, string $lang, array $sources = [], string $engine = 'fixed', bool $handoff = false, float $score = 0.0, array $suggestions = []): array
    {
        return compact('status', 'answer', 'sources', 'lang', 'engine', 'suggestions', 'handoff', 'score');
    }

    /** Journal (question tronquée, sans identifiant ni numéro) pour mesurer les questions sans réponse. */
    public function log(string $question, array $r): ?int
    {
        if (! \App\Support\SchemaCache::hasTable('ai_chat_logs')) {
            return null;
        }
        $q = preg_replace('/\+?\d[\d\s.\-]{6,}\d/', '[numéro]', preg_replace('/[\w.+-]+@[\w-]+\.[\w.]+/', '[e-mail]', $question));

        return DB::table('ai_chat_logs')->insertGetId([
            'question' => mb_substr($q, 0, 200), 'status' => $r['status'], 'top_score' => round($r['score'], 3),
            'sources' => count($r['sources']), 'lang' => $r['lang'], 'engine' => $r['engine'], 'created_at' => now(),
        ]);
    }
}

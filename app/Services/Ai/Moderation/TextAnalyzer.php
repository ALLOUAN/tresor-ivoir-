<?php

namespace App\Services\Ai\Moderation;

use App\Services\Ai\TextVectorizer;
use Illuminate\Support\Str;

/**
 * Analyse d'un texte (article, avis, fiche, événement) : chaque anomalie détectée porte un poids
 * (0-100) ; les poids se combinent en un score de risque unique (« ou bruité » : plusieurs
 * indices modérés font monter le score sans jamais dépasser 100).
 */
class TextAnalyzer
{
    public const LABELS = [
        'profanity' => 'Langage injurieux ou haineux',
        'spam_phrases' => 'Formules typiques de spam ou d\'arnaque',
        'links' => 'Liens externes',
        'contact_info' => 'Coordonnées de contact dans le texte',
        'payment_bypass' => 'Incitation à sortir de la plateforme (paiement/contact direct)',
        'shouting' => 'Texte en majuscules',
        'repetition' => 'Répétitions anormales',
        'placeholder' => 'Texte de remplissage ou de test',
        'gibberish' => 'Texte incohérent (suite de lettres sans sens)',
        'too_short' => 'Contenu trop court',
        'incomplete' => 'Fiche incomplète',
        'duplicate' => 'Doublon ou quasi-doublon',
    ];

    /** @param array<string,mixed> $o kind: article|review|provider|event ; words_min ; missing: string[] */
    public function analyze(string $text, array $o = []): array
    {
        $kind = $o['kind'] ?? 'article';
        $flags = [];
        $add = function (string $code, int $weight, string $detail = '') use (&$flags) {
            if ($weight > ($flags[$code]['weight'] ?? 0)) {
                $flags[$code] = ['weight' => $weight, 'detail' => $detail];
            }
        };

        $plain = trim(html_entity_decode(strip_tags($text)));
        $norm = ' '.Str::lower(Str::ascii($plain)).' ';
        $normSpaced = preg_replace('/[^a-z0-9%\']+/', ' ', $norm);
        $words = TextVectorizer::tokens($plain);
        $nWords = str_word_count(Str::ascii($plain));

        // Injures
        $bad = array_values(array_unique(array_merge(
            (array) config('ai.moderation.profanity'),
            array_map(fn ($w) => Str::lower(Str::ascii($w)), (array) config('chat.prohibited_words', []))
        )));
        $hits = $this->phrasesFound($normSpaced, $bad);
        if ($hits) {
            $add('profanity', 60, implode(', ', array_slice($hits, 0, 3)));
        }

        // Spam / arnaque
        $hits = $this->phrasesFound($normSpaced, (array) config('ai.moderation.spam_phrases'));
        if ($hits) {
            $add('spam_phrases', count($hits) >= 2 ? 55 : 40, implode(', ', array_slice($hits, 0, 3)));
        }

        // Liens
        preg_match_all('~(?:https?://|www\.)\S+|\b[a-z0-9-]+\.(?:com|net|org|ci|info|biz|xyz|ru)\b~i', $plain, $m);
        $links = count($m[0]);
        if ($links >= 3) {
            $add('links', 30, $links.' liens');
        } elseif ($links === 2) {
            $add('links', 20, '2 liens');
        } elseif ($links === 1) {
            $add('links', $kind === 'review' ? 20 : ($kind === 'article' ? 5 : 0), '1 lien');
        }
        if (preg_match('/\b(bit\.ly|tinyurl|t\.co|goo\.gl|cutt\.ly)\b/i', $plain)) {
            $add('links', 35, 'lien raccourci');
        }

        // Coordonnées
        $hasContact = preg_match('/\+?\d[\d\s.\-]{7,}\d/', $plain) || preg_match('/[\w.+-]+@[\w-]+\.[\w.]+/', $plain);
        if ($hasContact) {
            $add('contact_info', in_array($kind, ['review'], true) ? 25 : ($kind === 'article' ? 10 : 0), 'numéro ou e-mail');
        }

        // Contournement de la plateforme (avis uniquement : dans une fiche, ces mots sont légitimes)
        if ($kind === 'review') {
            $hits = $this->phrasesFound($normSpaced, array_map(fn ($w) => Str::lower(Str::ascii($w)), (array) config('chat.payment_bypass_keywords', [])));
            if ($hits) {
                $add('payment_bypass', 25, implode(', ', array_slice($hits, 0, 2)));
            }
        }

        // Majuscules
        $letters = preg_match_all('/\p{L}/u', $plain);
        if ($letters >= 40) {
            $upper = preg_match_all('/\p{Lu}/u', $plain);
            if ($upper / $letters >= 0.5) {
                $add('shouting', 20, round($upper / $letters * 100).' % de majuscules');
            }
        }

        // Répétitions
        if (preg_match('/(.)\1{5,}/u', $plain)) {
            $add('repetition', 15, 'caractère répété');
        }
        if (count($words) >= 30) {
            $freq = array_count_values($words);
            arsort($freq);
            $topShare = reset($freq) / count($words);
            if ($topShare > 0.25) {
                $add('repetition', 20, '« '.array_key_first($freq).' » = '.round($topShare * 100).' % des mots');
            }
        }

        // Remplissage
        foreach ((array) config('ai.moderation.placeholders') as $ph) {
            if (str_contains($normSpaced, ' '.Str::lower(Str::ascii($ph)).' ') || str_contains($normSpaced, Str::lower(Str::ascii($ph)))) {
                $add('placeholder', 35, $ph);
                break;
            }
        }

        // Charabia : mots longs sans voyelle, ou très peu de variété de lettres
        $long = array_filter(preg_split('/[^a-z]+/', trim($normSpaced)), fn ($w) => strlen($w) >= 4);
        if (count($long) >= 3) {
            $noVowel = count(array_filter($long, fn ($w) => ! preg_match('/[aeiouy]/', $w)));
            if ($noVowel / count($long) >= 0.3) {
                $add('gibberish', 30, round($noVowel / count($long) * 100).' % de mots sans voyelle');
            }
        }

        // Longueur
        $min = $o['words_min'] ?? match ($kind) {'article' => 120, 'provider' => 30, 'event' => 25, default => 0};
        if ($min > 0 && $nWords < $min) {
            $add('too_short', $nWords < $min / 3 ? 25 : 15, $nWords.' mots (minimum conseillé : '.$min.')');
        }
        if ($kind === 'review' && $nWords < 3 && $plain !== '') {
            $add('too_short', 10, $nWords.' mot(s)');
        }

        // Champs manquants
        $missing = (array) ($o['missing'] ?? []);
        if (count($missing) >= 2) {
            $add('incomplete', 15, 'manque : '.implode(', ', $missing));
        } elseif (count($missing) === 1) {
            $add('incomplete', 8, 'manque : '.$missing[0]);
        }

        return ['flags' => $flags, 'score' => self::combine($flags), 'words' => $nWords];
    }

    /** Score global (0-100) : 1 − Π(1 − poids/100). */
    public static function combine(array $flags): int
    {
        $keep = 1.0;
        foreach ($flags as $f) {
            $keep *= 1 - min(100, max(0, $f['weight'])) / 100;
        }

        return (int) round((1 - $keep) * 100);
    }

    /** @return string[] expressions trouvées (mots entiers) */
    private function phrasesFound(string $normSpaced, array $phrases): array
    {
        $found = [];
        $hay = ' '.trim(preg_replace('/\s+/', ' ', $normSpaced)).' ';
        foreach ($phrases as $p) {
            $p = trim(preg_replace('/[^a-z0-9%\']+/', ' ', Str::lower(Str::ascii($p))));
            if ($p !== '' && str_contains($hay, ' '.$p.' ')) {
                $found[] = $p;
            }
        }

        return $found;
    }
}

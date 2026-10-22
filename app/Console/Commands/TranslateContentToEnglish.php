<?php

namespace App\Console\Commands;

use App\Services\Ai\LlmClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remplit les champs anglais (*_en) encore vides à partir de leur version française (*_fr),
 * via le modèle de langage configuré (ANTHROPIC_API_KEY). Une traduction déjà saisie n'est
 * jamais écrasée. --dry-run liste ce qui serait traduit sans rien appeler ni écrire.
 */
class TranslateContentToEnglish extends Command
{
    protected $signature = 'content:translate-en {--dry-run : Affiche ce qui serait traduit, sans rien écrire} {--table= : Limiter à une table} {--limit=50 : Nombre maximal de champs traduits par exécution}';

    protected $description = 'Traduit en anglais les contenus dont la version anglaise est vide';

    /** Champs bilingues traduits : table => [champ => contient du HTML ?] */
    public const FIELDS = [
        'articles' => ['title' => false, 'excerpt' => false, 'content' => true, 'meta_title' => false, 'meta_desc' => false],
        'events' => ['title' => false, 'subtitle' => false, 'description' => true, 'meta_title' => false, 'meta_desc' => false],
        'providers' => ['description' => true, 'meta_title' => false, 'meta_desc' => false],
        'information_pages' => ['title' => false, 'body' => true],
        'article_categories' => ['name' => false, 'description' => false],
        'provider_categories' => ['name' => false, 'description' => false],
        'event_categories' => ['name' => false],
        'artwork_categories' => ['name' => false],
        'tour_categories' => ['name' => false],
        'menu_categories' => ['name' => false],
        'activity_categories' => ['name' => false],
        'transport_categories' => ['name' => false],
        'tags' => ['name' => false],
        'subscription_plans' => ['name' => false],
        // Fiches dont la colonne française n'a pas de suffixe (description → description_en).
        'accommodations' => ['short_description' => false, 'description' => true],
        'restaurants' => ['short_description' => false, 'description' => true],
        'leisure_venues' => ['short_description' => false, 'description' => true],
        'tourist_experiences' => ['short_description' => false, 'description' => true],
        'tourist_cities' => ['description' => true],
        'cultural_peoples' => ['description' => true],
        'cultural_elements' => ['short_description' => false, 'description' => true],
        'travel_agencies' => ['short_description' => false, 'description' => true],
        'transport_companies' => ['short_description' => false, 'description' => true],
        'tourist_sites' => ['short_description' => false, 'description' => true],
        'bundles' => ['title' => false, 'summary' => false, 'description' => false],
    ];

    public function handle(LlmClient $llm): int
    {
        $dry = (bool) $this->option('dry-run');
        if (! $dry && ! $llm->available()) {
            $this->error('Aucun modèle de langage configuré (ANTHROPIC_API_KEY). Utilisez --dry-run pour voir ce qui reste à traduire.');

            return self::FAILURE;
        }

        $limit = max(1, (int) $this->option('limit'));
        $done = $failed = $pending = 0;

        foreach (self::FIELDS as $table => $fields) {
            if (($only = $this->option('table')) && $only !== $table) continue;
            if (! Schema::hasTable($table)) continue;

            foreach ($fields as $field => $isHtml) {
                [$fr, $en] = [$field.'_fr', $field.'_en'];
                if (! Schema::hasColumn($table, $fr)) $fr = $field;
                if (! Schema::hasColumn($table, $fr) || ! Schema::hasColumn($table, $en)) continue;

                $rows = DB::table($table)
                    ->select('id', $fr)
                    ->whereNotNull($fr)->where($fr, '!=', '')
                    ->where(fn ($q) => $q->whereNull($en)->orWhere($en, ''))
                    ->orderBy('id')
                    ->get();

                foreach ($rows as $row) {
                    $pending++;
                    if ($dry) {
                        $this->line("{$table}#{$row->id}.{$en} ← ".mb_strimwidth(strip_tags((string) $row->$fr), 0, 70, '…'));
                        continue;
                    }
                    if ($done >= $limit) break 3;

                    $translated = $this->translate($llm, (string) $row->$fr, $isHtml);
                    if ($translated === null) {
                        $failed++;
                        continue;
                    }
                    // Écrit seulement si le champ est toujours vide (aucune saisie manuelle entre-temps).
                    DB::table($table)->where('id', $row->id)
                        ->where(fn ($q) => $q->whereNull($en)->orWhere($en, ''))
                        ->update([$en => $translated]);
                    $done++;
                }
            }
        }

        $dry
            ? $this->info("{$pending} champ(s) à traduire.")
            : $this->info("Traduits : {$done} · échecs : {$failed}".($done >= $limit ? " (limite de {$limit} atteinte, relancez pour continuer)" : ''));

        return self::SUCCESS;
    }

    private function translate(LlmClient $llm, string $text, bool $isHtml): ?string
    {
        $system = 'You translate content of an Ivorian culture and tourism magazine from French to British English. '
            .'Translate faithfully, keep proper nouns, place names and local terms (maquis, attiéké, pagne…) unchanged, '
            .($isHtml ? 'keep every HTML tag and attribute exactly as is and translate only the visible text, ' : 'return plain text, ')
            .'and answer with the translation only, without any comment.';

        $out = $llm->complete($system, $text, $isHtml ? 4000 : 600, config('ai.writing.model'), 90);
        $out = $out !== null ? trim($out) : null;

        // Garde-fous : réponse vide, ou HTML dont le nombre de balises diverge nettement de l'original.
        if ($out === null || $out === '') return null;
        if ($isHtml && abs(substr_count($out, '<') - substr_count($text, '<')) > max(2, (int) (substr_count($text, '<') * 0.1))) return null;

        return $out;
    }
}

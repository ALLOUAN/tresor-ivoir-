<?php

namespace App\Services\Ai\Moderation;

use App\Models\Article;
use App\Models\Event;
use App\Models\Provider;
use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Modération assistée. Calcule, pour chaque contenu, un score de risque (0-100), les raisons
 * et des suggestions (rubrique, étiquettes, doublons), puis enregistre un rapport dans
 * moderation_reports. Elle ne publie, ne refuse et ne supprime jamais rien : l'administrateur
 * décide, en traitant d'abord les cas douteux.
 */
class ModerationService
{
    public function __construct(
        private readonly TextAnalyzer $text,
        private readonly DuplicateDetector $dups,
        private readonly TaggingSuggester $tagger,
        private readonly ImageAnalyzer $images,
        private readonly ImageComplianceChecker $vision,
    ) {}

    public static function verdict(int $score): string
    {
        return $score >= config('ai.moderation.likely_threshold') ? 'likely' : ($score >= config('ai.moderation.review_threshold') ? 'review' : 'ok');
    }

    // ── Textes ──────────────────────────────────────────────────────────────

    /** Analyse sans enregistrer. @return array<string,mixed> rapport */
    public function analyzeText(string $type, object $row): array
    {
        $opts = ['kind' => $type];
        $suggest = [];
        $dupType = $type;

        switch ($type) {
            case 'article':
                $title = (string) ($row->title_fr ?? '');
                $body = trim(($row->excerpt_fr ?? '').' '.($row->content_fr ?? ''));
                $missing = array_values(array_filter([
                    empty($row->excerpt_fr) ? 'résumé' : null, empty($row->cover_url) ? 'image de couverture' : null, empty($row->category_id) ? 'rubrique' : null,
                ]));
                $opts['missing'] = $missing;
                $label = $title ?: 'Article #'.$row->id;
                $url = ! empty($row->slug_fr) ? route('articles.show', $row->slug_fr) : null;
                break;
            case 'review':
                $title = (string) ($row->title ?? '');
                $body = (string) ($row->comment ?? '');
                $label = Str::limit(($row->author_name ? $row->author_name.' : ' : '').($title ?: $body), 70);
                $slug = DB::table('providers')->where('id', $row->provider_id)->value('slug');
                $url = $slug ? route('providers.show', $slug) : null;
                break;
            case 'provider':
                $title = (string) ($row->name ?? '');
                $body = trim(($row->short_desc_fr ?? '').' '.($row->description_fr ?? ''));
                $label = $title ?: 'Prestataire #'.$row->id;
                $url = ! empty($row->slug) ? route('providers.show', $row->slug) : null;
                break;
            case 'event':
                $title = (string) ($row->title_fr ?? '');
                $body = trim(($row->subtitle_fr ?? '').' '.($row->description_fr ?? ''));
                $label = $title ?: 'Événement #'.$row->id;
                $url = ! empty($row->slug) ? route('events.show', $row->slug) : null;
                break;
            default:
                throw new \InvalidArgumentException("Type inconnu : $type");
        }

        $res = $this->text->analyze($title.' . '.$body, $opts);
        $flags = $res['flags'];

        if (trim(strip_tags($title.$body)) !== '') {
            $dup = $this->dups->detect($dupType, (int) $row->id, $title, $body);
            if ($dup) {
                $flags['duplicate'] = ['weight' => $dup['weight'], 'detail' => $dup['detail']];
                $suggest['duplicates'] = $dup['matches'];
            }
        }
        if ($type === 'article') {
            $suggest += ['tagging' => $this->tagger->suggest((int) $row->id, $title, $body, $row->category_id ? (int) $row->category_id : null)];
        }

        $score = TextAnalyzer::combine($flags);

        return $this->report($type, (int) $row->id, 'text', $label, $url, $score, $flags, $suggest, sha1($title.'|'.$body.'|'.json_encode($opts['missing'] ?? [])));
    }

    public function analyze(string $type, object $row): array
    {
        $r = $this->analyzeText($type, $row);
        $this->persist($r);

        return $r;
    }

    /** Pour les observateurs de modèles : jamais bloquant. */
    public function analyzeModel(Model $model): void
    {
        $map = [Article::class => 'article', Review::class => 'review', Provider::class => 'provider', Event::class => 'event'];
        $type = $map[get_class($model)] ?? null;
        if (! $type || ! config('ai.enabled', true) || ! config('ai.moderation.auto', true)) {
            return;
        }
        $this->dups->forget();
        $row = (object) $model->getAttributes();
        $this->analyze($type, $row);
        if ($type === 'article' && ! empty($row->cover_url)) {
            $this->analyzeImage('article_cover', (int) $row->id, (string) ($row->title_fr ?? 'Article'), (string) $row->cover_url, null);
        }
    }

    // ── Images ──────────────────────────────────────────────────────────────

    /** Chemin absolu d'une image hébergée sur le site, null si externe ou introuvable. */
    public function resolvePath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        if (preg_match('#^https?://#i', $url)) {
            $host = parse_url($url, PHP_URL_HOST);
            if ($host !== parse_url((string) config('app.url'), PHP_URL_HOST)) {
                return null;
            }
            $url = (string) parse_url($url, PHP_URL_PATH);
        }
        $rel = preg_replace('#^/?storage/#', '', ltrim($url, '/'));
        $abs = Storage::disk('public')->path($rel);

        return is_file($abs) ? $abs : null;
    }

    /** @return array<string,mixed>|null null si l'image n'est pas hébergée ici */
    public function analyzeImage(string $subjectType, int $id, string $label, string $url, ?string $pageUrl, ?array $knownHashes = null): ?array
    {
        $abs = $this->resolvePath($url);
        if (! $abs) {
            return null;
        }
        $a = $this->images->analyze($abs);
        $flags = $a['flags'];
        $suggest = ['dhash' => $a['dhash'], 'metrics' => $a['metrics'], 'file' => basename($abs)];

        // Doublon visuel
        if ($a['dhash']) {
            $limit = (int) config('ai.moderation.image.duplicate_distance');
            $known = $knownHashes ?? $this->knownHashes();
            foreach ($known as $k) {
                if (($k['type'] !== $subjectType || $k['id'] !== $id) && $k['hash'] && $k['file'] !== basename($abs)
                    && ImageAnalyzer::distance($a['dhash'], $k['hash']) <= $limit) {
                    $flags['visual_duplicate'] = ['weight' => 35, 'detail' => 'ressemble à « '.$k['label'].' »'];
                    $suggest['duplicates'] = [['label' => $k['label'], 'file' => $k['file']]];
                    break;
                }
            }
        }

        // Conformité du contenu (modèle de vision, si configuré)
        $c = $this->vision->check($abs);
        $suggest['content_check'] = $c['status'];
        if ($c['status'] === 'refused') {
            $flags['non_compliant'] = ['weight' => 70, 'detail' => implode(' ; ', $c['reasons'])];
        }

        $score = TextAnalyzer::combine($flags);
        $r = $this->report($subjectType, $id, 'image', $label, $pageUrl, $score, $flags, $suggest, sha1_file($abs).'|'.$c['status']);
        $this->persist($r);

        return $r;
    }

    /** @return array<int,array{type:string,id:int,hash:?string,label:string,file:string}> */
    private function knownHashes(): array
    {
        return DB::table('moderation_reports')->where('kind', 'image')->get(['subject_type', 'subject_id', 'label', 'suggestions'])->map(function ($r) {
            $s = json_decode((string) $r->suggestions, true) ?: [];

            return ['type' => $r->subject_type, 'id' => (int) $r->subject_id, 'hash' => $s['dhash'] ?? null, 'label' => (string) $r->label, 'file' => (string) ($s['file'] ?? '')];
        })->all();
    }

    // ── Passage complet ─────────────────────────────────────────────────────

    /** @return array<string,int> compteurs */
    public function scanAll(bool $withImages = true, int $imageLimit = 300): array
    {
        $this->dups->forget();
        $n = ['article' => 0, 'review' => 0, 'provider' => 0, 'event' => 0, 'image' => 0, 'image_skipped' => 0];

        foreach (DB::table('articles')->whereNull('deleted_at')->get() as $r) {
            $this->analyze('article', $r);
            $n['article']++;
        }
        foreach (DB::table('reviews')->get() as $r) {
            $this->analyze('review', $r);
            $n['review']++;
        }
        foreach (DB::table('providers')->whereNull('deleted_at')->get() as $r) {
            $this->analyze('provider', $r);
            $n['provider']++;
        }
        foreach (DB::table('events')->get() as $r) {
            $this->analyze('event', $r);
            $n['event']++;
        }

        if ($withImages) {
            $sources = [];
            foreach (DB::table('articles')->whereNull('deleted_at')->whereNotNull('cover_url')->get(['id', 'title_fr', 'cover_url', 'slug_fr']) as $r) {
                $sources[] = ['article_cover', (int) $r->id, 'Couverture : '.$r->title_fr, $r->cover_url, $r->slug_fr ? route('articles.show', $r->slug_fr) : null];
            }
            foreach (DB::table('media')->where('type', 'image')->get(['id', 'url', 'file_path', 'alt_text', 'original_name']) as $r) {
                $sources[] = ['media', (int) $r->id, 'Média : '.($r->alt_text ?: $r->original_name), $r->url ?: $r->file_path, null];
            }
            foreach (DB::table('accommodation_media')->get(['id', 'url', 'caption', 'accommodation_id']) as $r) {
                $sources[] = ['accommodation_media', (int) $r->id, 'Hébergement #'.$r->accommodation_id.' : '.($r->caption ?: 'photo'), $r->url, null];
            }
            $known = [];
            foreach (array_slice($sources, 0, $imageLimit) as [$type, $id, $label, $url, $page]) {
                $r = $this->analyzeImage($type, $id, $label, (string) $url, $page, $known);
                if ($r === null) {
                    $n['image_skipped']++;

                    continue;
                }
                $n['image']++;
                $known[] = ['type' => $type, 'id' => $id, 'hash' => $r['suggestions']['dhash'] ?? null, 'label' => $label, 'file' => $r['suggestions']['file'] ?? ''];
            }
        }

        return $n;
    }

    // ── Rapports ────────────────────────────────────────────────────────────

    /** @param array<string,array{weight:int,detail:string}> $flags */
    private function report(string $type, int $id, string $kind, string $label, ?string $url, int $score, array $flags, array $suggest, string $hash): array
    {
        $labels = TextAnalyzer::LABELS + ImageAnalyzer::LABELS;
        $reasons = [];
        foreach ($flags as $code => $f) {
            $reasons[] = ['code' => $code, 'label' => $labels[$code] ?? $code, 'detail' => $f['detail'], 'weight' => $f['weight']];
        }
        usort($reasons, fn ($a, $b) => $b['weight'] <=> $a['weight']);

        return [
            'subject_type' => $type, 'subject_id' => $id, 'kind' => $kind, 'label' => Str::limit($label, 190), 'url' => $url,
            'score' => $score, 'verdict' => self::verdict($score), 'reasons' => $reasons, 'suggestions' => $suggest, 'content_hash' => $hash,
        ];
    }

    public function persist(array $r): void
    {
        $existing = DB::table('moderation_reports')->where(['subject_type' => $r['subject_type'], 'subject_id' => $r['subject_id'], 'kind' => $r['kind']])->first();
        $payload = [
            'label' => $r['label'], 'url' => $r['url'], 'score' => $r['score'], 'verdict' => $r['verdict'],
            'reasons' => json_encode($r['reasons'], JSON_UNESCAPED_UNICODE), 'suggestions' => json_encode($r['suggestions'], JSON_UNESCAPED_UNICODE),
            'content_hash' => $r['content_hash'], 'updated_at' => now(),
        ];
        if (! $existing) {
            DB::table('moderation_reports')->insert($payload + [
                'subject_type' => $r['subject_type'], 'subject_id' => $r['subject_id'], 'kind' => $r['kind'], 'status' => 'pending', 'created_at' => now(),
            ]);

            return;
        }
        // Contenu modifié depuis la dernière décision : le dossier repasse en attente.
        $changed = $existing->content_hash !== $r['content_hash'];
        DB::table('moderation_reports')->where('id', $existing->id)->update($payload + ($changed ? ['status' => 'pending', 'decided_by' => null, 'decided_at' => null] : []));
    }
}

<?php

namespace App\Services\Ai\Moderation;

use App\Services\Ai\TextVectorizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Détection de doublons et de quasi-doublons parmi les contenus de même nature (similarité TF-IDF). */
class DuplicateDetector
{
    public const NEAR_DUPLICATE = 0.90;

    /** @var array<string,array{ids:array<int,string>, vectors:array<int,array>, titles:array<int,string>, labels:array<int,string>}> */
    private array $corpora = [];

    /**
     * @return array{weight:int,detail:string,matches:array<int,array{id:int,label:string,similarity:float}>}|null
     */
    public function detect(string $type, int $id, string $title, string $text): ?array
    {
        $c = $this->corpus($type);
        if (! $c || count($c['vectors']) < 2) {
            return null;
        }
        $vec = $c['vectorizer']->vector(TextVectorizer::termFrequencies([[$title, 3], [$text, 1]]));
        $normTitle = $this->normTitle($title);
        $matches = [];
        $exact = false;
        foreach ($c['vectors'] as $otherId => $v) {
            if ($otherId === $id) {
                continue;
            }
            $sim = TextVectorizer::cosine($vec, $v);
            $sameTitle = $normTitle !== '' && $normTitle === ($c['titles'][$otherId] ?? null);
            if ($sim >= self::NEAR_DUPLICATE || $sameTitle) {
                $matches[] = ['id' => (int) $otherId, 'label' => $c['labels'][$otherId] ?? ('#'.$otherId), 'similarity' => round($sim, 3)];
                $exact = $exact || $sameTitle;
            }
        }
        if (! $matches) {
            return null;
        }
        usort($matches, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);

        return [
            'weight' => $exact ? 40 : 45,
            'detail' => ($exact ? 'même titre que « ' : 'très proche de « ').$matches[0]['label'].' »'.(count($matches) > 1 ? ' (+'.(count($matches) - 1).')' : ''),
            'matches' => array_slice($matches, 0, 3),
        ];
    }

    public function forget(): void
    {
        $this->corpora = [];
    }

    private function normTitle(string $t): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($t))));
    }

    private function corpus(string $type): ?array
    {
        if (isset($this->corpora[$type])) {
            return $this->corpora[$type];
        }
        $rows = match ($type) {
            'article' => DB::table('articles')->whereNull('deleted_at')->get(['id', 'title_fr as title', DB::raw("CONCAT(COALESCE(excerpt_fr,''),' ',COALESCE(content_fr,'')) as body")]),
            'review' => DB::table('reviews')->get(['id', DB::raw("COALESCE(title,'') as title"), DB::raw("COALESCE(comment,'') as body")])
                ->filter(fn ($r) => str_word_count(Str::ascii($r->body)) >= 8)->values(),
            'provider' => DB::table('providers')->whereNull('deleted_at')->get(['id', 'name as title', DB::raw("CONCAT(COALESCE(short_desc_fr,''),' ',COALESCE(description_fr,'')) as body")]),
            'event' => DB::table('events')->get(['id', 'title_fr as title', DB::raw("COALESCE(description_fr,'') as body")]),
            default => collect(),
        };
        if ($rows->count() < 2) {
            return $this->corpora[$type] = [];
        }

        $tfs = [];
        $titles = [];
        $labels = [];
        foreach ($rows as $r) {
            $tfs[$r->id] = TextVectorizer::termFrequencies([[$r->title, 3], [strip_tags((string) $r->body), 1]]);
            $titles[$r->id] = $this->normTitle((string) $r->title);
            $labels[$r->id] = Str::limit((string) ($r->title ?: strip_tags((string) $r->body)), 60);
        }
        $vectorizer = new TextVectorizer;
        $vectorizer->fit($tfs);

        return $this->corpora[$type] = [
            'vectorizer' => $vectorizer, 'vectors' => array_map(fn ($tf) => $vectorizer->vector($tf), $tfs),
            'titles' => $titles, 'labels' => $labels,
        ];
    }
}

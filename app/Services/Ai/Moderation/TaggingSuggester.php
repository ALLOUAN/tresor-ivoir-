<?php

namespace App\Services\Ai\Moderation;

use App\Services\Ai\Taxonomy;
use App\Services\Ai\TextVectorizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Étiquetage automatique des articles (suggestions seulement) :
 *  - rubrique : vote des 3 articles publiés les plus proches (TF-IDF) ;
 *  - étiquettes : celles dont les mots figurent dans le texte ;
 *  - centres d'intérêt reconnus.
 */
class TaggingSuggester
{
    private ?array $knn = null;

    /** @return array{category:?array{id:int,name:string,confidence:float},tags:array<int,array{id:int,name:string}>,interests:string[]} */
    public function suggest(int $articleId, string $title, string $text, ?int $currentCategoryId): array
    {
        $tf = TextVectorizer::termFrequencies([[$title, 3], [strip_tags($text), 1]]);
        $out = ['category' => null, 'tags' => [], 'interests' => []];

        // Rubrique
        $k = $this->knn();
        if ($k) {
            $vec = $k['vectorizer']->vector($tf);
            $sims = [];
            foreach ($k['vectors'] as $id => $v) {
                if ($id !== $articleId) {
                    $sims[$id] = TextVectorizer::cosine($vec, $v);
                }
            }
            arsort($sims);
            $votes = [];
            foreach (array_slice($sims, 0, 3, true) as $id => $sim) {
                if ($sim > 0.12) {
                    $cat = $k['categories'][$id];
                    $votes[$cat] = ($votes[$cat] ?? 0) + $sim;
                }
            }
            if ($votes) {
                arsort($votes);
                $catId = array_key_first($votes);
                $total = array_sum($votes);
                $out['category'] = [
                    'id' => (int) $catId, 'name' => $k['names'][$catId] ?? ('#'.$catId),
                    'confidence' => round($votes[$catId] / $total, 2), 'differs' => $currentCategoryId !== null && (int) $catId !== $currentCategoryId,
                ];
            }
        }

        // Étiquettes
        $tokens = array_flip(array_keys($tf));
        foreach (DB::table('tags')->whereIn('type', ['article', 'shared'])->get(['id', 'name_fr']) as $tag) {
            $parts = TextVectorizer::tokens($tag->name_fr);
            if ($parts && count(array_filter($parts, fn ($p) => isset($tokens[$p]))) === count($parts)) {
                $out['tags'][] = ['id' => (int) $tag->id, 'name' => $tag->name_fr];
            }
        }
        $out['tags'] = array_slice($out['tags'], 0, 5);

        // Centres d'intérêt
        foreach (Taxonomy::INTERESTS as $key => $def) {
            $hits = 0;
            foreach ($def['keywords'] as $kw) {
                $stem = strlen($kw) > 4 && str_ends_with($kw, 's') ? substr($kw, 0, -1) : $kw;
                $hits += $tf[$stem] ?? 0;
            }
            if ($hits >= 3) {
                $out['interests'][$key] = $hits;
            }
        }
        arsort($out['interests']);
        $out['interests'] = array_slice(array_keys($out['interests']), 0, 3);

        return $out;
    }

    private function knn(): array
    {
        if ($this->knn !== null) {
            return $this->knn;
        }
        $rows = DB::table('articles')->whereNull('deleted_at')->whereNotNull('category_id')->where('status', 'published')
            ->get(['id', 'title_fr', 'excerpt_fr', 'content_fr', 'category_id']);
        if ($rows->count() < 4) {
            return $this->knn = [];
        }
        $names = DB::table('article_categories')->pluck('name_fr', 'id')->all();
        $tfs = [];
        $cats = [];
        foreach ($rows as $r) {
            $tfs[$r->id] = TextVectorizer::termFrequencies([[$r->title_fr, 3], [strip_tags($r->excerpt_fr.' '.$r->content_fr), 1]]);
            $cats[$r->id] = $r->category_id;
        }
        $v = new TextVectorizer;
        $v->fit($tfs);

        return $this->knn = ['vectorizer' => $v, 'vectors' => array_map(fn ($tf) => $v->vector($tf), $tfs), 'categories' => $cats, 'names' => $names];
    }
}

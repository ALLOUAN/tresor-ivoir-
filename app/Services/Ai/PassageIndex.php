<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Index des passages de texte du site, interrogé par l'assistant : fiches (description, prix,
 * chambres, horaires), articles, pages d'information (FAQ, guide), cultures et villes.
 * Chaque passage garde sa source (titre, adresse) pour être cité.
 */
class PassageIndex
{
    private const CACHE_KEY = 'ai.passages.v1';

    private ?array $snap = null;

    /** @var array<string,string> horaires lisibles par fiche, remplis par entityTexts() */
    private array $schedules = [];

    public function __construct(private readonly Catalog $catalog) {}

    /** @return array{passages:array<int,array>,vectors:array<int,array>,idf:array<string,float>} */
    public function snapshot(): array
    {
        return $this->snap ??= Cache::remember(self::CACHE_KEY, now()->addMinutes((int) config('ai.cache_minutes', 10)), fn () => $this->build());
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function vectorizer(): TextVectorizer
    {
        $idf = $this->snapshot()['idf'];

        return TextVectorizer::fromExport(['idf' => $idf, 'default' => log(1 + max(1, count($this->snapshot()['passages'])))]);
    }

    // ── Construction ────────────────────────────────────────────────────────

    private function build(): array
    {
        $passages = [];
        $add = function (string $src, string $type, string $typeLabel, string $title, string $url, string $text, ?string $image = null) use (&$passages) {
            foreach ($this->chunk($text) as $chunk) {
                $passages[] = ['src' => $src, 'type' => $type, 'type_label' => $typeLabel, 'title' => $title, 'url' => $url, 'text' => $chunk, 'image' => $image];
            }
        };

        // Pages d'information (FAQ : un passage par question)
        if (\App\Support\SchemaCache::hasTable('information_pages')) {
            foreach (DB::table('information_pages')->get(['slug', 'title_fr', 'body_fr']) as $p) {
                $plain = $this->plain((string) $p->body_fr);
                $type = $p->slug === 'faq' || $p->slug === 'user-guide' ? 'faq' : 'info';
                foreach (preg_split('/\n{2,}/', $plain) as $block) {
                    $block = trim($block);
                    if (str_word_count(Str::ascii($block)) >= 6) {
                        $passages[] = ['src' => 'info:'.$p->slug, 'type' => $type, 'type_label' => 'Aide', 'title' => $p->title_fr, 'url' => route('information.show', $p->slug), 'text' => $block, 'image' => null];
                    }
                }
            }
        }

        // Fiches du catalogue : faits structurés + descriptions
        $texts = $this->entityTexts();
        foreach ($this->catalog->items() as $key => $it) {
            $facts = [$it->typeLabel.' : '.$it->title.'.'];
            if ($it->city) {
                $facts[] = 'Ville : '.$it->city.'.';
            }
            if ($it->price !== null) {
                $facts[] = match ($it->type) {
                    'accommodation' => $it->price > 0 ? 'Prix : dès '.number_format($it->price, 0, ',', ' ').' FCFA la nuit.' : '',
                    'restaurant' => 'Repas moyen : environ '.number_format($it->price, 0, ',', ' ').' FCFA.',
                    'site' => $it->price > 0 ? 'Entrée : '.number_format($it->price, 0, ',', ' ').' FCFA.' : 'Entrée gratuite.',
                    'event' => $it->price > 0 ? 'Tarif : '.number_format($it->price, 0, ',', ' ').' FCFA.' : 'Entrée gratuite.',
                    default => $it->price > 0 ? 'Tarif : dès '.number_format($it->price, 0, ',', ' ').' FCFA.' : '',
                };
            }
            if (! empty($it->extra['rooms'])) {
                $facts[] = 'Chambres : '.collect($it->extra['rooms'])->map(fn ($r) => $r['name'].' ('.number_format($r['price'], 0, ',', ' ').' FCFA la nuit)')->implode(' ; ').'.';
            }
            if ($it->type === 'event' && $it->startsAt) {
                $facts[] = 'Date : '.\Illuminate\Support\Carbon::createFromTimestamp($it->startsAt)->locale('fr')->translatedFormat('j F Y').($it->endsAt && date('Y-m-d', $it->endsAt) !== date('Y-m-d', $it->startsAt) ? ' au '.\Illuminate\Support\Carbon::createFromTimestamp($it->endsAt)->locale('fr')->translatedFormat('j F Y') : '').'.';
            }
            $add($key, $it->type, $it->typeLabel, $it->title, $it->url, trim(implode(' ', array_filter($facts)).' '.($texts[$key] ?? '')), $it->image);
            // Horaires : passage à part, pour qu'une question sur les horaires les retrouve directement.
            if (! empty($this->schedules[$key])) {
                $passages[] = ['src' => $key, 'type' => $it->type, 'type_label' => $it->typeLabel, 'title' => $it->title, 'url' => $it->url, 'text' => 'Horaires d\'ouverture de '.$it->title.' : '.$this->schedules[$key].'.', 'image' => $it->image];
            }
        }

        // Articles publiés
        foreach (DB::table('articles')->where('status', 'published')->whereNull('deleted_at')->get(['id', 'title_fr', 'slug_fr', 'excerpt_fr', 'content_fr', 'cover_url']) as $a) {
            $add('article:'.$a->id, 'article', 'Article', $a->title_fr, route('articles.show', $a->slug_fr), $this->plain(trim($a->excerpt_fr.' '.$a->content_fr)), $a->cover_url);
        }

        // Cultures, éléments culturels, villes
        if (\App\Support\SchemaCache::hasTable('cultural_peoples')) {
            foreach (DB::table('cultural_peoples')->where('is_active', 1)->get() as $r) {
                $add('people:'.$r->id, 'culture', 'Culture', $r->name, route('cultural.people', $r->slug), $this->plain(trim('Peuple '.$r->name.'. Zone : '.$r->zone_geographique.'. Langue : '.$r->langue_principale.'. '.$r->description.' '.$r->histoire.' '.$r->symboles)), $r->thumbnail);
            }
            foreach (DB::table('cultural_elements')->where('is_active', 1)->whereNull('deleted_at')->get() as $r) {
                $add('element:'.$r->id, 'culture', 'Culture', $r->name, route('cultural.element', $r->slug), $this->plain(trim($r->short_description.' '.$r->description.' '.$r->origine_historique.' '.$r->practical_info.($r->meilleure_periode ? ' Meilleure période : '.$r->meilleure_periode.'.' : ''))), $r->thumbnail);
            }
        }
        foreach (DB::table('tourist_cities')->where('is_active', 1)->get() as $r) {
            $add('city:'.$r->id, 'city', 'Destination', $r->name, route('tourist.city', $r->slug), $this->plain('Ville de '.$r->name.' ('.$r->district.', '.$r->region_administrative.'). '.$r->description), $r->thumbnail);
        }

        // Vectorisation
        $tfs = [];
        foreach ($passages as $i => &$p) {
            $p['id'] = $i;
            $tfs[$i] = TextVectorizer::termFrequencies([[$p['title'], 2], [$p['type_label'], 0.5], [$p['text'], 1]]);
        }
        unset($p);
        $v = new TextVectorizer;
        $v->fit($tfs);

        return ['passages' => $passages, 'vectors' => array_map(fn ($tf) => $v->vector($tf), $tfs), 'idf' => $v->exportIdf()['idf']];
    }

    /** Textes longs des fiches : clé « type:id » => texte. */
    private function entityTexts(): array
    {
        $out = [];
        $map = [
            'accommodation' => ['accommodations', ['description', 'short_description', 'cancellation_policy', 'quartier', 'adresse'], fn ($r) => ($r->check_in_time ? ' Arrivée à partir de '.$r->check_in_time.'.' : '').($r->check_out_time ? ' Départ avant '.$r->check_out_time.'.' : '')],
            'restaurant' => ['restaurants', ['description', 'short_description', 'quartier', 'adresse'], null],
            'leisure' => ['leisure_venues', ['description', 'short_description', 'quartier', 'adresse'], null],
            'experience' => ['tourist_experiences', ['description', 'short_description', 'quartier', 'adresse'], null],
            'agency' => ['travel_agencies', ['description', 'short_description', 'quartier', 'adresse'], null],
            'transport' => ['transport_companies', ['description', 'short_description', 'quartier', 'adresse'], null],
            'site' => ['tourist_sites', ['description', 'short_description', 'practical_info', 'acces_description', 'point_repere', 'localite'], function ($r) {
                if ($r->schedules) {
                    $this->schedules['site:'.$r->id] = $this->schedule($r->schedules);
                }

                return '';
            }],
            'event' => ['events', ['subtitle_fr', 'description_fr', 'program', 'location_name', 'address'], null],
            'provider' => ['providers', ['short_desc_fr', 'description_fr', 'address'], null],
        ];
        foreach ($map as $type => [$table, $cols, $extra]) {
            foreach (DB::table($table)->get() as $r) {
                $parts = [];
                foreach ($cols as $c) {
                    $v = $this->plain((string) ($r->$c ?? ''));
                    if ($v !== '') {
                        $parts[] = $v;
                    }
                }
                $out[$type.':'.$r->id] = trim(implode(' ', $parts).($extra ? $extra($r) : ''));
            }
        }

        return $out;
    }

    /** « lundi fermé ; du mardi au vendredi de 09:00 à 17:00 ; … » : jours consécutifs identiques regroupés. */
    private function schedule(mixed $v): string
    {
        $days = is_string($v) ? json_decode($v, true) : $v;
        if (! is_array($days) || ! isset($days[0]['day'])) {
            return $this->flat($v);
        }
        $rows = [];
        foreach ($days as $d) {
            $label = ! empty($d['closed']) ? 'fermé' : 'de '.($d['opens'] ?? '?').' à '.($d['closes'] ?? '?');
            if ($rows && end($rows)['label'] === $label) {
                $rows[array_key_last($rows)]['to'] = mb_strtolower($d['day']);
            } else {
                $rows[] = ['from' => mb_strtolower($d['day']), 'to' => null, 'label' => $label];
            }
        }

        return implode(' ; ', array_map(fn ($r) => ($r['to'] ? 'du '.$r['from'].' au '.$r['to'] : $r['from']).' '.$r['label'], $rows));
    }

    private function flat(mixed $v): string
    {
        $d = is_string($v) ? (json_decode($v, true) ?? $v) : $v;
        if (is_string($d)) {
            return $d;
        }
        $out = [];
        array_walk_recursive($d, function ($x, $k) use (&$out) {
            if (is_scalar($x) && $x !== '' && $x !== null) {
                $out[] = (is_string($k) ? $k.' ' : '').$x;
            }
        });

        return implode(', ', $out);
    }

    /** Texte brut : balises retirées, retours de ligne conservés entre blocs. */
    private function plain(string $html): string
    {
        // Champs stockés en JSON (infos pratiques : [{"label": "...", "value": "..."}]) : on les lit comme du texte.
        $trim = ltrim($html);
        if ($trim !== '' && in_array($trim[0], ['[', '{'], true) && is_array($data = json_decode($trim, true))) {
            $parts = [];
            foreach ($data as $row) {
                if (is_array($row) && isset($row['label'], $row['value'])) {
                    $parts[] = $row['label'].' : '.$row['value'].'.';
                } elseif (is_array($row)) {
                    $parts[] = $this->flat($row).'.';
                } elseif (is_scalar($row)) {
                    $parts[] = (string) $row.'.';
                }
            }

            return implode(' ', $parts);
        }

        $t = preg_replace('~</(p|li|h[1-6]|div|tr)>|<br\s*/?>~i', "\n\n", $html);
        $t = html_entity_decode(strip_tags((string) $t), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace("/[ \t]+/", ' ', preg_replace("/\n{3,}/", "\n\n", $t)));
    }

    /** Découpe en passages d'environ 70 mots, à la phrase près. @return string[] */
    private function chunk(string $text): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if ($text === '') {
            return [];
        }
        $max = (int) config('ai.chat.passage_words', 70);
        $sentences = preg_split('/(?<=[.!?…])\s+(?=\p{Lu}|\d)/u', $text) ?: [$text];
        $out = [];
        $cur = '';
        foreach ($sentences as $s) {
            if ($cur !== '' && str_word_count(Str::ascii($cur.' '.$s)) > $max) {
                $out[] = $cur;
                $cur = '';
            }
            $cur = trim($cur.' '.$s);
        }
        if ($cur !== '') {
            $out[] = $cur;
        }

        return array_values(array_filter($out, fn ($c) => str_word_count(Str::ascii($c)) >= 4));
    }
}

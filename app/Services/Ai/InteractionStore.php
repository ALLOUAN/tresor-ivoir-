<?php

namespace App\Services\Ai;

use App\Models\Event;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Signaux de comportement : consultations enregistrées par le site, favoris et réservations
 * lus dans leurs tables d'origine. Un « acteur » est un visiteur connecté (u:ID) ou une
 * session anonyme (s:empreinte) : aucune donnée nominative n'est conservée pour les anonymes.
 */
class InteractionStore
{
    public function actorKey(Request $request): ?string
    {
        if ($request->user()) {
            return 'u:'.$request->user()->id;
        }
        if (! $request->hasSession()) {
            return null;
        }

        return 's:'.substr(hash_hmac('sha256', $request->session()->getId(), (string) config('app.key')), 0, 16);
    }

    public function record(string $actor, string $itemKey, string $action = 'view'): void
    {
        if (! \App\Support\SchemaCache::hasTable('ai_interactions') || ! str_contains($itemKey, ':')) {
            return;
        }
        [$type, $id] = explode(':', $itemKey, 2);
        $isUser = str_starts_with($actor, 'u:');
        $col = $isUser ? 'user_id' : 'session_key';
        $val = substr($actor, 2);

        // Une même consultation n'est comptée qu'une fois par demi-heure.
        $recent = DB::table('ai_interactions')->where($col, $val)->where('item_type', $type)->where('item_id', $id)
            ->where('action', $action)->where('created_at', '>', now()->subMinutes(30))->exists();
        if ($recent) {
            return;
        }

        DB::table('ai_interactions')->insert([
            'user_id' => $isUser ? (int) $val : null,
            'session_key' => $isUser ? null : $val,
            'item_type' => $type, 'item_id' => (int) $id, 'action' => $action,
            'is_simulated' => false, 'created_at' => now(),
        ]);
    }

    /** @return array<string,float> itemKey => poids cumulé (plafonné) */
    public function history(?string $actor): array
    {
        if ($actor === null) {
            return [];
        }

        return $this->matrix()[$actor] ?? [];
    }

    /**
     * Matrice acteur × fiche. Les poids de plusieurs signaux sur une même fiche s'additionnent
     * (plafond 6) : une consultation vaut 1, un favori 3, une réservation 5.
     *
     * @return array<string,array<string,float>>
     */
    public function matrix(bool $withSimulated = true): array
    {
        $w = config('ai.recommender.action_weights');
        $m = [];
        $add = function (string $actor, string $key, float $weight) use (&$m) {
            $m[$actor][$key] = min(6.0, ($m[$actor][$key] ?? 0) + $weight);
        };

        if (\App\Support\SchemaCache::hasTable('ai_interactions')) {
            $q = DB::table('ai_interactions');
            if (! $withSimulated) {
                $q->where('is_simulated', false);
            }
            foreach ($q->get(['user_id', 'session_key', 'item_type', 'item_id', 'action']) as $r) {
                $actor = $r->user_id ? 'u:'.$r->user_id : 's:'.$r->session_key;
                $add($actor, $r->item_type.':'.$r->item_id, (float) ($w[$r->action] ?? 1.0));
            }
        }

        if (\App\Support\SchemaCache::hasTable('visitor_favorites')) {
            foreach (DB::table('visitor_favorites')->get(['user_id', 'favoritable_type', 'favoritable_id']) as $f) {
                $type = match ($f->favoritable_type) {
                    Provider::class => 'provider',
                    Event::class => 'event',
                    default => null,
                };
                if ($type) {
                    $add('u:'.$f->user_id, $type.':'.$f->favoritable_id, (float) $w['favorite']);
                }
            }
        }

        if (\App\Support\SchemaCache::hasTable('reservations')) {
            foreach (DB::table('reservations')->whereNotNull('user_id')->whereNotIn('status', ['cancelled', 'canceled'])->get(['user_id', 'accommodation_id']) as $r) {
                if ($r->accommodation_id) {
                    $add('u:'.$r->user_id, 'accommodation:'.$r->accommodation_id, (float) $w['reserve']);
                }
            }
        }

        return $m;
    }

    public function clear(?string $actor): void
    {
        if ($actor === null || ! \App\Support\SchemaCache::hasTable('ai_interactions')) {
            return;
        }
        $isUser = str_starts_with($actor, 'u:');
        DB::table('ai_interactions')->where($isUser ? 'user_id' : 'session_key', substr($actor, 2))->where('is_simulated', false)->delete();
    }
}

<?php

namespace App\Services\Ai;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Profil déclaré (questionnaire de 3 questions) : centres d'intérêt, budget, type de voyage.
 * Visiteur connecté : table ai_profiles. Visiteur anonyme : session uniquement.
 */
class ProfileStore
{
    public const SESSION_KEY = 'ai.profile';

    public function get(Request $request): array
    {
        $empty = ['interests' => [], 'budget' => null, 'travel_type' => null, 'home_city_id' => null];

        if ($request->user() && \App\Support\SchemaCache::hasTable('ai_profiles')) {
            $row = DB::table('ai_profiles')->where('user_id', $request->user()->id)->first();
            if ($row) {
                return [
                    'interests' => Taxonomy::cleanInterests(json_decode((string) $row->interests, true)),
                    'budget' => $row->budget, 'travel_type' => $row->travel_type,
                    'home_city_id' => $row->home_city_id ? (int) $row->home_city_id : null,
                ];
            }
        }

        return array_merge($empty, $request->hasSession() ? (array) $request->session()->get(self::SESSION_KEY, []) : []);
    }

    public function isEmpty(array $profile): bool
    {
        return ! $profile['interests'] && ! $profile['budget'] && ! $profile['travel_type'];
    }

    public function save(Request $request, array $data): array
    {
        $profile = [
            'interests' => Taxonomy::cleanInterests($data['interests'] ?? []),
            'budget' => isset($data['budget'], Taxonomy::BUDGETS[$data['budget']]) ? $data['budget'] : null,
            'travel_type' => isset($data['travel_type'], Taxonomy::TRAVEL_TYPES[$data['travel_type']]) ? $data['travel_type'] : null,
            'home_city_id' => ! empty($data['home_city_id']) ? (int) $data['home_city_id'] : null,
        ];

        if ($request->user() && \App\Support\SchemaCache::hasTable('ai_profiles')) {
            DB::table('ai_profiles')->updateOrInsert(['user_id' => $request->user()->id], [
                'interests' => json_encode($profile['interests']), 'budget' => $profile['budget'],
                'travel_type' => $profile['travel_type'], 'home_city_id' => $profile['home_city_id'],
                'updated_at' => now(), 'created_at' => now(),
            ]);
        } elseif ($request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, $profile);
        }

        return $profile;
    }

    public function clear(Request $request): void
    {
        if ($request->user() && \App\Support\SchemaCache::hasTable('ai_profiles')) {
            DB::table('ai_profiles')->where('user_id', $request->user()->id)->delete();
        }
        if ($request->hasSession()) {
            $request->session()->forget(self::SESSION_KEY);
        }
    }
}

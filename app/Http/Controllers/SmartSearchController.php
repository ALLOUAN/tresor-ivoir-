<?php

namespace App\Http\Controllers;

use App\Services\Ai\Recommendations;
use App\Services\Ai\SmartSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Module 3 : recherche intelligente en langage naturel (export JSON pour le mobile). */
class SmartSearchController extends Controller
{
    public function json(Request $request, SmartSearch $search): JsonResponse
    {
        abort_unless(Recommendations::enabled(), 404);
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['query' => $q, 'results' => []]);
        }
        $r = $search->search($q, max(1, min(20, (int) $request->query('k', 8))));

        return response()->json([
            'query' => $q,
            'did_you_mean' => $r['did_you_mean'],
            'understood' => [
                'city' => $r['interpreted']['city']['name'] ?? null,
                'type' => $r['interpreted']['type'],
                'price' => $r['interpreted']['price'],
                'translated' => $r['interpreted']['translated'],
            ],
            'results' => array_map(fn ($x) => [
                'key' => $x['item']->key, 'type' => $x['item']->type, 'type_label' => $x['item']->typeLabel,
                'title' => $x['item']->title, 'city' => $x['item']->city, 'url' => $x['item']->url, 'image' => $x['item']->image,
                'price_xof' => $x['item']->price, 'score' => $x['score'], 'why' => $x['why'],
            ], $r['results']),
        ]);
    }
}

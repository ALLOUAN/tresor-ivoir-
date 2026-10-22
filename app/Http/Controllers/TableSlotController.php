<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Services\TableSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Créneaux de table disponibles pour une date (formulaire de réservation du restaurant). */
class TableSlotController extends Controller
{
    public function __invoke(Request $request, Restaurant $restaurant, TableSlots $slots): JsonResponse
    {
        abort_unless($restaurant->is_active && $slots->enabled($restaurant), 404);
        $v = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today', 'before:+1 year'],
            'party' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);
        $party = (int) ($v['party'] ?? 2);

        return response()->json([
            'success' => true,
            'auto_confirm' => (bool) $restaurant->auto_confirm,
            'slots' => array_map(fn ($s) => $s + ['available' => $s['remaining'] >= $party], $slots->day($restaurant, $v['date'])),
        ]);
    }
}

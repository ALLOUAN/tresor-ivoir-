<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Services\TableSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Plan de salle pour le formulaire de réservation : tables libres, réservées (grisées) ou trop petites. */
class TablePlanController extends Controller
{
    public function __invoke(Request $request, Restaurant $restaurant, TableSlots $slots): JsonResponse
    {
        abort_unless($restaurant->is_active, 404);
        $v = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today', 'before:+1 year'],
            'time' => ['nullable', 'date_format:H:i'],
            'party' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        return response()->json([
            'success' => true,
            'duration' => $slots->tableDuration($restaurant),
            'tables' => $slots->tablePlan($restaurant, $v['date'], $v['time'] ?? null, (int) ($v['party'] ?? 2)),
        ]);
    }
}

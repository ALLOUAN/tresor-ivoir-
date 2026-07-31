<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'room_name' => ['required', 'string', 'max:255'],
            'room_price_xof' => ['nullable', 'integer', 'min:0'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms_count' => ['required', 'integer', 'min:1', 'max:20'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $accommodation = Accommodation::findOrFail($validated['accommodation_id']);

        $checkIn = new \DateTimeImmutable($validated['check_in']);
        $checkOut = new \DateTimeImmutable($validated['check_out']);
        $nights = max(1, $checkIn->diff($checkOut)->days);

        $totalXof = ! empty($validated['room_price_xof'])
            ? $validated['room_price_xof'] * $nights * $validated['rooms_count']
            : null;

        Reservation::query()->create([
            'accommodation_id' => $accommodation->id,
            'accommodation_name' => $accommodation->name,
            'room_name' => $validated['room_name'],
            'room_price_xof' => $validated['room_price_xof'] ?? null,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'nights' => $nights,
            'rooms_count' => $validated['rooms_count'],
            'guests_count' => $validated['guests_count'],
            'total_xof' => $totalXof,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => Reservation::STATUS_NEW,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Votre demande de réservation a bien été envoyée. Notre équipe vous contactera rapidement.',
        ]);
    }
}

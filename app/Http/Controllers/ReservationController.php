<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Périodes déjà bloquées pour une chambre — alimente le calendrier de
     * disponibilité côté client (dates grisées/non sélectionnables). Accepte
     * room_id (préféré, cible une chambre précise même si plusieurs portent
     * le même nom) ou room_name à défaut (repli pour les liens existants).
     */
    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'room_id' => ['nullable', 'string', 'max:40'],
            'room_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['room_id']) && empty($validated['room_name'])) {
            return response()->json(['success' => false, 'message' => 'room_id ou room_name requis.'], 422);
        }

        $accommodation = Accommodation::findOrFail($validated['accommodation_id']);

        $room = ! empty($validated['room_id'])
            ? $accommodation->findRoomById($validated['room_id'])
            : $accommodation->findRoomByName((string) $validated['room_name']);

        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Chambre introuvable pour cet hébergement.'], 404);
        }

        $blocked = Reservation::blockedRanges(
            $accommodation->id,
            (string) $room['name'],
            $room['id'] ?? null,
            Reservation::roomUnits($room)
        );

        $blocked = collect($blocked)->merge(Reservation::closedRanges($room))->sortBy('check_in')->values()->all();

        return response()->json([
            'success' => true,
            'blocked_ranges' => $blocked,
        ]);
    }

    /** Devis d'un séjour (tarifs saisonniers, week-end, remises) affiché avant de réserver. */
    public function quote(Request $request, \App\Services\ReservationPricingService $pricing, \App\Services\RoomRates $rates): JsonResponse
    {
        $v = $request->validate([
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'room_id' => ['nullable', 'string', 'max:40'],
            'room_name' => ['nullable', 'string', 'max:255'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in', 'before:+2 years'],
            'rooms_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'promo_code' => ['nullable', 'string', 'max:30'],
        ]);
        $a = Accommodation::findOrFail($v['accommodation_id']);
        $room = ! empty($v['room_id']) ? $a->findRoomById($v['room_id']) : $a->findRoomByName((string) ($v['room_name'] ?? ''));
        if (! $room || (int) ($room['price_xof'] ?? 0) <= 0) {
            return response()->json(['success' => false], 404);
        }
        $q = $rates->quote($room, $v['check_in'], $v['check_out'], (int) ($v['rooms_count'] ?? 1));
        // Code promo de l'établissement : déduit du total, l'acompte suit.
        $p = app(\App\Services\ProviderPromoService::class)->check($a->provider_id, $v['promo_code'] ?? null, $q['total']);
        $q['promo_error'] = $p['error'];
        $q['promo_discount_xof'] = $p['discount'];
        $q['promo_label'] = $p['promo']?->label();
        $q['total'] -= $p['discount'];
        $calc = $pricing->compute($v['check_in'], $v['check_out'], (int) ($v['rooms_count'] ?? 1), (int) $room['price_xof'], $q['total']);

        return response()->json(['success' => true] + $q + ['deposit' => (int) $calc['deposit_xof']]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'room_id' => ['nullable', 'string', 'max:40'],
            'room_name' => ['required', 'string', 'max:255'],
            'room_price_xof' => ['nullable', 'integer', 'min:0'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms_count' => ['required', 'integer', 'min:1', 'max:20'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
            'promo_code' => ['nullable', 'string', 'max:30'],
        ]);

        $accommodation = Accommodation::findOrFail($validated['accommodation_id']);

        $room = ! empty($validated['room_id'])
            ? $accommodation->findRoomById($validated['room_id'])
            : $accommodation->findRoomByName($validated['room_name']);
        if (! $room) {
            return response()->json([
                'success' => false,
                'message' => 'Chambre introuvable pour cet hébergement.',
            ], 422);
        }
        // Le prix vient toujours de la donnée serveur, jamais de celui soumis par le client :
        // une chambre sans tarif n'est pas réservable en ligne.
        if ((int) ($room['price_xof'] ?? 0) <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Cette chambre n'est pas réservable en ligne : son tarif n'est pas défini.",
            ], 422);
        }
        $roomName = (string) $room['name'];
        $roomId = $room['id'] ?? null;
        $units = Reservation::roomUnits($room);
        if (Reservation::roomClosedDuring($room, $validated['check_in'], $validated['check_out'])) {
            return response()->json([
                'success' => false,
                'message' => "L'établissement a fermé cette chambre à la vente sur une partie de ces dates. Merci de choisir d'autres dates.",
            ], 422);
        }
        $validated['room_price_xof'] = (int) $room['price_xof'];

        // Verrou + vérification + création dans la même transaction : sans cela, deux
        // requêtes concurrentes pourraient chacune passer le contrôle hasConflict() avant
        // que l'une des deux n'ait committé sa réservation, créant un double-booking. Le
        // verrou porte sur la ligne accommodation (granularité simple mais suffisante ici :
        // elle sérialise les tentatives de réservation d'un même hébergement, sans bloquer
        // celles des autres hébergements).
        $promoCheck = app(\App\Services\ProviderPromoService::class)->check(
            $accommodation->provider_id,
            $validated['promo_code'] ?? null,
            (int) app(\App\Services\RoomRates::class)->quote($room, $validated['check_in'], $validated['check_out'], (int) $validated['rooms_count'])['total'],
        );
        if ($promoCheck['error']) {
            return response()->json(['success' => false, 'message' => $promoCheck['error']], 422);
        }
        $promo = $promoCheck['promo'];

        $result = DB::transaction(function () use ($accommodation, $room, $roomName, $roomId, $units, $validated, $promo) {
            Accommodation::whereKey($accommodation->id)->lockForUpdate()->first();

            if (Reservation::hasConflict($accommodation->id, $roomName, $validated['check_in'], $validated['check_out'], null, $roomId, $units, (int) $validated['rooms_count'])) {
                return null;
            }

            $checkIn = new \DateTimeImmutable($validated['check_in']);
            $checkOut = new \DateTimeImmutable($validated['check_out']);
            $nights = max(1, $checkIn->diff($checkOut)->days);

            $totalXof = ! empty($validated['room_price_xof'])
                ? app(\App\Services\RoomRates::class)->quote($room, $validated['check_in'], $validated['check_out'], (int) $validated['rooms_count'])['total']
                : null;
            $discount = $promo && $totalXof ? $promo->discountFor($totalXof) : 0;
            $totalXof = $totalXof !== null ? $totalXof - $discount : null;
            if ($promo && ! app(\App\Services\ProviderPromoService::class)->consume($promo)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['promo_code' => __('Ce code promo vient d\'atteindre son nombre maximal d\'utilisations.')]);
            }

            return Reservation::query()->create([
                'user_id' => Auth::id(),
                'accommodation_id' => $accommodation->id,
                'accommodation_name' => $accommodation->name,
                'provider_id' => $accommodation->provider_id,
                'room_name' => $roomName,
                'room_id' => $roomId,
                'room_price_xof' => $validated['room_price_xof'] ?? null,
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'nights' => $nights,
                'rooms_count' => $validated['rooms_count'],
                'guests_count' => $validated['guests_count'],
                'total_xof' => $totalXof,
                'promo_code' => $promo?->code,
                'promo_discount_xof' => $discount ?: null,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'message' => $validated['message'] ?? null,
                'status' => Reservation::STATUS_NEW,
            ]);
        });

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Ces dates ne sont plus disponibles pour cette chambre. Merci de choisir d\'autres dates.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Votre demande de réservation a bien été envoyée. Notre équipe vous contactera rapidement.',
        ]);
    }
}

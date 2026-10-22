<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Vue d'ensemble (lecture) des demandes de réservation restaurants, loisirs et circuits. */
class BookingRequestController extends Controller
{
    public function index(Request $request): View
    {
        $type = array_key_exists((string) $request->query('type'), BookingRequest::TYPES) ? $request->query('type') : null;
        $status = array_key_exists((string) $request->query('statut'), BookingRequest::STATUS_LABELS) ? $request->query('statut') : null;

        $bookings = BookingRequest::with('provider')
            ->when($type, fn ($q) => $q->where('bookable_type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $counts = BookingRequest::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.booking-requests.index', compact('bookings', 'counts', 'type', 'status'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) > 200) {
            $q = substr($q, 0, 200);
        }

        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(Reservation::statusOptions());
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $dateFrom = $this->sanitizeDate($request->query('date_from'));
        $dateTo = $this->sanitizeDate($request->query('date_to'));

        $base = Reservation::query();

        $stats = [
            'total' => (clone $base)->count(),
            'new' => (clone $base)->where('status', Reservation::STATUS_NEW)->count(),
            'confirmed' => (clone $base)->where('status', Reservation::STATUS_CONFIRMED)->count(),
            'cancelled' => (clone $base)->where('status', Reservation::STATUS_CANCELLED)->count(),
            'this_month' => (clone $base)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'commissions_xof' => (clone $base)->where('payment_status', Reservation::PAYMENT_DEPOSIT_PAID)->sum('commission_amount_xof'),
        ];

        $reservations = Reservation::query()
            ->search($q !== '' ? $q : null)
            ->statusFilter($status !== '' ? $status : null)
            ->createdBetween(
                is_string($dateFrom) && $dateFrom !== '' ? $dateFrom : null,
                is_string($dateTo) && $dateTo !== '' ? $dateTo : null,
            )
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.system.reservations.index', compact(
            'reservations',
            'stats',
            'q',
            'status',
            'dateFrom',
            'dateTo',
        ));
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load('payments', 'provider');

        return view('admin.system.reservations.show', compact('reservation'));
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Reservation::statusOptions()))],
        ]);

        $reservation->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.reservations.show', $reservation)
            ->with('success', 'Statut mis à jour.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Réservation supprimée.');
    }

    public function export(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'csv');
        if ($format !== 'csv') {
            abort(404);
        }

        $q = trim((string) $request->query('q', ''));
        if (strlen($q) > 200) {
            $q = substr($q, 0, 200);
        }

        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(Reservation::statusOptions());
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $dateFrom = $this->sanitizeDate($request->query('date_from'));
        $dateTo = $this->sanitizeDate($request->query('date_to'));

        $rows = Reservation::query()
            ->search($q !== '' ? $q : null)
            ->statusFilter($status !== '' ? $status : null)
            ->createdBetween(
                is_string($dateFrom) && $dateFrom !== '' ? $dateFrom : null,
                is_string($dateTo) && $dateTo !== '' ? $dateTo : null,
            )
            ->orderByDesc('id')
            ->get();

        $filename = 'reservations-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['id', 'date', 'statut', 'paiement', 'hebergement', 'chambre', 'arrivee', 'depart', 'nuits', 'chambres', 'personnes', 'total_xof', 'acompte_xof', 'commission_xof', 'nom', 'email', 'telephone'], ';');
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->created_at?->format('Y-m-d H:i:s'),
                    Reservation::STATUS_LABELS[$r->status] ?? $r->status,
                    Reservation::PAYMENT_STATUS_LABELS[$r->payment_status] ?? $r->payment_status,
                    $r->accommodation_name,
                    $r->room_name,
                    $r->check_in?->format('Y-m-d'),
                    $r->check_out?->format('Y-m-d'),
                    $r->nights,
                    $r->rooms_count,
                    $r->guests_count,
                    $r->total_xof,
                    $r->deposit_amount_xof,
                    $r->commission_amount_xof,
                    $r->full_name,
                    $r->email,
                    $r->phone,
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function sanitizeDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TouristVisit;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Supervision admin des visites de sites touristiques — même patron qu'Admin\ReservationController. */
class TouristVisitManagementController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(TouristVisit::STATUS_LABELS);
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $base = TouristVisit::query();

        $stats = [
            'total' => (clone $base)->count(),
            'paid' => (clone $base)->where('status', TouristVisit::STATUS_PAID)->count(),
            'cancelled' => (clone $base)->where('status', TouristVisit::STATUS_CANCELLED)->count(),
            'this_month' => (clone $base)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'commissions_xof' => (clone $base)->where('status', TouristVisit::STATUS_PAID)->sum('commission_amount_xof'),
        ];

        $visits = TouristVisit::query()
            ->with('experience', 'provider')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.system.tourist-visits.index', compact('visits', 'stats', 'status'));
    }

    public function show(TouristVisit $touristVisit): View
    {
        $touristVisit->load('experience', 'provider', 'session', 'user');

        return view('admin.system.tourist-visits.show', ['visit' => $touristVisit]);
    }

    public function update(Request $request, TouristVisit $touristVisit): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(TouristVisit::STATUS_LABELS))],
        ]);

        $touristVisit->update(['status' => $validated['status']]);

        return redirect()->route('admin.tourist-visits.show', $touristVisit)->with('success', 'Statut mis à jour.');
    }

    public function refund(Request $request, TouristVisit $touristVisit, WalletService $walletService): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($touristVisit->status !== TouristVisit::STATUS_PAID) {
            return back()->with('error', 'Cette visite n\'a pas de paiement à rembourser.');
        }

        $walletService->recordManualRefundForVisit($touristVisit, Auth::user(), $validated['note'] ?? null);

        if ($touristVisit->user_id && ($client = User::find($touristVisit->user_id))) {
            $client->notify(new VisitorSystemNotification(
                'Visite remboursée',
                'Votre réservation pour "'.$touristVisit->experience_name.'" a été remboursée.',
                route('tourist-experience.index'),
            ));
        }

        return back()->with('success', 'Remboursement enregistré.');
    }

    public function destroy(TouristVisit $touristVisit): RedirectResponse
    {
        $touristVisit->delete();

        return redirect()->route('admin.tourist-visits.index')->with('success', 'Visite supprimée.');
    }

    public function export(Request $request): StreamedResponse
    {
        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(TouristVisit::STATUS_LABELS);
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $rows = TouristVisit::query()
            ->with('experience')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->get();

        $filename = 'visites-touristiques-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['id', 'reference', 'date', 'statut', 'type_visite', 'avec_guide', 'date_visite', 'periode', 'participants', 'total_xof', 'commission_xof', 'nom', 'email', 'telephone'], ';');
            foreach ($rows as $v) {
                fputcsv($out, [
                    $v->id,
                    $v->reference,
                    $v->created_at?->format('Y-m-d H:i:s'),
                    TouristVisit::STATUS_LABELS[$v->status] ?? $v->status,
                    TouristVisit::TYPE_LABELS[$v->visit_type] ?? $v->visit_type,
                    $v->with_guide ? 'oui' : 'non',
                    ($v->session_date ?? $v->desired_date)?->format('Y-m-d'),
                    $v->session_time_label,
                    $v->participants_count,
                    $v->amount_total_xof,
                    $v->commission_amount_xof,
                    $v->full_name,
                    $v->email,
                    $v->phone,
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

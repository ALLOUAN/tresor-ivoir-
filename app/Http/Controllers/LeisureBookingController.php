<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\ActivityWaitlist;
use App\Models\LeisurePackage;
use App\Models\LeisurePass;
use App\Models\LeisurePassPurchase;
use App\Services\CinetPayService;
use App\Services\LeisureAvailability;
use App\Services\LeisureBookingService;
use App\Services\LeisurePaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Loisirs : page d'activité avec créneaux en temps réel, réservation immédiate (CinetPay, solde ou carte
 * multi-séances), formules anniversaire / groupe avec acompte, cartes, billet QR, « Mes activités »
 * (annulation, report, liste d'attente).
 */
class LeisureBookingController extends Controller
{
    private function activity(string $slug): Activity
    {
        return Activity::available()->where('slug', $slug)->with(['category', 'resources'])->firstOrFail();
    }

    public function show(Request $request, string $slug): View
    {
        $activity = $this->activity($slug);
        $place = $activity->place();
        $packages = LeisurePackage::where('provider_id', $activity->provider_id)->where('is_active', true)->orderBy('weekday_price_xof')->get();
        $passes = LeisurePass::where('provider_id', $activity->provider_id)->where('is_active', true)->orderBy('price_xof')->get()
            ->filter(fn (LeisurePass $p) => $p->coversActivity($activity->id))->values();
        $others = Activity::available()->where('provider_id', $activity->provider_id)->whereKeyNot($activity->id)->orderBy('sort_order')->take(4)->get();
        $myPasses = Auth::check()
            ? LeisurePassPurchase::where('user_id', Auth::id())->where('status', LeisurePassPurchase::STATUS_ACTIVE)->where('provider_id', $activity->provider_id)->get()
                ->filter(fn ($p) => (empty($p->activity_ids) || in_array($activity->id, array_map('intval', $p->activity_ids), true)) && ($p->remaining() === null || $p->remaining() > 0) && (! $p->expires_on || ! $p->expires_on->isPast()))->values()
            : collect();

        return view('leisure.activity', compact('activity', 'place', 'packages', 'passes', 'others', 'myPasses') + [
            'preDate' => $request->query('date'), 'preTime' => $request->query('heure'),
        ]);
    }

    public function slots(Request $request, string $slug, LeisureAvailability $availability): JsonResponse
    {
        $activity = $this->activity($slug);
        $date = $request->validate(['date' => ['required', 'date_format:Y-m-d']])['date'];

        return response()->json(['date' => $date, 'slots' => $availability->slotsFor($activity, $date), 'capacity' => $activity->slot_capacity]);
    }

    public function book(Request $request, string $slug, LeisureBookingService $service, LeisurePaymentService $payments, CinetPayService $cinetPay): RedirectResponse
    {
        $activity = $this->activity($slug);
        abort_unless(Auth::user()->role === 'visitor', 403, __('Les réservations se font avec un compte visiteur.'));
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'string', 'max:5'],
            'tiers' => ['required', 'array', 'max:12'],
            'tiers.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'addons' => ['nullable', 'array', 'max:12'],
            'addons.*' => ['integer', 'min:0', 'max:11'],
            'payment_method' => ['required', 'in:cinetpay,wallet,pass'],
            'pass_id' => ['nullable', 'integer'],
            'waiver_names' => ['nullable', 'array', 'max:50'],
            'waiver_names.*' => ['nullable', 'string', 'max:120'],
            'waiver_accept' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);
        $user = Auth::user();
        try {
            $booking = $service->bookActivity($activity, $user, [
                'date' => $data['date'], 'time' => $data['time'], 'tiers' => array_map('intval', $data['tiers']), 'addons' => $data['addons'] ?? [],
                'payment_method' => $data['payment_method'], 'pass_id' => $data['pass_id'] ?? null,
                'waiver_names' => $data['waiver_names'] ?? [], 'waiver_accept' => $request->boolean('waiver_accept'),
                'full_name' => trim($user->first_name.' '.$user->last_name) ?: $user->email, 'email' => $user->email,
                'phone' => ($data['phone'] ?? null) ?: $user->phone, 'message' => $data['message'] ?? null, 'ip' => $request->ip(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['booking' => $e->getMessage()], 'leisure');
        }

        if ($booking->status === ActivityBooking::STATUS_PAID) {
            $service->afterPayment($booking);

            return redirect()->to($booking->ticketUrl());
        }

        return $this->startPayment($booking, $data['payment_method'], $payments, $cinetPay, fn () => $service->afterPayment($booking));
    }

    public function package(string $slug): View
    {
        $package = LeisurePackage::where('slug', $slug)->where('is_active', true)->with('resource')->firstOrFail();
        $place = \App\Models\LeisureVenue::where('provider_id', $package->provider_id)->where('is_active', true)->first();

        return view('leisure.package', compact('package', 'place'));
    }

    public function packageSlots(Request $request, string $slug, LeisureAvailability $availability): JsonResponse
    {
        $package = LeisurePackage::where('slug', $slug)->where('is_active', true)->with('resource')->firstOrFail();
        $date = $request->validate(['date' => ['required', 'date_format:Y-m-d']])['date'];
        $day = \Carbon\Carbon::parse($date);

        return response()->json(['slots' => $availability->packageSlotsFor($package, $date), 'base_price' => $package->basePriceFor($day), 'weekend' => $day->isWeekend()]);
    }

    public function bookPackage(Request $request, string $slug, LeisureBookingService $service, LeisurePaymentService $payments, CinetPayService $cinetPay): RedirectResponse
    {
        $package = LeisurePackage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_unless(Auth::user()->role === 'visitor', 403, __('Les réservations se font avec un compte visiteur.'));
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after:today'],
            'time' => ['required', 'string', 'max:5'],
            'participants' => ['required', 'integer', 'min:1', 'max:500'],
            'celebrant_name' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['required', 'in:cinetpay,wallet'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);
        $user = Auth::user();
        try {
            $booking = $service->bookPackage($package, $user, $data + ['full_name' => trim($user->first_name.' '.$user->last_name) ?: $user->email, 'email' => $user->email]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['booking' => $e->getMessage()], 'leisure');
        }

        return $this->startPayment($booking, $data['payment_method'], $payments, $cinetPay, fn () => $service->afterPayment($booking));
    }

    public function buyPass(Request $request, LeisurePass $leisurePass, LeisurePaymentService $payments, CinetPayService $cinetPay): RedirectResponse
    {
        abort_unless($leisurePass->is_active, 404);
        abort_unless(Auth::user()->role === 'visitor', 403, __('Les achats se font avec un compte visiteur.'));
        $method = $request->validate(['payment_method' => ['required', 'in:cinetpay,wallet']])['payment_method'];
        $purchase = LeisurePassPurchase::create([
            'leisure_pass_id' => $leisurePass->id, 'provider_id' => $leisurePass->provider_id, 'user_id' => Auth::id(),
            'pass_name' => $leisurePass->name, 'sessions_total' => $leisurePass->sessions, 'activity_ids' => $leisurePass->activity_ids ?: null,
            'amount_xof' => $leisurePass->price_xof, 'commission_percent' => $payments->commissionPercent(),
            'status' => LeisurePassPurchase::STATUS_PENDING,
        ]);

        return $this->startPayment($purchase, $method, $payments, $cinetPay, fn () => Auth::user()->notify(new \App\Notifications\VisitorSystemNotification(
            __('Carte activée'), $purchase->pass_name.' — '.$purchase->reference, route('visitor.activity-bookings.index'),
        )));
    }

    private function startPayment(Model $owner, string $method, LeisurePaymentService $payments, CinetPayService $cinetPay, callable $after): RedirectResponse
    {
        $success = fn () => $owner instanceof ActivityBooking
            ? redirect()->to($owner->ticketUrl())
            : redirect()->route('visitor.activity-bookings.index')->with('success', __('Carte activée : réservez vos séances depuis la page de l\'activité.'));

        if ($method === 'wallet') {
            try {
                $payments->payFromWallet($owner, Auth::user());
            } catch (\RuntimeException $e) {
                $owner->update(['status' => 'failed']);

                return back()->withInput()->withErrors(['booking' => $e->getMessage()], 'leisure');
            }
            $after();

            return $success();
        }
        if (! $cinetPay->isConfigured()) {
            $owner->update(['status' => 'failed']);

            return back()->withInput()->withErrors(['booking' => __('Le paiement en ligne n\'est pas encore disponible : payez avec votre solde.')], 'leisure');
        }
        $user = Auth::user();
        $result = $cinetPay->initPayment([
            'amount' => $payments->amountDue($owner),
            'designation' => ($owner instanceof LeisurePassPurchase ? $owner->pass_name : $owner->item_name),
            'client_first_name' => $user->first_name, 'client_last_name' => $user->last_name, 'client_email' => $user->email,
            'success_url' => route('leisure.payment.return'), 'failed_url' => route('leisure.payment.return'), 'notify_url' => route('leisure.payment.webhook'),
        ]);
        if (! $result['success']) {
            $owner->update(['status' => 'failed']);

            return back()->withInput()->withErrors(['booking' => $result['message'] ?? __('Erreur lors de l\'initialisation du paiement.')], 'leisure');
        }
        $owner->update(['gateway' => 'cinetpay', 'gateway_txn_id' => $result['merchant_transaction_id']]);

        return redirect()->away($result['payment_url']);
    }

    private function findByTxn(string $token): ?Model
    {
        return $token === '' ? null : (ActivityBooking::where('gateway_txn_id', $token)->first() ?? LeisurePassPurchase::where('gateway_txn_id', $token)->first());
    }

    private function confirmed(Model $owner, LeisurePaymentService $payments, LeisureBookingService $service): void
    {
        if ($payments->markPaid($owner, $owner->gateway_txn_id)) {
            if ($owner instanceof ActivityBooking) {
                $service->afterPayment($owner);
            } else {
                $owner->user?->notify(new \App\Notifications\VisitorSystemNotification(__('Carte activée'), $owner->pass_name.' — '.$owner->reference, route('visitor.activity-bookings.index')));
            }
        }
    }

    public function handleReturn(Request $request, CinetPayService $cinetPay, LeisurePaymentService $payments, LeisureBookingService $service): RedirectResponse
    {
        $owner = $this->findByTxn((string) ($request->query('merchant_transaction_id') ?? $request->query('transaction_id') ?? $request->query('cpm_trans_id') ?? ''));
        if (! $owner) {
            return redirect()->route('leisure.index')->with('error', __('Réservation introuvable. Contactez le support si vous avez été débité.'));
        }
        if ($cinetPay->checkPaymentStatus($owner->gateway_txn_id)['success'] ?? false) {
            $this->confirmed($owner, $payments, $service);
        }
        $owner->refresh();
        if ($owner instanceof ActivityBooking && $owner->status === ActivityBooking::STATUS_PAID) {
            return redirect()->to($owner->ticketUrl());
        }

        return redirect()->route('visitor.activity-bookings.index')->with(
            $owner->status === LeisurePassPurchase::STATUS_ACTIVE ? 'success' : 'error',
            $owner->status === LeisurePassPurchase::STATUS_ACTIVE ? __('Carte activée.') : __('Le paiement n\'a pas abouti. Vous pouvez réessayer.'),
        );
    }

    public function webhook(Request $request, CinetPayService $cinetPay, LeisurePaymentService $payments, LeisureBookingService $service): JsonResponse
    {
        $owner = $this->findByTxn((string) ($request->input('merchant_transaction_id') ?? $request->input('cpm_trans_id') ?? ''));
        if (! $owner) {
            return response()->json(['ok' => false], 404);
        }
        if ($cinetPay->checkPaymentStatus($owner->gateway_txn_id)['success'] ?? false) {
            $this->confirmed($owner, $payments, $service);

            return response()->json(['ok' => true]);
        }
        if ($owner->status === 'pending_payment') {
            $owner->update(['status' => 'failed']);
        }

        return response()->json(['ok' => false]);
    }

    /** Billet : QR code, détail, conditions, décharge. */
    public function ticket(ActivityBooking $activityBooking): View
    {
        abort_unless(in_array($activityBooking->status, [ActivityBooking::STATUS_PAID, ActivityBooking::STATUS_CANCELLED, ActivityBooking::STATUS_REFUNDED], true), 404);

        return view('leisure.ticket', ['booking' => $activityBooking->load(['activity', 'package'])]);
    }

    /* ── Espace visiteur ── */

    public function mine(): View
    {
        $uid = Auth::id();

        return view('visitor.activity-bookings.index', [
            'bookings' => ActivityBooking::with(['activity', 'package'])->where('user_id', $uid)
                ->where(fn ($q) => $q->whereNotIn('status', [ActivityBooking::STATUS_PENDING_PAYMENT, ActivityBooking::STATUS_FAILED])->orWhere('created_at', '>=', now()->subDay()))
                ->orderByDesc('booking_date')->paginate(12),
            'passes' => LeisurePassPurchase::where('user_id', $uid)->where('status', LeisurePassPurchase::STATUS_ACTIVE)->latest()->get(),
            'waitlist' => ActivityWaitlist::with('activity')->where('user_id', $uid)->whereIn('status', [ActivityWaitlist::STATUS_WAITING, ActivityWaitlist::STATUS_NOTIFIED])
                ->whereDate('booking_date', '>=', now()->toDateString())->orderBy('booking_date')->get(),
        ]);
    }

    public function cancel(ActivityBooking $activityBooking, LeisureBookingService $service): RedirectResponse
    {
        abort_unless((int) $activityBooking->user_id === (int) Auth::id(), 404);
        try {
            $refund = $service->cancel($activityBooking);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $refund > 0
            ? __('Réservation annulée. :a FCFA ont été crédités sur votre solde.', ['a' => number_format($refund, 0, ',', ' ')])
            : __('Réservation annulée.'));
    }

    public function reschedule(Request $request, ActivityBooking $activityBooking, LeisureBookingService $service): RedirectResponse
    {
        abort_unless((int) $activityBooking->user_id === (int) Auth::id(), 404);
        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'], 'time' => ['required', 'string', 'max:5']]);
        try {
            $service->reschedule($activityBooking, $data['date'], $data['time']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Réservation reportée au :d à :t.', ['d' => \Carbon\Carbon::parse($data['date'])->format('d/m/Y'), 't' => $data['time']]));
    }

    public function joinWaitlist(Request $request, string $slug, LeisureBookingService $service): RedirectResponse
    {
        $activity = $this->activity($slug);
        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'], 'time' => ['required', 'string', 'max:5'], 'participants' => ['required', 'integer', 'min:1', 'max:50']]);
        try {
            $service->joinWaitlist($activity, Auth::user(), $data['date'], \App\Models\TouristVisitType::normalizeTime($data['time']) ?? $data['time'], (int) $data['participants']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['booking' => $e->getMessage()], 'leisure');
        }

        return back()->with('waitlist_ok', __('Vous êtes sur la liste d\'attente : nous vous prévenons dès qu\'une place se libère.'));
    }

    public function leaveWaitlist(ActivityWaitlist $waitlist, LeisureBookingService $service): RedirectResponse
    {
        abort_unless((int) $waitlist->user_id === (int) Auth::id(), 404);
        $wasNotified = $waitlist->status === ActivityWaitlist::STATUS_NOTIFIED;
        $waitlist->update(['status' => ActivityWaitlist::STATUS_CANCELLED]);
        if ($wasNotified) {
            $service->releaseWaitlist($waitlist->activity, $waitlist->booking_date->toDateString(), $waitlist->booking_time);
        }

        return back()->with('success', __('Vous avez quitté la liste d\'attente.'));
    }
}

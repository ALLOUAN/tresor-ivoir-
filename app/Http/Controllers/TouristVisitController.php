<?php

namespace App\Http\Controllers;

use App\Models\TouristExperience;
use App\Models\TouristVisit;
use App\Models\TouristVisitSession;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use App\Services\CinetPayService;
use App\Services\TouristVisitPricingService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * Réservation de visite d'un site touristique (individuelle, guidée, groupée).
 * Calqué sur ArtworkPurchaseController (paiement intégral, pas d'étape KYC —
 * contrairement à ReservationPaymentController, qui gère un acompte hôtelier
 * avec enregistrement des voyageurs, sans rapport avec une simple visite).
 */
class TouristVisitController extends Controller
{
    private function resolveExperience(string $slug): TouristExperience
    {
        return TouristExperience::active()->where('slug', $slug)->firstOrFail();
    }

    // ── Étape 1 : état initial pour le module de réservation ─────────────────

    public function init(string $slug): JsonResponse
    {
        $experience = $this->resolveExperience($slug);
        $user = Auth::user();

        return response()->json([
            'authenticated' => (bool) $user,
            'user' => $user ? [
                'name' => trim($user->first_name.' '.$user->last_name),
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ] : null,
            'modes' => [
                'individual' => (bool) $experience->visit_individual_enabled,
                'guided' => (bool) $experience->visit_guided_enabled,
                'group' => (bool) $experience->visit_group_enabled,
            ],
            'unit_price_xof' => $experience->visit_individual_price_xof,
            'guide_supplement_xof' => $experience->visit_guide_supplement_xof,
        ]);
    }

    public function sessions(string $slug): JsonResponse
    {
        $experience = $this->resolveExperience($slug);

        $sessions = $experience->visitSessions()
            ->where('is_active', true)
            ->where('session_date', '>=', now()->toDateString())
            ->orderBy('session_date')
            ->get()
            ->map(fn (TouristVisitSession $session) => [
                'id' => $session->id,
                'date' => $session->session_date->format('Y-m-d'),
                'date_label' => $session->session_date->translatedFormat('d M Y'),
                'period_label' => $session->period_label,
                'price_per_person_xof' => $session->price_per_person_xof,
                'remaining_seats' => $session->remainingSeats(),
                'conditions' => $session->conditions,
            ])
            ->values();

        return response()->json(['sessions' => $sessions]);
    }

    // ── Étape 2A : créer un compte visiteur + initier la réservation ─────────

    public function registerAndPay(
        Request $request,
        string $slug,
        CinetPayService $cinetPay,
        TouristVisitPricingService $pricing
    ): JsonResponse {
        $experience = $this->resolveExperience($slug);

        if (Auth::check()) {
            return $this->initiateVisit($request, $experience, $cinetPay, $pricing, Auth::user());
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', PasswordRule::min(8)],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password_hash' => $data['password'],
            'role' => 'visitor',
            'is_active' => true,
            'is_verified' => false,
            'email_verified_at' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->initiateVisit($request, $experience, $cinetPay, $pricing, $user);
    }

    // ── Étape 2B : réservation pour un utilisateur déjà connecté ─────────────

    public function pay(
        Request $request,
        string $slug,
        CinetPayService $cinetPay,
        TouristVisitPricingService $pricing
    ): JsonResponse {
        $experience = $this->resolveExperience($slug);

        return $this->initiateVisit($request, $experience, $cinetPay, $pricing, Auth::user());
    }

    // ── Étape 3 : retour navigateur depuis CinetPay ───────────────────────────

    public function handleReturn(Request $request, CinetPayService $cinetPay): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id')
            ?? $request->query('payment_token')
            ?? $request->query('transaction_id')
            ?? $request->query('cpm_trans_id')
            ?? '');

        $visit = $token !== ''
            ? TouristVisit::with('experience')->where('gateway_txn_id', $token)->first()
            : TouristVisit::with('experience')->find(session('pending_visit_id'));

        if (! $visit) {
            return redirect()->route('tourist-experience.index')
                ->with('error', 'Réservation introuvable. Contactez le support si vous avez été débité.');
        }

        if ($visit->status === TouristVisit::STATUS_PAID) {
            session()->forget('pending_visit_id');

            return redirect()->to($visit->confirmationUrl());
        }

        $statusResult = $cinetPay->checkPaymentStatus($visit->gateway_txn_id);

        if ($statusResult['success']) {
            $this->completeVisit($visit, $visit->gateway_txn_id);
            session()->forget('pending_visit_id');

            return redirect()->to($visit->confirmationUrl());
        }

        return redirect()->route('tourist-experience.show', $visit->experience->slug)
            ->with('error', 'Le paiement n\'a pas abouti. Vous pouvez réessayer.');
    }

    // ── Webhook CinetPay ──────────────────────────────────────────────────────

    public function webhook(Request $request, CinetPayService $cinetPay): JsonResponse
    {
        $token = (string) ($request->input('merchant_transaction_id')
            ?? $request->input('cpm_trans_id')
            ?? $request->input('payment_token')
            ?? '');

        if ($token === '') {
            return response()->json(['ok' => false, 'message' => 'token manquant'], 400);
        }

        $visit = TouristVisit::where('gateway_txn_id', $token)->first();

        if (! $visit) {
            return response()->json(['ok' => false, 'message' => 'Visite introuvable'], 404);
        }

        if ($visit->status === TouristVisit::STATUS_PAID) {
            return response()->json(['ok' => true]);
        }

        $statusResult = $cinetPay->checkPaymentStatus($token);

        if ($statusResult['success']) {
            $this->completeVisit($visit, $token);

            return response()->json(['ok' => true]);
        }

        $visit->update(['status' => TouristVisit::STATUS_FAILED]);

        return response()->json(['ok' => false]);
    }

    // ── Confirmation ───────────────────────────────────────────────────────────

    public function confirmation(TouristVisit $touristVisit): View
    {
        abort_unless($touristVisit->status === TouristVisit::STATUS_PAID, 404);

        return view('tourist-visits.confirmation', ['visit' => $touristVisit]);
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function initiateVisit(
        Request $request,
        TouristExperience $experience,
        CinetPayService $cinetPay,
        TouristVisitPricingService $pricing,
        User $user
    ): JsonResponse {
        $validated = $request->validate([
            'visit_type' => ['required', 'in:individual,guided,group'],
            'session_id' => ['required_if:visit_type,group', 'nullable', 'exists:tourist_visit_sessions,id'],
            'participants_count' => ['required', 'integer', 'min:1', 'max:50'],
            'desired_date' => ['nullable', 'date', 'after_or_equal:today'],
            'with_guide' => ['sometimes', 'boolean'],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', 'in:cinetpay,wallet'],
        ]);

        $visitType = $validated['visit_type'];
        $withGuide = $visitType === 'guided' ? true : $request->boolean('with_guide');
        $participants = (int) $validated['participants_count'];

        abort_unless($experience->is_active, 404);
        abort_unless(match ($visitType) {
            'individual' => $experience->visit_individual_enabled,
            'guided' => $experience->visit_guided_enabled,
            'group' => $experience->visit_group_enabled,
        }, 404, 'Ce mode de visite n\'est pas proposé pour ce site.');

        $baseAttributes = [
            'tourist_experience_id' => $experience->id,
            'provider_id' => $experience->provider_id,
            'user_id' => $user->id,
            'experience_name' => $experience->name,
            'visit_type' => $visitType,
            'with_guide' => $withGuide,
            'participants_count' => $participants,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'ip_address' => $request->ip(),
            'currency' => 'XOF',
        ];

        if ($visitType === 'group') {
            $unitPrice = 0;
            $result = DB::transaction(function () use ($validated, $participants, $experience) {
                $session = TouristVisitSession::whereKey($validated['session_id'])->lockForUpdate()->first();

                if (! $session || ! $session->is_active || (int) $session->tourist_experience_id !== (int) $experience->id) {
                    return null;
                }
                if ($session->remainingSeats() < $participants) {
                    return null;
                }

                return $session;
            });

            if (! $result) {
                return response()->json(['success' => false, 'message' => 'Cette session est complète ou n\'est plus disponible.'], 422);
            }

            $unitPrice = (int) ($result->price_per_person_xof ?? 0);
            $calc = $pricing->compute($unitPrice, $participants, false, 0);

            $visit = TouristVisit::create($baseAttributes + [
                'tourist_visit_session_id' => $result->id,
                'session_date' => $result->session_date,
                'session_time_label' => $result->period_label,
                'unit_price_xof' => $unitPrice,
                'guide_supplement_xof' => 0,
                'amount_total_xof' => $calc['amount_total_xof'],
                'commission_percent' => $calc['commission_percent'],
                'commission_amount_xof' => $calc['commission_amount_xof'],
                'provider_net_amount_xof' => $calc['provider_net_amount_xof'],
                'status' => TouristVisit::STATUS_PENDING_PAYMENT,
            ]);
        } else {
            $unitPrice = (int) ($experience->visit_individual_price_xof ?? 0);
            $guideSupplement = (int) ($experience->visit_guide_supplement_xof ?? 0);
            $calc = $pricing->compute($unitPrice, $participants, $withGuide, $guideSupplement);

            $visit = TouristVisit::create($baseAttributes + [
                'desired_date' => $validated['desired_date'] ?? null,
                'unit_price_xof' => $unitPrice,
                'guide_supplement_xof' => $withGuide ? $guideSupplement : 0,
                'amount_total_xof' => $calc['amount_total_xof'],
                'commission_percent' => $calc['commission_percent'],
                'commission_amount_xof' => $calc['commission_amount_xof'],
                'provider_net_amount_xof' => $calc['provider_net_amount_xof'],
                'status' => TouristVisit::STATUS_PENDING_PAYMENT,
            ]);
        }

        if ($visit->amount_total_xof <= 0) {
            $visit->update(['status' => TouristVisit::STATUS_PAID, 'paid_at' => now()]);
            $this->notifyVisitConfirmed($visit);

            return response()->json(['success' => true, 'redirect_url' => $visit->confirmationUrl()]);
        }

        $paymentMethod = $validated['payment_method'] ?? 'cinetpay';

        if ($paymentMethod === 'wallet') {
            try {
                app(WalletService::class)->payVisitFromWallet($visit, $user);
            } catch (\RuntimeException $e) {
                $visit->update(['status' => TouristVisit::STATUS_FAILED]);

                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            $this->notifyVisitConfirmed($visit);

            return response()->json(['success' => true, 'redirect_url' => $visit->confirmationUrl()]);
        }

        $result = $cinetPay->initPayment([
            'amount' => $visit->amount_total_xof,
            'designation' => 'Visite — '.$experience->name,
            'client_first_name' => $user->first_name,
            'client_last_name' => $user->last_name,
            'client_email' => $user->email,
            'success_url' => route('tourist-visit.payment.return'),
            'failed_url' => route('tourist-visit.payment.return'),
            'notify_url' => route('tourist-visit.payment.webhook'),
        ]);

        if (! $result['success']) {
            $visit->update(['status' => TouristVisit::STATUS_FAILED]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.',
            ], 422);
        }

        $visit->update(['gateway' => 'cinetpay', 'gateway_txn_id' => $result['merchant_transaction_id']]);
        session(['pending_visit_id' => $visit->id]);

        return response()->json(['success' => true, 'payment_url' => $result['payment_url']]);
    }

    /**
     * Verrouillée + idempotente : webhook et retour navigateur peuvent appeler ceci
     * quasi simultanément (course réaliste). Pas d'état « survente » ici — contrairement
     * à ArtworkOrder, la place (mode groupé) est déjà verrouillée à la création de la
     * visite, pas à la confirmation du paiement.
     */
    private function completeVisit(TouristVisit $visit, string $token): void
    {
        DB::transaction(function () use ($visit, $token) {
            $visit = TouristVisit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
            if ($visit->status !== TouristVisit::STATUS_PENDING_PAYMENT) {
                return;
            }

            $visit->update(['status' => TouristVisit::STATUS_PAID, 'paid_at' => now(), 'gateway_txn_id' => $token]);

            app(WalletService::class)->creditSaleForVisit($visit);
            $this->notifyVisitConfirmed($visit);
        });
    }

    private function notifyVisitConfirmed(TouristVisit $visit): void
    {
        if ($visit->user_id && ($client = User::find($visit->user_id))) {
            $client->notify(new VisitorSystemNotification(
                'Visite confirmée',
                'Votre réservation pour "'.$visit->experience_name.'" est confirmée.',
                route('tourist-experience.show', $visit->experience?->slug ?? ''),
            ));
        }

        $providerUser = $visit->provider?->user;
        if ($providerUser) {
            $providerUser->notify(new VisitorSystemNotification(
                'Nouvelle visite réservée',
                $visit->full_name.' a réservé une visite — "'.$visit->experience_name.'" — '.$visit->reference,
                route('provider.tourist-visits.show', $visit),
            ));
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\TouristExperience;
use App\Models\TouristVisit;
use App\Models\TouristVisitSession;
use App\Models\TouristVisitType;
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

    /**
     * Créneaux d'un type de visite pour une date : places restantes (grisé si complet ou passé)
     * et ajustement de prix (week-end, heures creuses).
     */
    public function slots(Request $request, string $slug): JsonResponse
    {
        $experience = $this->resolveExperience($slug);
        $data = $request->validate([
            'type' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $type = TouristVisitType::active()->where('tourist_experience_id', $experience->id)->findOrFail($data['type']);

        return response()->json([
            'date' => $data['date'],
            'has_slots' => $type->hasSlots(),
            'capacity' => $type->capacity(),
            'slots' => $type->availabilityFor($data['date']),
        ]);
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
            'email' => \App\Services\ClientAccountService::uniqueEmailRule(),
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', \App\Services\ClientAccountService::passwordRule()],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        \App\Services\ClientAccountService::freeTrashedEmail($data['email']);

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
                ->with('error', __('Réservation introuvable. Contactez le support si vous avez été débité.'));
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
            ->with('error', __('Le paiement n\'a pas abouti. Vous pouvez réessayer.'));
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
        abort_unless($touristVisit->hasValidTicket(), 404);

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
            'visit_type' => ['required', 'in:individual,guided,group,custom'],
            'visit_type_id' => ['required_if:visit_type,custom', 'nullable', 'integer'],
            'session_id' => ['required_if:visit_type,group', 'nullable', 'exists:tourist_visit_sessions,id'],
            'participants_count' => ['required', 'integer', 'min:1', 'max:50'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'residents_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'desired_date' => ['nullable', 'date', 'after_or_equal:today'],
            'with_guide' => ['sometimes', 'boolean'],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', 'in:cinetpay,wallet,on_site'],
            'visit_time' => ['nullable', 'string', 'max:5'],
            'tiers' => ['nullable', 'array', 'max:12'],
            'tiers.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'addons' => ['nullable', 'array', 'max:12'],
            'addons.*' => ['nullable', 'integer', 'min:0', 'max:11'],
            'language' => ['nullable', 'string', 'max:40'],
        ]);

        $visitType = $validated['visit_type'];
        $withGuide = $visitType === 'guided' ? true : $request->boolean('with_guide');
        $participants = (int) $validated['participants_count'];

        abort_unless($experience->is_active, 404);

        // Type personnalisé : doit appartenir à ce site et être actif.
        $customType = null;
        if ($visitType === 'custom') {
            $customType = TouristVisitType::active()
                ->where('tourist_experience_id', $experience->id)
                ->find($validated['visit_type_id'] ?? 0);
            abort_unless($customType, 404, 'Ce type de visite n\'est pas proposé pour ce site.');
            $withGuide = $customType->includes_guide;
        }

        abort_unless(match ($visitType) {
            'individual' => $experience->visit_individual_enabled,
            'guided' => $experience->visit_guided_enabled,
            'group' => $experience->visit_group_enabled,
            'custom' => true,
        }, 404, 'Ce mode de visite n\'est pas proposé pour ce site.');

        $baseAttributes = [
            'tourist_experience_id' => $experience->id,
            'provider_id' => $experience->provider_id,
            'user_id' => $user->id,
            'experience_name' => $experience->name,
            'visit_type' => $visitType,
            'tourist_visit_type_id' => $customType?->id,
            'visit_type_name' => $customType?->name,
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
        } elseif ($customType) {
            $visit = $this->createCustomVisit($request, $validated, $customType, $baseAttributes, $pricing);
            if ($visit instanceof JsonResponse) {
                return $visit;
            }
        } else {
            $unitPrice = (int) ($experience->visit_individual_price_xof ?? 0);
            $guideSupplement = (int) ($experience->visit_guide_supplement_xof ?? 0);
            // Tarifs enfant / résident : seulement s'ils sont définis, sinon tarif normal.
            $children = $experience->visit_child_price_xof !== null ? (int) ($validated['children_count'] ?? 0) : 0;
            $residents = $experience->visit_resident_price_xof !== null ? (int) ($validated['residents_count'] ?? 0) : 0;
            if ($children + $residents > $participants) {
                return response()->json(['success' => false, 'message' => __('Le nombre d\'enfants et de résidents dépasse le nombre de participants.')], 422);
            }
            $peopleXof = ($participants - $children - $residents) * $unitPrice
                + $children * (int) $experience->visit_child_price_xof
                + $residents * (int) $experience->visit_resident_price_xof;
            $calc = $pricing->compute($peopleXof, 1, $withGuide, $guideSupplement);

            $visit = TouristVisit::create($baseAttributes + [
                'desired_date' => $validated['desired_date'] ?? null,
                'unit_price_xof' => $unitPrice,
                'children_count' => $children,
                'residents_count' => $residents,
                'guide_supplement_xof' => $withGuide ? $guideSupplement : 0,
                'amount_total_xof' => $calc['amount_total_xof'],
                'commission_percent' => $calc['commission_percent'],
                'commission_amount_xof' => $calc['commission_amount_xof'],
                'provider_net_amount_xof' => $calc['provider_net_amount_xof'],
                'status' => TouristVisit::STATUS_PENDING_PAYMENT,
            ]);
        }

        if ($visit->status === TouristVisit::STATUS_BOOKED) {
            $this->notifyVisitConfirmed($visit);

            return response()->json(['success' => true, 'redirect_url' => $visit->confirmationUrl()]);
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
     * Réservation d'un type personnalisé : date (obligatoire), créneau, quantités par catégorie de tarif,
     * options, langue, ajustement week-end / heures creuses, délai d'annulation et paiement sur place.
     * La place est vérifiée sous verrou du type : deux clients ne peuvent pas prendre la dernière place.
     */
    private function createCustomVisit(Request $request, array $validated, TouristVisitType $type, array $baseAttributes, TouristVisitPricingService $pricing): TouristVisit|JsonResponse
    {
        $fail = fn (string $message) => response()->json(['success' => false, 'message' => $message], 422);

        $date = $validated['desired_date'] ?? null;
        if (! $date) {
            return $fail(__('Choisissez la date de votre visite.'));
        }
        $date = \Carbon\Carbon::parse($date)->toDateString();

        $time = null;
        if ($type->hasSlots()) {
            $time = TouristVisitType::normalizeTime((string) ($validated['visit_time'] ?? ''));
            if (! $time || ! in_array($time, $type->slots(), true)) {
                return $fail(__('Choisissez un créneau horaire.'));
            }
        }

        // Quantités par catégorie de tarif (sans grille : le nombre de participants au tarif unique).
        $tiers = $type->tiers();
        $quantities = $type->hasTiers()
            ? collect($tiers)->keys()->map(fn ($i) => (int) ($validated['tiers'][$i] ?? 0))->all()
            : [(int) $validated['participants_count']];
        $participants = array_sum($quantities);
        if ($participants < 1) {
            return $fail(__('Indiquez au moins un participant.'));
        }
        if ($type->max_participants && $participants > $type->max_participants) {
            return $fail(__('Ce type de visite accepte au maximum :n participants.', ['n' => $type->max_participants]));
        }

        $languages = $type->languagesList();
        $language = trim((string) ($validated['language'] ?? '')) ?: ($languages[0] ?? null);
        if ($languages && ! in_array($language, $languages, true)) {
            return $fail(__('Cette langue n\'est pas proposée pour ce type de visite.'));
        }

        $payOnSite = ($validated['payment_method'] ?? null) === 'on_site';
        if ($payOnSite && ! $type->allow_pay_on_site) {
            return $fail(__('Le paiement sur place n\'est pas proposé pour ce type de visite.'));
        }

        // Prix : catégories ajustées (week-end / heures creuses), puis options, puis supplément guide.
        $adjust = $type->adjustPercentFor(\Carbon\Carbon::parse($date, 'Africa/Abidjan'), $time);
        $breakdown = collect($tiers)->map(fn ($t, $i) => [
            'label' => $t['label'], 'qty' => $quantities[$i] ?? 0, 'unit_price_xof' => TouristVisitType::applyPercent($t['price_xof'], $adjust),
        ])->filter(fn ($l) => $l['qty'] > 0)->values()->all();
        $offered = $type->addonsList();
        $addons = collect($validated['addons'] ?? [])->unique()->filter(fn ($i) => isset($offered[$i]))
            ->map(fn ($i) => ['label' => $offered[$i]['label'], 'per' => $offered[$i]['per'], 'qty' => $offered[$i]['per'] === 'person' ? $participants : 1, 'unit_price_xof' => $offered[$i]['price_xof']])
            ->values()->all();
        $peopleXof = collect($breakdown)->sum(fn ($l) => $l['qty'] * $l['unit_price_xof']) + collect($addons)->sum(fn ($a) => $a['qty'] * $a['unit_price_xof']);
        $guide = $type->appliedGuideSupplement();
        $calc = $pricing->compute($peopleXof, 1, $guide > 0, $guide);

        $startsAt = \Carbon\Carbon::parse($date, 'Africa/Abidjan');
        if ($time) {
            $startsAt->setTimeFromTimeString($time);
        }
        $cancellableUntil = $type->free_cancellation_hours !== null ? $startsAt->copy()->subHours($type->free_cancellation_hours) : null;

        return DB::transaction(function () use ($type, $date, $time, $participants, $fail, $baseAttributes, $breakdown, $addons, $language, $payOnSite, $adjust, $calc, $guide, $cancellableUntil) {
            TouristVisitType::whereKey($type->id)->lockForUpdate()->first();
            $slot = collect($type->availabilityFor($date))->firstWhere('time', $time);
            if (! $slot || $slot['past']) {
                return $fail(__('Ce créneau est déjà passé. Choisissez-en un autre.'));
            }
            if ($slot['remaining'] !== null && $slot['remaining'] < $participants) {
                return $fail($slot['remaining'] > 0
                    ? __('Il ne reste que :n place(s) sur ce créneau.', ['n' => $slot['remaining']])
                    : __('Ce créneau est complet. Choisissez-en un autre.'));
            }

            return TouristVisit::create(array_merge($baseAttributes, [
                'participants_count' => $participants,
                'desired_date' => $date,
                'visit_time' => $time,
                'tier_breakdown' => $breakdown,
                'addons' => $addons ?: null,
                'language' => $language,
                'pay_on_site' => $payOnSite,
                'price_adjust_percent' => $adjust ?: null,
                'cancellable_until' => $cancellableUntil?->copy()->utc(),
                'unit_price_xof' => (int) ($breakdown[0]['unit_price_xof'] ?? 0),
                'children_count' => 0,
                'residents_count' => 0,
                'guide_supplement_xof' => $guide,
                'amount_total_xof' => $calc['amount_total_xof'],
                'commission_percent' => $calc['commission_percent'],
                'commission_amount_xof' => $calc['commission_amount_xof'],
                'provider_net_amount_xof' => $calc['provider_net_amount_xof'],
                'gateway' => $payOnSite ? 'on_site' : null,
                'status' => $payOnSite && $calc['amount_total_xof'] > 0 ? TouristVisit::STATUS_BOOKED : TouristVisit::STATUS_PENDING_PAYMENT,
            ]));
        });
    }

    /**
     * Annulation par le client dans le délai d'annulation gratuite : visite payée → remboursée
     * sur son solde ; visite à régler sur place → simplement annulée.
     */
    public function cancel(TouristVisit $touristVisit): RedirectResponse
    {
        abort_unless((int) $touristVisit->user_id === (int) Auth::id(), 403);

        $result = DB::transaction(function () use ($touristVisit) {
            $visit = TouristVisit::whereKey($touristVisit->id)->lockForUpdate()->firstOrFail();
            if (! $visit->isCancellableByClient()) {
                return __('Le délai d\'annulation gratuite est dépassé : cette visite ne peut plus être annulée en ligne.');
            }
            if ($visit->status === TouristVisit::STATUS_PAID && $visit->amount_total_xof > 0) {
                app(WalletService::class)->recordManualRefundForVisit($visit, Auth::user(), 'Annulation gratuite par le client', true);
                $visit->refresh()->update(['cancelled_at' => now()]);
            } else {
                $visit->update(['status' => TouristVisit::STATUS_CANCELLED, 'cancelled_at' => now()]);
            }

            return null;
        });

        if ($result) {
            return back()->with('error', $result);
        }

        $providerUser = $touristVisit->provider?->user;
        $providerUser?->notify(new VisitorSystemNotification(
            'Visite annulée par le client',
            $touristVisit->full_name.' a annulé sa visite — "'.$touristVisit->experience_name.'" — '.$touristVisit->reference,
            route('provider.tourist-visits.show', $touristVisit),
        ));

        return back()->with('success', $touristVisit->fresh()->status === TouristVisit::STATUS_REFUNDED
            ? __('Visite annulée. Le montant a été crédité sur votre solde Trésors d\'Ivoire.')
            : __('Visite annulée.'));
    }

    /** Mes visites : billets, détails et annulation gratuite. */
    public function mine(): View
    {
        $visits = TouristVisit::with('experience')->where('user_id', Auth::id())
            ->whereNotIn('status', [TouristVisit::STATUS_FAILED])
            ->where(fn ($q) => $q->where('status', '!=', TouristVisit::STATUS_PENDING_PAYMENT)->orWhere('created_at', '>=', now()->subDay()))
            ->latest()->paginate(12);

        return view('visitor.tourist-visits.index', compact('visits'));
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

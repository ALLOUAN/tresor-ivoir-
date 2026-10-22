<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\ArtworkOrder;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use App\Services\ArtworkPricingService;
use App\Services\CinetPayService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ArtworkPurchaseController extends Controller
{
    // ── Étape 1 : état initial pour le bouton d'achat ────────────────────────

    public function init(Artwork $artwork): JsonResponse
    {
        abort_unless($artwork->isAvailable(), 404);

        $user = Auth::user();

        return response()->json([
            'authenticated' => (bool) $user,
            'user' => $user ? [
                'name' => trim($user->first_name.' '.$user->last_name),
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ] : null,
            'artwork' => [
                'uuid' => $artwork->uuid,
                'title' => $artwork->title,
                'price_xof' => $artwork->price_xof,
                'price_label' => number_format((int) $artwork->price_xof, 0, ',', ' ').' XOF',
            ],
        ]);
    }

    // ── Étape 2A : créer un compte visiteur + initier paiement ───────────────

    public function registerAndPay(
        Request $request,
        Artwork $artwork,
        CinetPayService $cinetPay,
        ArtworkPricingService $pricing
    ): JsonResponse {
        abort_unless($artwork->isAvailable(), 404);

        if (Auth::check()) {
            return $this->initiatePurchase($request, $artwork, $cinetPay, $pricing, Auth::user());
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

        return $this->initiatePurchase($request, $artwork, $cinetPay, $pricing, $user);
    }

    // ── Étape 2B : paiement pour utilisateur déjà connecté ───────────────────

    public function pay(
        Request $request,
        Artwork $artwork,
        CinetPayService $cinetPay,
        ArtworkPricingService $pricing
    ): JsonResponse {
        abort_unless($artwork->isAvailable(), 404);

        return $this->initiatePurchase($request, $artwork, $cinetPay, $pricing, Auth::user());
    }

    // ── Étape 3 : retour navigateur depuis CinetPay ───────────────────────────

    public function handleReturn(Request $request, CinetPayService $cinetPay): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id')
            ?? $request->query('payment_token')
            ?? $request->query('transaction_id')
            ?? $request->query('cpm_trans_id')
            ?? '');

        $order = $token !== ''
            ? ArtworkOrder::with('artwork')->where('gateway_txn_id', $token)->first()
            : ArtworkOrder::with('artwork')->find(session('pending_artwork_order_id'));

        if (! $order) {
            return redirect()->route('art.index')
                ->with('error', __('Achat introuvable. Contactez le support si vous avez été débité.'));
        }

        if ($order->payment_plan === 'layaway' && $order->pending_installment) {
            session()->forget('pending_artwork_order_id');
            if ($cinetPay->checkPaymentStatus($order->gateway_txn_id)['success'] ?? false) {
                $this->settleInstallment($order);
            }

            return redirect()->route('visitor.art-orders.index')->with('success', __('Paiement enregistré.'));
        }

        if ($order->status === ArtworkOrder::STATUS_PAID) {
            session()->forget('pending_artwork_order_id');

            return redirect()->route('art.show', $order->artwork->slug)
                ->with('success', __('Paiement confirmé ! Merci pour votre achat.'));
        }

        $statusResult = $cinetPay->checkPaymentStatus($order->gateway_txn_id);

        if ($statusResult['success']) {
            $this->completePurchase($order, $order->gateway_txn_id);
            session()->forget('pending_artwork_order_id');

            return redirect()->route('art.show', $order->artwork->slug)
                ->with('success', __('Paiement confirmé ! Merci pour votre achat.'));
        }

        return redirect()->route('art.show', $order->artwork->slug)
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

        $order = ArtworkOrder::where('gateway_txn_id', $token)->first();

        if (! $order) {
            return response()->json(['ok' => false, 'message' => 'Commande introuvable'], 404);
        }

        if ($order->payment_plan === 'layaway' && $order->pending_installment) {
            if ($cinetPay->checkPaymentStatus($token)['success'] ?? false) {
                $this->settleInstallment($order);

                return response()->json(['ok' => true]);
            }

            return response()->json(['ok' => false]);
        }

        if ($order->status === ArtworkOrder::STATUS_PAID) {
            return response()->json(['ok' => true]);
        }

        $statusResult = $cinetPay->checkPaymentStatus($token);

        if ($statusResult['success']) {
            $this->completePurchase($order, $token);

            return response()->json(['ok' => true]);
        }

        $order->update(['status' => ArtworkOrder::STATUS_CANCELLED]);

        return response()->json(['ok' => false]);
    }

    // ── Achat « marché » : édition, offre acceptée, livraison, assurance, paiement en 3 fois ──

    public function checkout(Request $request, Artwork $artwork, \App\Services\ArtSalesService $sales, CinetPayService $cinetPay): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'visitor', 403, __('Les achats se font avec un compte visiteur.'));
        $data = $request->validate([
            'edition_id' => ['nullable', 'integer'], 'offer_id' => ['nullable', 'integer'],
            'delivery_mode' => ['required', 'in:pickup,abidjan,country'],
            'delivery_address' => ['required_unless:delivery_mode,pickup', 'nullable', 'string', 'max:255'],
            'delivery_city' => ['nullable', 'string', 'max:120'], 'insurance' => ['nullable', 'boolean'],
            'plan' => ['nullable', 'in:full,layaway'], 'payment_method' => ['required', 'in:cinetpay,wallet'],
            'phone' => ['required', 'string', 'max:30'],
        ], ['delivery_address.required_unless' => __('Indiquez l\'adresse de livraison.')]);
        $user = Auth::user();
        try {
            $order = DB::transaction(function () use ($artwork, $sales, $user, $data, $request) {
                Artwork::whereKey($artwork->id)->lockForUpdate()->first();

                return $sales->createOrder($artwork->fresh(), $user, $data + ['insurance' => $request->boolean('insurance'), 'ip' => $request->ip()]);
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['checkout' => $e->getMessage()], 'art');
        }

        return $this->payDue($order, $data['payment_method'], $sales, $cinetPay);
    }

    /** Échéance suivante d'un paiement en plusieurs fois. */
    public function payInstallment(Request $request, ArtworkOrder $artworkOrder, \App\Services\ArtSalesService $sales, CinetPayService $cinetPay): RedirectResponse
    {
        abort_unless((int) $artworkOrder->buyer_user_id === (int) Auth::id(), 404);
        abort_unless($artworkOrder->status === ArtworkOrder::STATUS_RESERVED, 422);
        $method = $request->validate(['payment_method' => ['required', 'in:cinetpay,wallet']])['payment_method'];

        return $this->payDue($artworkOrder, $method, $sales, $cinetPay);
    }

    private function payDue(ArtworkOrder $order, string $method, \App\Services\ArtSalesService $sales, CinetPayService $cinetPay): RedirectResponse
    {
        $user = Auth::user();
        if ($method === 'wallet') {
            try {
                $result = $sales->payFromWallet($order, $user);
            } catch (\RuntimeException $e) {
                return back()->withInput()->withErrors(['checkout' => $e->getMessage()], 'art');
            }
            if ($order->payment_plan === 'layaway') {
                if ($result === 'paid') {
                    $sales->completeLayaway($order);
                }

                return redirect()->route('visitor.art-orders.index')->with('success', $result === 'paid' ? __('Œuvre entièrement réglée. Merci !') : __('Paiement enregistré : l\'œuvre vous est réservée.'));
            }
            $this->completePurchase($order, 'WALLET-'.$order->id);

            return redirect()->route('visitor.art-orders.index')->with('success', __('Paiement confirmé ! Merci pour votre achat.'));
        }
        if (! $cinetPay->isConfigured()) {
            return back()->withInput()->withErrors(['checkout' => __('Le paiement en ligne n\'est pas encore disponible : payez avec votre solde.')], 'art');
        }
        $amount = $sales->dueNow($order);
        $result = $cinetPay->initPayment([
            'amount' => $amount, 'designation' => 'Œuvre — '.$order->artwork?->title,
            'client_first_name' => $user->first_name, 'client_last_name' => $user->last_name, 'client_email' => $user->email,
            'success_url' => route('art.purchase.return'), 'failed_url' => route('art.purchase.return'), 'notify_url' => route('art.purchase.webhook'),
        ]);
        if (! $result['success']) {
            return back()->withInput()->withErrors(['checkout' => $result['message'] ?? __('Erreur lors de l\'initialisation du paiement.')], 'art');
        }
        $order->update(['gateway' => 'cinetpay', 'gateway_txn_id' => $result['merchant_transaction_id'],
            'pending_installment' => $order->payment_plan === 'layaway' ? $order->nextInstallment()['n'] : null]);
        session(['pending_artwork_order_id' => $order->id]);

        return redirect()->away($result['payment_url']);
    }

    private function settleInstallment(ArtworkOrder $order): void
    {
        $sales = app(\App\Services\ArtSalesService::class);
        if ($sales->settleInstallment($order, (int) $order->pending_installment)) {
            $sales->completeLayaway($order);
        }
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function initiatePurchase(
        Request $request,
        Artwork $artwork,
        CinetPayService $cinetPay,
        ArtworkPricingService $pricing,
        User $user
    ): JsonResponse {
        // Verrou court, sans mutation : seule la confirmation de paiement décrémente
        // réellement le stock (cf. completePurchase). Ce contrôle évite juste d'engager
        // un paiement CinetPay pour une pièce déjà épuisée entre-temps.
        $available = DB::transaction(function () use ($artwork) {
            $locked = Artwork::whereKey($artwork->id)->lockForUpdate()->first();

            return $locked && $locked->isAvailable();
        });

        if (! $available) {
            return response()->json([
                'success' => false,
                'message' => 'Cette œuvre n\'est plus disponible.',
            ], 422);
        }

        $calc = $pricing->compute((int) $artwork->price_xof);

        $result = $cinetPay->initPayment([
            'amount' => $calc['amount_total_xof'],
            'designation' => 'Achat œuvre — '.$artwork->title,
            'client_first_name' => $user->first_name,
            'client_last_name' => $user->last_name,
            'client_email' => $user->email,
            'success_url' => route('art.purchase.return'),
            'failed_url' => route('art.purchase.return'),
            'notify_url' => route('art.purchase.webhook'),
        ]);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.',
            ], 422);
        }

        $order = ArtworkOrder::create([
            'artwork_id' => $artwork->id,
            'provider_id' => $artwork->provider_id,
            'buyer_user_id' => $user->id,
            'unit_price_xof' => $artwork->price_xof,
            'amount_total_xof' => $calc['amount_total_xof'],
            'commission_percent' => $calc['commission_percent'],
            'commission_amount_xof' => $calc['commission_amount_xof'],
            'artist_net_amount_xof' => $calc['artist_net_amount_xof'],
            'currency' => 'XOF',
            'status' => ArtworkOrder::STATUS_PENDING_PAYMENT,
            'gateway' => 'cinetpay',
            'gateway_txn_id' => $result['merchant_transaction_id'],
            'buyer_name' => trim($user->first_name.' '.$user->last_name),
            'buyer_email' => $user->email,
            'buyer_phone' => $user->phone,
            'ip_address' => $request->ip(),
            'metadata' => [
                'payment_token' => $result['payment_token'] ?? null,
            ],
        ]);

        session(['pending_artwork_order_id' => $order->id]);

        return response()->json([
            'success' => true,
            'payment_url' => $result['payment_url'],
        ]);
    }

    /**
     * Le paiement CinetPay a, à ce stade, déjà réellement abouti (vérifié par
     * checkPaymentStatus avant l'appel à cette méthode) — l'argent est encaissé quoi
     * qu'il arrive. La seule question ici est donc l'état du stock, jamais si l'argent
     * doit être pris ou non. Si le stock est déjà épuisé (survente — deux acheteurs
     * quasi simultanés sur une pièce unique), la commande est marquée STATUS_OVERSOLD
     * plutôt que STATUS_PAID : jamais silencieusement confondue avec une vente
     * normale à expédier, l'argent reste crédité normalement (artiste + commission)
     * pour que le remboursement manuel admin existant (recordManualRefundForArtworkOrder)
     * puisse le reprendre sans logique financière nouvelle, et les admins actifs sont
     * notifiés immédiatement.
     */
    private function completePurchase(ArtworkOrder $order, string $token): void
    {
        DB::transaction(function () use ($order, $token) {
            $order = ArtworkOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status !== ArtworkOrder::STATUS_PENDING_PAYMENT) {
                return; // déjà traité (webhook + retour navigateur concurrents)
            }

            $artwork = Artwork::whereKey($order->artwork_id)->lockForUpdate()->first();
            if ($order->artwork_edition_id) {
                // Édition limitée : le tirage a son propre stock, l'original n'est pas touché.
                $edition = \App\Models\ArtworkEdition::whereKey($order->artwork_edition_id)->lockForUpdate()->first();
                $oversold = ! $edition || $edition->remaining() < 1;
            } else {
                $oversold = ! $artwork || $artwork->stock_quantity <= 0;
                if (! $oversold) {
                    $artwork->decrement('stock_quantity');
                    if ($artwork->fresh()->stock_quantity <= 0) {
                        $artwork->update(['status' => Artwork::STATUS_SOLD]);
                    }
                }
            }

            $order->update([
                'status' => $oversold ? ArtworkOrder::STATUS_OVERSOLD : ArtworkOrder::STATUS_PAID,
                'paid_at' => now(),
                'gateway_txn_id' => $token,
            ]);

            app(WalletService::class)->creditSaleForArtworkOrder($order);
            if (! $oversold) {
                app(\App\Services\ArtSalesService::class)->onPaid($order);
            }

            if ($order->buyer_user_id && ($buyer = User::find($order->buyer_user_id))) {
                $buyer->notify($oversold
                    ? new VisitorSystemNotification(
                        'Paiement reçu — pièce indisponible',
                        'Votre paiement pour "'.($artwork->title ?? $order->artwork?->title).'" a bien été reçu, mais cette pièce en exemplaire unique vient d\'être vendue à un autre acheteur. Notre équipe va procéder à votre remboursement.',
                        route('visitor.art-orders.index'),
                    )
                    : new VisitorSystemNotification(
                        'Commande confirmée',
                        'Votre achat de "'.($artwork->title ?? $order->artwork?->title).'" est confirmé.',
                        route('visitor.art-orders.index'),
                    ));
            }

            $artistUser = $order->provider?->user;
            if ($artistUser && ! $oversold) {
                $artistUser->notify(new VisitorSystemNotification(
                    'Nouvelle vente',
                    'Vous avez vendu "'.($artwork->title ?? $order->artwork?->title).'" pour '.number_format((int) $order->artist_net_amount_xof, 0, ',', ' ').' XOF.',
                    route('provider.art-orders.show', $order),
                ));
            }

            if ($oversold) {
                foreach (User::where('role', 'admin')->where('is_active', true)->get() as $admin) {
                    $admin->notify(new VisitorSystemNotification(
                        'Survente détectée — remboursement à traiter',
                        'La commande '.$order->reference.' ("'.($artwork->title ?? $order->artwork?->title).'") a été payée alors que le stock était déjà épuisé. Remboursement manuel nécessaire.',
                        route('admin.art-orders.show', $order),
                    ));
                }
            }
        });
    }
}

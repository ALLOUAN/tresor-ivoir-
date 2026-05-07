<?php

namespace App\Http\Controllers;

use App\Models\MediaPurchase;
use App\Models\SiteMediaItem;
use App\Models\User;
use App\Services\CinetPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule;

class MediaPurchaseController extends Controller
{
    // ── Étape 1 : état initial pour le modal ─────────────────────────────────

    public function init(SiteMediaItem $media): JsonResponse
    {
        abort_unless($media->is_active && $media->price > 0, 404);

        $user = Auth::user();

        return response()->json([
            'authenticated' => (bool) $user,
            'user' => $user ? [
                'name'  => trim($user->first_name . ' ' . $user->last_name),
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ] : null,
            'media' => [
                'uuid'  => $media->uuid,
                'title' => $media->title ?? $media->original_name,
                'price' => (float) $media->price,
                'price_label' => number_format((float) $media->price, 0, ',', ' ') . ' FCFA',
            ],
        ]);
    }

    // ── Étape 2A : créer un compte visiteur + initier paiement ───────────────

    public function registerAndPay(
        Request $request,
        SiteMediaItem $media,
        CinetPayService $cinetPay
    ): JsonResponse {
        abort_unless($media->is_active && $media->price > 0, 404);

        if (Auth::check()) {
            return $this->initiatePurchase($request, $media, $cinetPay, Auth::user());
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'      => ['required', 'string', 'max:20'],
            'password'   => ['required', PasswordRule::min(8)],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required'  => 'Le nom est obligatoire.',
            'email.required'      => 'L\'adresse e-mail est obligatoire.',
            'email.unique'        => 'Cette adresse e-mail est déjà utilisée.',
            'phone.required'      => 'Le téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = User::create([
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'password_hash'     => $data['password'],
            'role'              => 'visitor',
            'is_active'         => true,
            'is_verified'       => false,
            'email_verified_at' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->initiatePurchase($request, $media, $cinetPay, $user);
    }

    // ── Étape 2B : paiement pour utilisateur déjà connecté ───────────────────

    public function pay(
        Request $request,
        SiteMediaItem $media,
        CinetPayService $cinetPay
    ): JsonResponse {
        abort_unless($media->is_active && $media->price > 0, 404);

        return $this->initiatePurchase($request, $media, $cinetPay, Auth::user());
    }

    // ── Étape 3 : retour navigateur depuis CinetPay ───────────────────────────

    public function handleReturn(Request $request, CinetPayService $cinetPay): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id')
            ?? $request->query('payment_token')
            ?? $request->query('transaction_id')
            ?? $request->query('cpm_trans_id')
            ?? '');

        $purchase = $token !== ''
            ? MediaPurchase::with('media')->where('gateway_txn_id', $token)->first()
            : MediaPurchase::with('media')->find(session('pending_media_purchase_id'));

        if (! $purchase) {
            return redirect()->route('gallery.public')
                ->with('error', 'Achat introuvable. Contactez le support si vous avez été débité.');
        }

        if ($purchase->status === 'completed') {
            session()->forget('pending_media_purchase_id');
            return redirect()->route('gallery.public.show', $purchase->media->uuid)
                ->with('success', 'Paiement confirmé ! Votre achat est disponible.');
        }

        $statusResult = $cinetPay->checkPaymentStatus($purchase->gateway_txn_id);

        if ($statusResult['success']) {
            $this->completePurchase($purchase, $purchase->gateway_txn_id);
            session()->forget('pending_media_purchase_id');
            return redirect()->route('gallery.public.show', $purchase->media->uuid)
                ->with('success', 'Paiement confirmé ! Votre achat est disponible.');
        }

        return redirect()->route('gallery.public.show', $purchase->media->uuid)
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

        $purchase = MediaPurchase::where('gateway_txn_id', $token)->first();

        if (! $purchase) {
            return response()->json(['ok' => false, 'message' => 'Achat introuvable'], 404);
        }

        if ($purchase->status === 'completed') {
            return response()->json(['ok' => true]);
        }

        $statusResult = $cinetPay->checkPaymentStatus($token);

        if ($statusResult['success']) {
            $this->completePurchase($purchase, $token);
            return response()->json(['ok' => true]);
        }

        $purchase->update(['status' => 'failed']);

        return response()->json(['ok' => false]);
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function initiatePurchase(
        Request $request,
        SiteMediaItem $media,
        CinetPayService $cinetPay,
        User $user
    ): JsonResponse {
        $amount = (float) $media->price;

        $result = $cinetPay->initPayment([
            'amount'              => $amount,
            'designation'         => 'Achat image — ' . ($media->title ?? $media->original_name),
            'client_first_name'   => $user->first_name,
            'client_last_name'    => $user->last_name,
            'client_email'        => $user->email,
            'client_phone_number' => $user->phone ?? '',
            'success_url'         => route('gallery.purchase.return'),
            'failed_url'          => route('gallery.purchase.return'),
            'notify_url'          => route('gallery.purchase.webhook'),
        ]);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.',
            ], 422);
        }

        $purchase = MediaPurchase::create([
            'media_id'       => $media->id,
            'user_id'        => $user->id,
            'amount'         => $amount,
            'currency'       => 'XOF',
            'gateway'        => 'cinetpay',
            'gateway_txn_id' => $result['merchant_transaction_id'],
            'status'         => 'pending',
            'ip_address'     => $request->ip(),
            'metadata'       => [
                'payment_token' => $result['payment_token'] ?? null,
                'media_uuid'    => $media->uuid,
            ],
        ]);

        session(['pending_media_purchase_id' => $purchase->id]);

        return response()->json([
            'success'     => true,
            'payment_url' => $result['payment_url'],
            'user' => [
                'name'  => trim($user->first_name . ' ' . $user->last_name),
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            'media' => [
                'title'       => $media->title ?? $media->original_name,
                'price_label' => number_format($amount, 0, ',', ' ') . ' FCFA',
            ],
        ]);
    }

    private function completePurchase(MediaPurchase $purchase, string $token): void
    {
        $purchase->update([
            'status'  => 'completed',
            'paid_at' => now(),
            'gateway_txn_id' => $token,
        ]);
    }
}

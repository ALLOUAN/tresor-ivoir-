<?php

namespace App\Services;

use App\Mail\GiftCardMail;
use App\Models\GiftCard;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Argent des bons cadeaux : à l'achat, la plateforme encaisse et retient sa commission (même taux
 * que les réservations payées en ligne) ; la part de l'établissement reste en attente jusqu'à
 * l'utilisation du bon, ou son expiration (le montant reste alors acquis à l'établissement).
 * Écritures uniques par (bon, type) : jamais de double encaissement.
 */
class GiftCardService
{
    public function __construct(private readonly BookingPaymentService $bookings) {}

    public function priceCard(GiftCard $card): void
    {
        $rate = $this->bookings->commissionPercent();
        $card->update(['commission_percent' => $rate, 'commission_amount_xof' => (int) round($card->amount_xof * $rate / 100)]);
    }

    /** Paiement confirmé : active le bon (code, validité), encaisse, et envoie le bon au bénéficiaire. */
    public function markPaid(GiftCard $card, string $method, ?string $txn = null): bool
    {
        $activated = DB::transaction(function () use ($card, $method, $txn) {
            $card = GiftCard::whereKey($card->id)->lockForUpdate()->firstOrFail();
            if ($card->status !== GiftCard::STATUS_PENDING) {
                return false;
            }
            $card->update([
                'status' => GiftCard::STATUS_ACTIVE, 'payment_method' => $method, 'gateway_txn_id' => $txn ?? $card->gateway_txn_id,
                'paid_at' => now(), 'expires_at' => now()->addMonths(GiftCard::VALIDITY_MONTHS)->toDateString(),
            ]);
            $this->collect($card);

            return true;
        });

        if ($activated) {
            $card->refresh();
            Mail::to($card->recipient_email)->queue(new GiftCardMail($card, 'recipient'));
            if (strcasecmp($card->buyer_email, $card->recipient_email) !== 0) {
                Mail::to($card->buyer_email)->queue(new GiftCardMail($card, 'buyer'));
            }
            $card->provider?->user?->notify(new VisitorSystemNotification(
                'Bon cadeau vendu',
                number_format($card->amount_xof, 0, ',', ' ').' FCFA offert à '.$card->recipient_name.' par '.$card->buyer_name.'.',
                route('provider.gift-cards.index'),
            ));
        }

        return $activated;
    }

    public function payFromWallet(GiftCard $card, User $client): void
    {
        DB::transaction(function () use ($card, $client) {
            $card = GiftCard::whereKey($card->id)->lockForUpdate()->firstOrFail();
            if ($card->status !== GiftCard::STATUS_PENDING) {
                return;
            }
            $wallet = Wallet::whereKey(Wallet::forUser($client)->id)->lockForUpdate()->firstOrFail();
            if ($wallet->balance_available_xof < $card->amount_xof) {
                throw new \RuntimeException(__('Solde insuffisant pour payer ce bon cadeau depuis votre portefeuille.'));
            }
            $this->row($wallet, 'balance_available_xof', WalletTransaction::TYPE_WALLET_PAYMENT, -$card->amount_xof, $card, 'Achat bon cadeau — '.$card->formattedCode());
        });
        $this->markPaid($card, 'wallet');
    }

    /** Utilisation du bon par l'établissement : la part en attente devient disponible. */
    public function redeem(GiftCard $card, User $by): void
    {
        DB::transaction(function () use ($card, $by) {
            $card = GiftCard::whereKey($card->id)->lockForUpdate()->firstOrFail();
            if (! $card->isUsable()) {
                throw new \RuntimeException(__('Ce bon n\'est pas utilisable (:s).', ['s' => $card->statusLabel()]));
            }
            $card->update(['status' => GiftCard::STATUS_REDEEMED, 'redeemed_at' => now(), 'redeemed_by' => $by->id]);
            $this->release($card, 'Bon cadeau '.$card->formattedCode().' utilisé');
        });
    }

    /** Bons arrivés à expiration sans être utilisés : marqués expirés, montant acquis à l'établissement. */
    public function expireOverdue(): int
    {
        $n = 0;
        GiftCard::where('status', GiftCard::STATUS_ACTIVE)->whereDate('expires_at', '<', now()->toDateString())->get()
            ->each(function (GiftCard $card) use (&$n) {
                DB::transaction(function () use ($card) {
                    $card->update(['status' => GiftCard::STATUS_EXPIRED]);
                    $this->release($card, 'Bon cadeau '.$card->formattedCode().' expiré (montant acquis)');
                });
                $n++;
            });

        return $n;
    }

    private function collect(GiftCard $card): void
    {
        $net = max(0, $card->amount_xof - $card->commission_amount_xof);
        try {
            $platform = Wallet::whereKey(Wallet::platform()->id)->lockForUpdate()->firstOrFail();
            $this->row($platform, 'balance_available_xof', 'gift_sale_collected', $card->amount_xof, $card, 'Bon cadeau encaissé — '.$card->formattedCode());
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || in_array((string) ($e->errorInfo[1] ?? ''), ['1062', '19'], true)) return;
            throw $e;
        }
        $platform->refresh();
        WalletTransaction::create([
            'wallet_id' => $platform->id, 'type' => 'gift_commission', 'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $card->commission_amount_xof, 'balance_before_xof' => $platform->balance_available_xof,
            'balance_after_xof' => $platform->balance_available_xof, 'gift_card_id' => $card->id, 'description' => 'Commission plateforme — bon cadeau',
        ]);
        if ($net > 0 && $card->provider) {
            $platform->refresh();
            $this->row($platform, 'balance_available_xof', 'gift_transfer_to_provider', -$net, $card, 'Transfert vers prestataire — bon cadeau');
            $pw = Wallet::whereKey(Wallet::forProvider($card->provider)->id)->lockForUpdate()->firstOrFail();
            $this->row($pw, 'balance_pending_xof', 'gift_credit_pending', $net, $card, 'Bon cadeau '.$card->formattedCode().' (en attente d\'utilisation)');
        }
    }

    private function release(GiftCard $card, string $description): void
    {
        $pending = WalletTransaction::where('gift_card_id', $card->id)->where('type', 'gift_credit_pending')->first();
        if (! $pending || WalletTransaction::where('gift_card_id', $card->id)->where('type', 'gift_credit_available')->exists()) {
            return;
        }
        $wallet = Wallet::whereKey($pending->wallet_id)->lockForUpdate()->firstOrFail();
        $amount = min((int) $pending->amount_xof, $wallet->balance_pending_xof);
        WalletTransaction::create([
            'wallet_id' => $wallet->id, 'type' => 'gift_credit_available', 'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $amount, 'balance_before_xof' => $wallet->balance_available_xof, 'balance_after_xof' => $wallet->balance_available_xof + $amount,
            'gift_card_id' => $card->id, 'description' => $description,
        ]);
        $wallet->decrementPending($amount);
        $wallet->incrementAvailable($amount);
    }

    private function row(Wallet $wallet, string $field, string $type, int $signed, GiftCard $card, string $description): void
    {
        $before = $wallet->{$field};
        WalletTransaction::create([
            'wallet_id' => $wallet->id, 'type' => $type, 'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $signed, 'balance_before_xof' => $before, 'balance_after_xof' => $before + $signed,
            'gift_card_id' => $card->id, 'description' => $description,
        ]);
        if ($field === 'balance_pending_xof') {
            $signed >= 0 ? $wallet->incrementPending($signed) : $wallet->decrementPending(-$signed);
        } else {
            $signed >= 0 ? $wallet->incrementAvailable($signed) : $wallet->decrementAvailable(-$signed);
        }
    }
}

<?php

namespace App\Services;

use App\Models\ArtistFollow;
use App\Models\ArtistProfile;
use App\Models\Artwork;
use App\Models\ArtworkEdition;
use App\Models\ArtworkOffer;
use App\Models\ArtworkOrder;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Ventes d'œuvres « marché » : devis (édition, offre acceptée, livraison, assurance), paiement intégral ou
 * en 3 fois (acompte réservant l'œuvre), réception confirmée, retour sous 7 jours, part de l'artiste
 * libérée après le délai de retour, offres et abonnés.
 *
 * L'encaissement final passe toujours par WalletService::creditSaleForArtworkOrder() (garanties
 * d'idempotence existantes) ; pour un paiement en plusieurs fois, les échéances déjà reçues par la
 * plateforme sont déduites de cet encaissement final.
 */
class ArtSalesService
{
    /** Part du prix réglée à la réservation, puis deux échéances à 30 et 60 jours. */
    public const LAYAWAY_DEPOSIT_PERCENT = 30;

    public const LAYAWAY_GRACE_DAYS = 7;

    public function __construct(
        private readonly ArtworkPricingService $pricing,
        private readonly WalletService $wallet,
    ) {}

    /**
     * Devis d'achat.
     *
     * @param  array{edition_id?: ?int, offer_id?: ?int, delivery_mode?: string, insurance?: bool, plan?: string}  $in
     * @return array{price: int, label: ?string, edition: ?ArtworkEdition, offer: ?ArtworkOffer, delivery_fee: int, insurance: int, total: int, plan: string, installments: array, lines: array}
     */
    public function quote(Artwork $artwork, ?User $user, array $in): array
    {
        $profile = ArtistProfile::forProvider($artwork->provider);
        $edition = null;
        $offer = null;
        $price = (int) $artwork->price_xof;
        $label = null;

        if (! empty($in['edition_id'])) {
            $edition = $artwork->editions()->where('is_active', true)->find($in['edition_id']);
            if (! $edition || $edition->remaining() < 1) {
                throw new \InvalidArgumentException(__('Cette édition est épuisée.'));
            }
            $price = $edition->price_xof;
            $label = $edition->label;
        } elseif (! empty($in['offer_id'])) {
            $offer = ArtworkOffer::where('artwork_id', $artwork->id)->where('user_id', $user?->id)->find($in['offer_id']);
            if (! $offer || $offer->payablePrice() === null) {
                throw new \InvalidArgumentException(__('Cette offre n\'est plus payable (expirée ou non acceptée).'));
            }
            $price = $offer->payablePrice();
        }
        if (! $edition && ($artwork->stock_quantity < 1 || $artwork->status !== Artwork::STATUS_PUBLISHED)) {
            throw new \InvalidArgumentException(__('L\'original n\'est plus disponible.'));
        }

        $options = $profile->deliveryOptions($artwork);
        $mode = $in['delivery_mode'] ?? 'pickup';
        if (! isset($options[$mode])) {
            throw new \InvalidArgumentException(__('Mode de livraison non proposé par l\'artiste.'));
        }
        $fee = $options[$mode]['fee'];
        $insurance = ($in['insurance'] ?? false) && $mode !== 'pickup' ? $profile->insuranceFor($price) : 0;
        $total = $price + $fee + $insurance;

        $plan = ($in['plan'] ?? 'full') === 'layaway' ? 'layaway' : 'full';
        if ($plan === 'layaway' && (! $artwork->layaway_allowed || $edition)) {
            throw new \InvalidArgumentException(__('Le paiement en plusieurs fois n\'est pas proposé pour cette œuvre.'));
        }
        $installments = [];
        if ($plan === 'layaway') {
            $deposit = (int) (ceil($total * self::LAYAWAY_DEPOSIT_PERCENT / 100 / 100) * 100);
            $rest = $total - $deposit;
            $second = (int) floor($rest / 2 / 100) * 100;
            $today = now('Africa/Abidjan')->startOfDay();
            $installments = [
                ['n' => 1, 'amount_xof' => $deposit, 'due_on' => $today->toDateString(), 'paid_at' => null],
                ['n' => 2, 'amount_xof' => $second, 'due_on' => $today->copy()->addDays(30)->toDateString(), 'paid_at' => null],
                ['n' => 3, 'amount_xof' => $rest - $second, 'due_on' => $today->copy()->addDays(60)->toDateString(), 'paid_at' => null],
            ];
        }

        $lines = [['label' => $label ? __('Édition « :l »', ['l' => $label]) : ($offer ? __('Prix convenu (offre acceptée)') : __('Œuvre originale')), 'amount_xof' => $price]];
        if ($fee) {
            $lines[] = ['label' => $options[$mode]['label'], 'amount_xof' => $fee];
        }
        if ($insurance) {
            $lines[] = ['label' => __('Assurance transport (:p %)', ['p' => rtrim(rtrim(number_format($profile->insurance_percent, 2, ',', ''), '0'), ',')]), 'amount_xof' => $insurance];
        }

        return compact('price', 'label', 'edition', 'offer', 'total', 'plan', 'installments', 'lines') + ['delivery_fee' => $fee, 'insurance' => $insurance, 'mode' => $mode];
    }

    /** @param array<string, mixed> $in */
    public function createOrder(Artwork $artwork, User $user, array $in): ArtworkOrder
    {
        $q = $this->quote($artwork, $user, $in);
        $calc = $this->pricing->compute($q['price']); // commission sur le prix de l'œuvre seulement

        return ArtworkOrder::create([
            'artwork_id' => $artwork->id, 'provider_id' => $artwork->provider_id, 'buyer_user_id' => $user->id,
            'artwork_edition_id' => $q['edition']?->id, 'edition_label' => $q['label'], 'edition_size' => $q['edition']?->edition_size,
            'artwork_offer_id' => $q['offer']?->id,
            'unit_price_xof' => $q['price'], 'amount_total_xof' => $q['total'],
            'commission_percent' => $calc['commission_percent'], 'commission_amount_xof' => $calc['commission_amount_xof'],
            'artist_net_amount_xof' => $q['total'] - $calc['commission_amount_xof'],
            'currency' => 'XOF', 'status' => ArtworkOrder::STATUS_PENDING_PAYMENT,
            'delivery_mode' => $q['mode'], 'delivery_address' => $in['delivery_address'] ?? null, 'delivery_city' => $in['delivery_city'] ?? null,
            'delivery_fee_xof' => $q['delivery_fee'], 'insurance_xof' => $q['insurance'],
            'payment_plan' => $q['plan'], 'installments' => $q['installments'] ?: null,
            'buyer_name' => trim($user->first_name.' '.$user->last_name) ?: $user->email, 'buyer_email' => $user->email,
            'buyer_phone' => ($in['phone'] ?? null) ?: $user->phone, 'ip_address' => $in['ip'] ?? null,
            'metadata' => ['lines' => $q['lines']],
        ]);
    }

    /** Montant du prochain versement : totalité, ou prochaine échéance. */
    public function dueNow(ArtworkOrder $order): int
    {
        return $order->payment_plan === 'layaway' ? (int) ($order->nextInstallment()['amount_xof'] ?? 0) : (int) $order->amount_total_xof;
    }

    /**
     * Débite le solde du client pour le versement dû. Retourne 'paid' (commande soldée, à finaliser)
     * ou 'installment' (échéance enregistrée).
     */
    public function payFromWallet(ArtworkOrder $order, User $client): string
    {
        return DB::transaction(function () use ($order, $client) {
            $order = ArtworkOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $amount = $this->dueNow($order);
            if ($amount <= 0) {
                throw new \RuntimeException(__('Rien à payer pour cette commande.'));
            }
            $w = Wallet::whereKey(Wallet::forUser($client)->id)->lockForUpdate()->firstOrFail();
            if ($w->balance_available_xof < $amount) {
                throw new \RuntimeException(__('Solde insuffisant pour payer depuis votre portefeuille.'));
            }
            $n = $order->payment_plan === 'layaway' ? (int) $order->nextInstallment()['n'] : null;
            $this->row($w, $order, $n ? 'art_wallet_installment_'.$n : WalletTransaction::TYPE_WALLET_PAYMENT, -$amount, __('Paiement d\'œuvre').' — '.$order->reference);
            $order->update(['gateway' => 'wallet']);
            if ($n) {
                return $this->settleInstallment($order, $n) ? 'paid' : 'installment';
            }

            return 'paid';
        });
    }

    /**
     * Échéance reçue (solde ou CinetPay). L'acompte réserve l'œuvre ; la dernière échéance rend la
     * commande « à finaliser » (retourne true).
     */
    public function settleInstallment(ArtworkOrder $order, int $n): bool
    {
        return DB::transaction(function () use ($order, $n) {
            $order = ArtworkOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $plan = collect($order->installments ?? []);
            $inst = $plan->firstWhere('n', $n);
            if (! $inst || ! empty($inst['paid_at'])) {
                return false;
            }
            $platform = Wallet::whereKey(Wallet::platform()->id)->lockForUpdate()->firstOrFail();
            try {
                $this->row($platform, $order, 'art_installment_'.$n, (int) $inst['amount_xof'], 'Échéance '.$n.' — '.$order->reference);
            } catch (QueryException $e) {
                if ($this->isDuplicate($e)) {
                    return false;
                }
                throw $e;
            }
            $order->update([
                'installments' => $plan->map(fn ($i) => $i['n'] === $n ? array_merge($i, ['paid_at' => now()->toIso8601String()]) : $i)->all(),
                'amount_paid_xof' => $order->amount_paid_xof + (int) $inst['amount_xof'], 'pending_installment' => null,
            ]);
            if ($n === 1) {
                $artwork = Artwork::whereKey($order->artwork_id)->lockForUpdate()->first();
                if ($artwork && $artwork->stock_quantity > 0) {
                    $artwork->decrement('stock_quantity');
                    if ($artwork->fresh()->stock_quantity <= 0) {
                        $artwork->update(['status' => Artwork::STATUS_SOLD]);
                    }
                }
                $order->update(['status' => ArtworkOrder::STATUS_RESERVED]);
                $order->buyer?->notify(new VisitorSystemNotification(__('Œuvre réservée'), __('Acompte reçu pour ":t". Prochaine échéance le :d.', ['t' => $order->artwork?->title, 'd' => \Carbon\Carbon::parse($plan->firstWhere('n', 2)['due_on'])->format('d/m/Y')]), route('visitor.art-orders.index')));
                $order->provider?->user?->notify(new VisitorSystemNotification('Œuvre réservée', '"'.$order->artwork?->title.'" est réservée par '.$order->buyer_name.' (paiement en 3 fois). Remise après le solde.', route('provider.art-orders.show', $order)));
            }

            return collect($order->fresh()->installments)->every(fn ($i) => ! empty($i['paid_at']));
        });
    }

    /** Commande à paiement échelonné entièrement soldée : encaissement final et passage en « payée ». */
    public function completeLayaway(ArtworkOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $order = ArtworkOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status !== ArtworkOrder::STATUS_RESERVED) {
                return;
            }
            $order->update(['status' => ArtworkOrder::STATUS_PAID, 'paid_at' => now()]);
            $this->wallet->creditSaleForArtworkOrder($order);
            $order->buyer?->notify(new VisitorSystemNotification(__('Œuvre soldée'), __('":t" est entièrement réglée : l\'artiste prépare la remise.', ['t' => $order->artwork?->title]), route('visitor.art-orders.index')));
            $order->provider?->user?->notify(new VisitorSystemNotification('Œuvre soldée', '"'.$order->artwork?->title.'" est entièrement payée : vous pouvez la remettre ou l\'expédier.', route('provider.art-orders.show', $order)));
        });
    }

    /** Édition limitée ou offre au moment du paiement complet (appelé depuis la finalisation d'achat). */
    public function onPaid(ArtworkOrder $order): void
    {
        if ($order->artwork_edition_id) {
            $edition = ArtworkEdition::whereKey($order->artwork_edition_id)->lockForUpdate()->first();
            if ($edition) {
                $edition->increment('sold_count');
                $order->update(['edition_number' => $edition->fresh()->sold_count]);
            }
        }
        if ($order->artwork_offer_id) {
            ArtworkOffer::whereKey($order->artwork_offer_id)->update(['status' => 'paid']);
        }
        $order->update(['amount_paid_xof' => $order->amount_total_xof]);
    }

    /* ── Réception, retour, libération ── */

    public function confirmReceipt(ArtworkOrder $order): void
    {
        if (! in_array($order->status, [ArtworkOrder::STATUS_PAID, ArtworkOrder::STATUS_SHIPPED], true)) {
            throw new \RuntimeException(__('Cette commande ne peut pas être confirmée.'));
        }
        $order->update(['status' => ArtworkOrder::STATUS_DELIVERED, 'delivered_at' => $order->delivered_at ?? now(), 'received_at' => now()]);
        $order->provider?->user?->notify(new VisitorSystemNotification('Œuvre reçue', $order->buyer_name.' a confirmé la réception de "'.$order->artwork?->title.'". Votre part sera disponible dans '.ArtworkOrder::RETURN_DAYS.' jours, sauf retour.', route('provider.art-orders.show', $order)));
    }

    public function requestReturn(ArtworkOrder $order, string $reason): void
    {
        if (! $order->canRequestReturn()) {
            throw new \RuntimeException(__('Le délai de retour est dépassé ou ce retour n\'est pas possible.'));
        }
        $order->update(['return_status' => 'requested', 'return_reason' => $reason, 'return_requested_at' => now()]);
        $order->provider?->user?->notify(new VisitorSystemNotification('Demande de retour', $order->buyer_name.' demande le retour de "'.$order->artwork?->title.'" : '.$reason, route('provider.art-orders.show', $order)));
        foreach (User::where('role', 'admin')->where('is_active', true)->get() as $admin) {
            $admin->notify(new VisitorSystemNotification('Demande de retour d\'œuvre', $order->reference.' — '.$reason, route('admin.art-orders.show', $order)));
        }
    }

    /**
     * Œuvre revenue chez l'artiste dans son état d'origine : remboursement intégral sur le solde de
     * l'acheteur (reprise sur la part de l'artiste + commission), stock remis en vente.
     */
    public function refundReturn(ArtworkOrder $order, User $actor): void
    {
        DB::transaction(function () use ($order, $actor) {
            $order = ArtworkOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($order->return_status, ['requested', 'accepted'], true)) {
                throw new \RuntimeException(__('Aucun retour en cours pour cette commande.'));
            }
            $this->wallet->recordManualRefundForArtworkOrder($order, $actor, 'Retour sous '.ArtworkOrder::RETURN_DAYS.' jours');
            $this->creditBuyer($order, (int) $order->amount_total_xof, 'Remboursement — retour d\'œuvre');
            $order->update(['return_status' => 'refunded']);
            $this->restock($order);
        });
        $order->buyer?->notify(new VisitorSystemNotification(__('Retour remboursé'), __(':a FCFA crédités sur votre solde pour ":t".', ['a' => number_format((int) $order->amount_total_xof, 0, ',', ' '), 't' => $order->artwork?->title]), route('visitor.art-orders.index')));
    }

    /** Crédite l'acheteur ; la commission déjà retenue par la plateforme est reprise sur son portefeuille. */
    private function creditBuyer(ArtworkOrder $order, int $amount, string $reason): void
    {
        if ($amount <= 0 || ! $order->buyer) {
            return;
        }
        $platform = Wallet::whereKey(Wallet::platform()->id)->lockForUpdate()->firstOrFail();
        $commission = min($amount, (int) $order->commission_amount_xof);
        if ($commission > 0) {
            $this->row($platform, $order, 'art_refund_platform', -$commission, 'Remboursement (commission) — '.$order->reference);
        }
        $client = Wallet::whereKey(Wallet::forUser($order->buyer)->id)->lockForUpdate()->firstOrFail();
        $this->row($client, $order, 'art_refund_client', $amount, $reason.' — '.$order->reference);
    }

    private function restock(ArtworkOrder $order): void
    {
        if ($order->artwork_edition_id) {
            ArtworkEdition::whereKey($order->artwork_edition_id)->where('sold_count', '>', 0)->decrement('sold_count');

            return;
        }
        $artwork = Artwork::whereKey($order->artwork_id)->lockForUpdate()->first();
        if ($artwork) {
            $artwork->increment('stock_quantity');
            if ($artwork->status === Artwork::STATUS_SOLD) {
                $artwork->update(['status' => Artwork::STATUS_PUBLISHED]);
            }
        }
    }

    /**
     * Tâche planifiée : part de l'artiste libérée après le délai de retour (pas de retour en cours),
     * relance et annulation des paiements échelonnés en retard, offres expirées.
     *
     * @return array<string, int>
     */
    public function processDaily(): array
    {
        $stats = ['released' => 0, 'reminded' => 0, 'cancelled' => 0, 'offers_expired' => 0];

        ArtworkOrder::where('status', ArtworkOrder::STATUS_DELIVERED)->whereNotNull('received_at')
            ->where('received_at', '<=', now()->subDays(ArtworkOrder::RETURN_DAYS))
            ->where(fn ($q) => $q->whereNull('return_status')->orWhere('return_status', 'refused'))
            ->whereNotIn('id', WalletTransaction::where('type', WalletTransaction::TYPE_ART_CREDIT_AVAILABLE)->whereNotNull('artwork_order_id')->select('artwork_order_id'))
            ->get()->each(function (ArtworkOrder $o) use (&$stats) {
                $this->wallet->releaseArtworkOrderCredit($o);
                $stats['released']++;
            });

        $today = now('Africa/Abidjan')->startOfDay();
        ArtworkOrder::where('status', ArtworkOrder::STATUS_RESERVED)->get()->each(function (ArtworkOrder $o) use ($today, &$stats) {
            $next = $o->nextInstallment();
            if (! $next) {
                return;
            }
            $due = \Carbon\Carbon::parse($next['due_on'], 'Africa/Abidjan');
            if ($due->copy()->addDays(self::LAYAWAY_GRACE_DAYS)->lt($today)) {
                $this->cancelLayaway($o);
                $stats['cancelled']++;
            } elseif ($due->diffInDays($today, false) >= -3 && $due->gte($today)) {
                $o->buyer?->notify(new VisitorSystemNotification(__('Échéance à venir'), __(':a FCFA à régler avant le :d pour ":t".', ['a' => number_format((int) $next['amount_xof'], 0, ',', ' '), 'd' => $due->format('d/m/Y'), 't' => $o->artwork?->title]), route('visitor.art-orders.index')));
                $stats['reminded']++;
            }
        });

        $stats['offers_expired'] = ArtworkOffer::whereIn('status', ['accepted', 'countered'])->where('expires_at', '<', now())->update(['status' => 'expired']);

        return $stats;
    }

    /**
     * Échéance impayée au-delà du délai de grâce : réservation annulée, œuvre remise en vente. Les échéances
     * 2 et 3 versées sont remboursées sur le solde ; l'acompte (non remboursable, conditions affichées)
     * revient à l'artiste en dédommagement, commission déduite.
     */
    public function cancelLayaway(ArtworkOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $order = ArtworkOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status !== ArtworkOrder::STATUS_RESERVED) {
                return;
            }
            $paid = collect($order->installments ?? [])->filter(fn ($i) => ! empty($i['paid_at']));
            $deposit = (int) ($paid->firstWhere('n', 1)['amount_xof'] ?? 0);
            $refund = (int) $paid->where('n', '>', 1)->sum('amount_xof');
            $platform = Wallet::whereKey(Wallet::platform()->id)->lockForUpdate()->firstOrFail();
            if ($refund > 0 && $order->buyer) {
                $this->row($platform, $order, 'art_refund_platform', -$refund, 'Échéances remboursées — '.$order->reference);
                $this->row(Wallet::whereKey(Wallet::forUser($order->buyer)->id)->lockForUpdate()->firstOrFail(), $order, 'art_refund_client', $refund, 'Réservation annulée — échéances remboursées');
            }
            $net = $deposit - (int) round($deposit * (float) $order->commission_percent / 100);
            if ($net > 0 && $order->provider) {
                $this->row($platform, $order, WalletTransaction::TYPE_ART_TRANSFER_TO_ARTIST, -$net, 'Acompte conservé — dédommagement artiste — '.$order->reference);
                $this->row(Wallet::whereKey(Wallet::forProvider($order->provider)->id)->lockForUpdate()->firstOrFail(), $order, WalletTransaction::TYPE_ART_CREDIT_AVAILABLE, $net, 'Acompte conservé (réservation annulée) — '.$order->reference);
            }
            $order->update(['status' => ArtworkOrder::STATUS_CANCELLED]);
            $this->restock($order);
            $order->buyer?->notify(new VisitorSystemNotification(__('Réservation annulée'), __('Échéance non réglée : la réservation de ":t" est annulée. :a FCFA remboursés sur votre solde (acompte non remboursable).', ['t' => $order->artwork?->title, 'a' => number_format($refund, 0, ',', ' ')]), route('visitor.art-orders.index')));
        });
    }

    /* ── Offres ── */

    public function makeOffer(Artwork $artwork, User $user, int $amount, ?string $message): ArtworkOffer
    {
        if (! $artwork->accepts_offers || ! $artwork->isAvailable()) {
            throw new \InvalidArgumentException(__('Cette œuvre n\'accepte pas d\'offre.'));
        }
        if ($amount >= $artwork->price_xof) {
            throw new \InvalidArgumentException(__('Votre offre est au moins égale au prix : achetez directement.'));
        }
        if ($amount < (int) round($artwork->price_xof * 0.5)) {
            throw new \InvalidArgumentException(__('Offre trop basse : proposez au moins la moitié du prix affiché.'));
        }
        if (ArtworkOffer::where('artwork_id', $artwork->id)->where('user_id', $user->id)->whereIn('status', ['pending', 'countered'])->exists()) {
            throw new \InvalidArgumentException(__('Vous avez déjà une offre en cours sur cette œuvre.'));
        }
        $auto = $artwork->min_offer_xof && $amount < $artwork->min_offer_xof;
        $offer = ArtworkOffer::create([
            'artwork_id' => $artwork->id, 'provider_id' => $artwork->provider_id, 'user_id' => $user->id, 'amount_xof' => $amount,
            'message' => $message, 'status' => $auto ? 'declined' : 'pending', 'responded_at' => $auto ? now() : null,
        ]);
        if (! $auto) {
            $artwork->provider?->user?->notify(new VisitorSystemNotification('Nouvelle offre', number_format($amount, 0, ',', ' ').' FCFA proposés pour "'.$artwork->title.'" (prix : '.number_format((int) $artwork->price_xof, 0, ',', ' ').' FCFA).', route('provider.art-offers.index')));
        }

        return $offer;
    }

    public function respondOffer(ArtworkOffer $offer, string $action, ?int $counter = null): void
    {
        if ($offer->status !== 'pending') {
            throw new \RuntimeException(__('Cette offre a déjà reçu une réponse.'));
        }
        if ($action === 'counter' && (! $counter || $counter <= $offer->amount_xof || $counter >= $offer->artwork->price_xof)) {
            throw new \RuntimeException(__('La contre-offre doit être entre l\'offre et le prix affiché.'));
        }
        $offer->update([
            'status' => match ($action) { 'accept' => 'accepted', 'counter' => 'countered', default => 'declined' },
            'counter_amount_xof' => $action === 'counter' ? $counter : null,
            'expires_at' => $action === 'decline' ? null : now()->addHours(ArtworkOffer::VALID_HOURS),
            'responded_at' => now(),
        ]);
        $title = $offer->artwork?->title;
        $offer->user?->notify(new VisitorSystemNotification(
            match ($action) { 'accept' => __('Offre acceptée'), 'counter' => __('Contre-proposition'), default => __('Offre refusée') },
            match ($action) {
                'accept' => __('Votre offre de :a FCFA pour ":t" est acceptée : réglez-la sous :h h.', ['a' => number_format($offer->amount_xof, 0, ',', ' '), 't' => $title, 'h' => ArtworkOffer::VALID_HOURS]),
                'counter' => __('L\'artiste propose :a FCFA pour ":t" (valable :h h).', ['a' => number_format((int) $counter, 0, ',', ' '), 't' => $title, 'h' => ArtworkOffer::VALID_HOURS]),
                default => __('Votre offre pour ":t" n\'a pas été retenue.', ['t' => $title]),
            },
            $offer->artwork ? route('art.show', $offer->artwork->slug) : route('art.index'),
        ));
    }

    public function acceptCounter(ArtworkOffer $offer): void
    {
        if ($offer->status !== 'countered' || $offer->expires_at?->isPast()) {
            throw new \RuntimeException(__('Cette contre-proposition n\'est plus valable.'));
        }
        $offer->update(['status' => 'accepted', 'expires_at' => now()->addHours(ArtworkOffer::VALID_HOURS)]);
    }

    /* ── Abonnés ── */

    public function notifyFollowers(Artwork $artwork): int
    {
        $name = ArtistProfile::where('provider_id', $artwork->provider_id)->value('display_name') ?? $artwork->provider?->name;
        $n = 0;
        ArtistFollow::with('user')->where('provider_id', $artwork->provider_id)->get()->each(function (ArtistFollow $f) use ($artwork, $name, &$n) {
            $f->user?->notify(new VisitorSystemNotification(__('Nouvelle œuvre de :a', ['a' => $name]), '"'.$artwork->title.'" — '.number_format((int) $artwork->price_xof, 0, ',', ' ').' FCFA', route('art.show', $artwork->slug)));
            $n++;
        });

        return $n;
    }

    private function row(Wallet $wallet, ArtworkOrder $order, string $type, int $signed, string $description): void
    {
        $before = $wallet->balance_available_xof;
        WalletTransaction::create([
            'wallet_id' => $wallet->id, 'type' => $type, 'status' => WalletTransaction::STATUS_COMPLETED, 'amount_xof' => $signed,
            'balance_before_xof' => $before, 'balance_after_xof' => $before + $signed, 'artwork_order_id' => $order->id, 'description' => $description,
        ]);
        $signed >= 0 ? $wallet->incrementAvailable($signed) : $wallet->decrementAvailable(-$signed);
    }

    private function isDuplicate(QueryException $e): bool
    {
        return in_array((string) ($e->errorInfo[1] ?? ''), ['1062', '19'], true) || str_contains($e->getMessage(), 'Duplicate');
    }
}

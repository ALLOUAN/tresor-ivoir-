<?php

namespace App\Services;

use App\Models\ArtCommission;
use App\Models\ArtistProfile;
use App\Models\Provider;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;

/** Parcours d'une commande sur mesure, de la demande à la livraison. */
class ArtCommissionService
{
    public function __construct(private readonly ArtCommissionPaymentService $payments, private readonly ArtworkPricingService $pricing) {}

    public function request(Provider $artist, User $user, array $d): ArtCommission
    {
        $profile = ArtistProfile::forProvider($artist);
        if (! $profile->accepts_commissions) {
            throw new \InvalidArgumentException(__('Cet artiste ne prend pas de commande sur mesure pour le moment.'));
        }
        $c = ArtCommission::create($d + ['provider_id' => $artist->id, 'user_id' => $user->id, 'status' => 'requested', 'commission_percent' => $this->pricing->commissionPercent()]);
        $artist->user?->notify(new VisitorSystemNotification('Demande sur mesure', $user->first_name.' : « '.$c->subject.' »'.($c->budget_xof ? ' — budget '.number_format($c->budget_xof, 0, ',', ' ').' FCFA' : ''), route('provider.art-commissions.index')));

        return $c;
    }

    public function quote(ArtCommission $c, int $price, int $days, ?string $message): void
    {
        $this->expect($c, ['requested', 'quoted']);
        $percent = ArtistProfile::where('provider_id', $c->provider_id)->value('commission_deposit_percent') ?? 50;
        $c->update(['status' => 'quoted', 'quote_xof' => $price, 'quote_days' => $days, 'quote_message' => $message,
            'deposit_xof' => (int) (ceil($price * $percent / 100 / 100) * 100)]);
        $this->tellClient($c, __('Devis reçu'), __(':a FCFA, réalisation en :d jours. Versez l\'acompte pour lancer l\'esquisse.', ['a' => number_format($price, 0, ',', ' '), 'd' => $days]));
    }

    public function decline(ArtCommission $c, ?string $message): void
    {
        $this->expect($c, ['requested', 'quoted']);
        $c->update(['status' => 'declined', 'quote_message' => $message]);
        $this->tellClient($c, __('Demande sur mesure déclinée'), $message ?: __('L\'artiste ne peut pas réaliser cette commande.'));
    }

    /** Paiement depuis le solde de l'étape due (acompte ou solde). */
    public function payFromWallet(ArtCommission $c, User $client): void
    {
        $stage = $c->dueStage() ?? throw new \RuntimeException(__('Rien à payer pour le moment.'));
        $this->payments->forStage($stage)->payFromWallet($c, $client);
        $this->afterPayment($c->fresh(), $stage);
    }

    public function markPaid(ArtCommission $c, string $stage, ?string $txn): void
    {
        if ($this->payments->forStage($stage)->markPaid($c, $txn)) {
            $this->afterPayment($c->fresh(), $stage);
        }
    }

    private function afterPayment(ArtCommission $c, string $stage): void
    {
        $c->provider?->user?->notify(new VisitorSystemNotification(
            $stage === 'deposit' ? 'Acompte reçu — sur mesure' : 'Solde reçu — sur mesure',
            $c->reference.' — « '.$c->subject.' » : '.($stage === 'deposit' ? 'vous pouvez préparer l\'esquisse.' : 'finalisez et livrez l\'œuvre.'),
            route('provider.art-commissions.index'),
        ));
    }

    /** @param array<int, string> $images */
    public function sendSketch(ArtCommission $c, array $images, ?string $note): void
    {
        $this->expect($c, ['deposit_paid', 'revision']);
        $c->update(['status' => 'sketch_sent', 'sketches' => array_values(array_merge($c->sketches ?? [], $images)), 'sketch_note' => $note]);
        $this->tellClient($c, __('Esquisse à valider'), __('L\'artiste vous propose une esquisse pour « :s ».', ['s' => $c->subject]));
    }

    public function approveSketch(ArtCommission $c): void
    {
        $this->expect($c, ['sketch_sent']);
        $c->update(['status' => 'sketch_approved']);
        $c->provider?->user?->notify(new VisitorSystemNotification('Esquisse validée', $c->reference.' — le client a validé l\'esquisse ; le solde est demandé.', route('provider.art-commissions.index')));
    }

    public function requestRevision(ArtCommission $c, string $note): void
    {
        $this->expect($c, ['sketch_sent']);
        if ($c->revisions_used >= ArtCommission::MAX_REVISIONS) {
            throw new \RuntimeException(__('Vous avez utilisé vos :n retouches incluses.', ['n' => ArtCommission::MAX_REVISIONS]));
        }
        $c->update(['status' => 'revision', 'revision_note' => $note, 'revisions_used' => $c->revisions_used + 1]);
        $c->provider?->user?->notify(new VisitorSystemNotification('Retouches demandées', $c->reference.' : '.$note, route('provider.art-commissions.index')));
    }

    public function deliver(ArtCommission $c): void
    {
        $this->expect($c, ['balance_paid']);
        $c->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->tellClient($c, __('Œuvre sur mesure livrée'), __('« :s » vous a été remise. Merci !', ['s' => $c->subject]));
    }

    /** Annulation : par le client avant l'acompte ; par l'artiste à tout moment avant la livraison (remboursement intégral). */
    public function cancel(ArtCommission $c, bool $byArtist): void
    {
        if ($byArtist) {
            $this->expect($c, ['requested', 'quoted', 'deposit_paid', 'sketch_sent', 'revision', 'sketch_approved', 'balance_paid']);
            foreach (['deposit', 'balance'] as $stage) {
                $paid = (int) \App\Models\WalletTransaction::where('art_commission_id', $c->id)->where('type', 'art_commission_'.$stage.'_collected')->value('amount_xof');
                if ($paid > 0) {
                    $this->payments->forStage($stage)->refund($c, $paid, __('Commande sur mesure annulée par l\'artiste'));
                }
            }
            $this->tellClient($c, __('Commande sur mesure annulée'), __('L\'artiste a annulé « :s ». Les sommes versées sont remboursées sur votre solde.', ['s' => $c->subject]));
        } else {
            $this->expect($c, ['requested', 'quoted']);
        }
        $c->update(['status' => 'cancelled']);
    }

    private function expect(ArtCommission $c, array $statuses): void
    {
        if (! in_array($c->status, $statuses, true)) {
            throw new \RuntimeException(__('Action impossible à cette étape (:s).', ['s' => $c->statusLabel()]));
        }
    }

    private function tellClient(ArtCommission $c, string $title, string $body): void
    {
        $c->user?->notify(new VisitorSystemNotification($title, $c->reference.' — '.$body, route('visitor.art-commissions.index')));
    }
}

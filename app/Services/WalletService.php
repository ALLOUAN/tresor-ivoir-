<?php

namespace App\Services;

use App\Mail\ReservationDepositConfirmedMail;
use App\Mail\ReservationReceivedProviderMail;
use App\Models\ArtworkOrder;
use App\Models\PaymentSetting;
use App\Models\PayoutAuditLog;
use App\Models\PayoutRequest;
use App\Models\Provider;
use App\Models\ProviderDebt;
use App\Models\Reservation;
use App\Models\TouristVisit;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Notifications\PayoutSuspendedAdminNotification;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Point d'entrée unique pour tout mouvement d'argent du wallet — aucun contrôleur
 * ne doit écrire directement dans wallets/wallet_transactions.
 *
 * Le solde d'un wallet plateforme après traitement d'une réservation correspond
 * exactement à la commission conservée (acompte encaissé, part prestataire transférée) :
 * ce n'est pas un solde brut cumulatif de tous les acomptes, seulement ce qui reste
 * réellement à la plateforme.
 */
class WalletService
{
    public function __construct(
        private readonly PayoutRiskEngine $riskEngine,
        private readonly PayoutOtpService $otpService,
    ) {}

    public function creditDepositForReservation(Reservation $reservation): void
    {
        $depositXof = (int) $reservation->deposit_amount_xof;
        if ($depositXof <= 0) {
            return;
        }

        DB::transaction(function () use ($reservation, $depositXof) {
            $alreadyProcessed = WalletTransaction::where('reservation_id', $reservation->id)
                ->where('type', WalletTransaction::TYPE_DEPOSIT_COLLECTED)
                ->exists();
            if ($alreadyProcessed) {
                return;
            }

            // Valeurs figées sur la réservation au moment d'initiate() — jamais recalculées
            // depuis PaymentSetting ici, sinon un changement de taux entre la création de la
            // réservation et la confirmation du paiement modifierait rétroactivement ce que
            // le client a accepté de payer.
            $commissionXof = max(0, (int) $reservation->commission_amount_xof);
            $providerNetXof = max(0, $depositXof - $commissionXof);

            $platformWallet = $this->lockedWallet(Wallet::platform()->id);

            try {
                $this->insertRow($platformWallet, 'balance_available_xof', WalletTransaction::TYPE_DEPOSIT_COLLECTED, $depositXof, $reservation, "Acompte encaissé — {$reservation->reference}");
            } catch (QueryException $e) {
                if ($this->isDuplicateKeyViolation($e)) {
                    return; // course concurrente (webhook + retour navigateur) : déjà traité
                }
                throw $e;
            }

            // Ligne de revenu informative : n'affecte pas le solde une seconde fois (déjà
            // inclus ci-dessus) — le solde plateforme, une fois la part prestataire transférée
            // ci-dessous, correspondra exactement à ce montant de commission.
            $platformWallet->refresh();
            WalletTransaction::create([
                'wallet_id' => $platformWallet->id,
                'type' => WalletTransaction::TYPE_COMMISSION,
                'status' => WalletTransaction::STATUS_COMPLETED,
                'amount_xof' => $commissionXof,
                'balance_before_xof' => $platformWallet->balance_available_xof,
                'balance_after_xof' => $platformWallet->balance_available_xof,
                'reservation_id' => $reservation->id,
                'description' => "Commission plateforme — {$reservation->reference}",
            ]);

            if ($providerNetXof > 0 && $reservation->provider_id) {
                $platformWallet->refresh();
                $this->insertRow($platformWallet, 'balance_available_xof', WalletTransaction::TYPE_TRANSFER_TO_PROVIDER, -$providerNetXof, $reservation, "Transfert vers prestataire — {$reservation->reference}");

                $providerWallet = $this->lockedWallet(Wallet::forProvider($reservation->provider)->id);
                $this->insertRow($providerWallet, 'balance_pending_xof', WalletTransaction::TYPE_PROVIDER_CREDIT_PENDING, $providerNetXof, $reservation, "Réservation {$reservation->reference}");
            }
        });
    }

    /**
     * Point d'entrée unique des notifications « réservation confirmée » (client + prestataire)
     * — appelé depuis les deux chemins de paiement (CinetPay et wallet), chacun déjà protégé
     * contre le double traitement par son propre verrou/garde d'idempotence (lockForUpdate +
     * vérification de statut avant appel), donc pas de garde supplémentaire ici : centraliser
     * évite simplement de dupliquer la résolution des destinataires entre les deux appelants.
     */
    public function notifyReservationConfirmed(Reservation $reservation): void
    {
        if ($reservation->email) {
            Mail::to($reservation->email)->queue(new ReservationDepositConfirmedMail($reservation));
        }

        $provider = $reservation->provider;
        $providerUser = $provider?->user;
        $providerEmail = $provider?->email ?: $providerUser?->email;

        if ($providerEmail) {
            Mail::to($providerEmail)->queue(new ReservationReceivedProviderMail($reservation));
        }

        if ($providerUser) {
            $providerUser->notify(new VisitorSystemNotification(
                'Nouvelle réservation',
                $reservation->full_name.' a réservé "'.$reservation->accommodation_name.'" — '.$reservation->reference,
                route('provider.reservations.show', $reservation),
            ));
        }
    }

    /**
     * Répartit le paiement d'une commande d'œuvre : commission plateforme encaissée
     * (disponible immédiatement) puis part artiste créditée en attente (balance_pending_xof)
     * jusqu'à libération à la livraison (cf. Phase 3 du plan — non implémentée ici).
     * Copie structurelle de creditDepositForReservation() : mêmes garanties d'idempotence
     * et de verrouillage, adaptées à ArtworkOrder.
     */
    public function creditSaleForArtworkOrder(ArtworkOrder $order): void
    {
        $amountXof = (int) $order->amount_total_xof;
        if ($amountXof <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $amountXof) {
            $alreadyProcessed = WalletTransaction::where('artwork_order_id', $order->id)
                ->where('type', WalletTransaction::TYPE_ART_SALE_COLLECTED)
                ->exists();
            if ($alreadyProcessed) {
                return;
            }

            // Valeurs figées sur la commande au moment de sa création — jamais recalculées
            // ici (même règle que pour les réservations).
            $commissionXof = max(0, (int) $order->commission_amount_xof);
            $artistNetXof = max(0, (int) $order->artist_net_amount_xof);

            $platformWallet = $this->lockedWallet(Wallet::platform()->id);

            try {
                $this->insertArtworkRow($platformWallet, 'balance_available_xof', WalletTransaction::TYPE_ART_SALE_COLLECTED, $amountXof, $order, "Vente encaissée — {$order->reference}");
            } catch (QueryException $e) {
                if ($this->isDuplicateKeyViolation($e)) {
                    return; // course concurrente (webhook + retour navigateur) : déjà traité
                }
                throw $e;
            }

            $platformWallet->refresh();
            WalletTransaction::create([
                'wallet_id' => $platformWallet->id,
                'type' => WalletTransaction::TYPE_ART_COMMISSION,
                'status' => WalletTransaction::STATUS_COMPLETED,
                'amount_xof' => $commissionXof,
                'balance_before_xof' => $platformWallet->balance_available_xof,
                'balance_after_xof' => $platformWallet->balance_available_xof,
                'artwork_order_id' => $order->id,
                'description' => "Commission plateforme — {$order->reference}",
            ]);

            if ($artistNetXof > 0) {
                $platformWallet->refresh();
                $this->insertArtworkRow($platformWallet, 'balance_available_xof', WalletTransaction::TYPE_ART_TRANSFER_TO_ARTIST, -$artistNetXof, $order, "Transfert vers artiste — {$order->reference}");

                $artistWallet = $this->lockedWallet(Wallet::forProvider($order->provider)->id);
                $this->insertArtworkRow($artistWallet, 'balance_pending_xof', WalletTransaction::TYPE_ART_CREDIT_PENDING, $artistNetXof, $order, "Vente œuvre {$order->reference}");
            }
        });
    }

    /**
     * Crédite le paiement d'une visite de site touristique : commission plateforme
     * encaissée (disponible immédiatement) puis part prestataire créditée directement
     * disponible (contrairement aux réservations hôtelières, pas de délai de rétention
     * à respecter — aucune date de check-out à attendre pour une simple visite).
     * Copie structurelle de creditSaleForArtworkOrder() : mêmes garanties d'idempotence
     * et de verrouillage, adaptées à TouristVisit.
     */
    public function creditSaleForVisit(TouristVisit $visit): void
    {
        $amountXof = (int) $visit->amount_total_xof;
        if ($amountXof <= 0) {
            return;
        }

        DB::transaction(function () use ($visit, $amountXof) {
            $alreadyProcessed = WalletTransaction::where('tourist_visit_id', $visit->id)
                ->where('type', WalletTransaction::TYPE_VISIT_SALE_COLLECTED)
                ->exists();
            if ($alreadyProcessed) {
                return;
            }

            $commissionXof = max(0, (int) $visit->commission_amount_xof);
            $providerNetXof = max(0, $amountXof - $commissionXof);

            $platformWallet = $this->lockedWallet(Wallet::platform()->id);

            try {
                $this->insertVisitRow($platformWallet, 'balance_available_xof', WalletTransaction::TYPE_VISIT_SALE_COLLECTED, $amountXof, $visit, "Visite encaissée — {$visit->reference}");
            } catch (QueryException $e) {
                if ($this->isDuplicateKeyViolation($e)) {
                    return; // course concurrente (webhook + retour navigateur) : déjà traité
                }
                throw $e;
            }

            $platformWallet->refresh();
            WalletTransaction::create([
                'wallet_id' => $platformWallet->id,
                'type' => WalletTransaction::TYPE_VISIT_COMMISSION,
                'status' => WalletTransaction::STATUS_COMPLETED,
                'amount_xof' => $commissionXof,
                'balance_before_xof' => $platformWallet->balance_available_xof,
                'balance_after_xof' => $platformWallet->balance_available_xof,
                'tourist_visit_id' => $visit->id,
                'description' => "Commission plateforme — {$visit->reference}",
            ]);

            if ($providerNetXof > 0 && $visit->provider_id) {
                $platformWallet->refresh();
                $this->insertVisitRow($platformWallet, 'balance_available_xof', WalletTransaction::TYPE_VISIT_TRANSFER_TO_PROVIDER, -$providerNetXof, $visit, "Transfert vers prestataire — {$visit->reference}");

                $providerWallet = $this->lockedWallet(Wallet::forProvider($visit->provider)->id);
                $this->insertVisitRow($providerWallet, 'balance_available_xof', WalletTransaction::TYPE_VISIT_CREDIT_AVAILABLE, $providerNetXof, $visit, "Visite {$visit->reference}");
            }
        });
    }

    /**
     * Crédite le wallet du client suite à une recharge confirmée (CinetPay). Ne touche jamais
     * le wallet plateforme : une recharge ne génère aucun revenu, l'argent reste la propriété
     * du client tant qu'il n'a pas servi à payer une réservation (cf. payReservationFromWallet).
     */
    public function creditTopup(WalletTopup $topup): void
    {
        $amountXof = (int) $topup->amount_xof;
        if ($amountXof <= 0) {
            return;
        }

        DB::transaction(function () use ($topup, $amountXof) {
            $wallet = $this->lockedWallet($topup->wallet_id);
            $balanceBefore = $wallet->balance_available_xof;

            try {
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => WalletTransaction::TYPE_WALLET_TOPUP,
                    'status' => WalletTransaction::STATUS_COMPLETED,
                    'amount_xof' => $amountXof,
                    'balance_before_xof' => $balanceBefore,
                    'balance_after_xof' => $balanceBefore + $amountXof,
                    'wallet_topup_id' => $topup->id,
                    'description' => "Recharge du solde — {$topup->uuid}",
                ]);
            } catch (QueryException $e) {
                if ($this->isDuplicateKeyViolation($e)) {
                    return; // course concurrente (webhook + retour navigateur) : déjà traité
                }
                throw $e;
            }

            $wallet->incrementAvailable($amountXof);
        });
    }

    /**
     * Paie l'acompte d'une réservation directement depuis le solde du client — remplace
     * l'étape CinetPay pour ce même montant (l'acompte uniquement, même périmètre que le
     * paiement CinetPay ; le solde restant du séjour continue d'être payé sur place).
     * Réutilise creditDepositForReservation() inchangée pour la répartition commission/
     * prestataire : seule la source du crédit change (débit wallet plutôt qu'un encaissement
     * CinetPay frais), la logique de répartition déjà revue et testée ne change pas.
     */
    public function payReservationFromWallet(Reservation $reservation, User $client): void
    {
        $depositXof = (int) $reservation->deposit_amount_xof;
        if ($depositXof <= 0) {
            throw new \RuntimeException('Montant d\'acompte invalide pour cette réservation.');
        }

        DB::transaction(function () use ($reservation, $client, $depositXof) {
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID) {
                return; // déjà payée (double clic, nouvel appel) — no-op silencieux
            }

            $wallet = $this->lockedWallet(Wallet::forUser($client)->id);
            if ($wallet->balance_available_xof < $depositXof) {
                throw new \RuntimeException('Solde insuffisant pour payer cet acompte depuis votre wallet.');
            }

            try {
                $this->insertRow($wallet, 'balance_available_xof', WalletTransaction::TYPE_WALLET_PAYMENT, -$depositXof, $reservation, "Paiement acompte — {$reservation->reference}");
            } catch (QueryException $e) {
                if ($this->isDuplicateKeyViolation($e)) {
                    return; // déjà traité par un appel concurrent
                }
                throw $e;
            }

            $reservation->update(['payment_status' => Reservation::PAYMENT_DEPOSIT_PAID]);

            $this->creditDepositForReservation($reservation);
            $this->notifyReservationConfirmed($reservation);
        });
    }

    /**
     * Paie une visite directement depuis le solde du client — jumeau de
     * payReservationFromWallet(), adapté au paiement intégral (pas d'acompte) des visites.
     */
    public function payVisitFromWallet(TouristVisit $visit, User $client): void
    {
        $amountXof = (int) $visit->amount_total_xof;
        if ($amountXof <= 0) {
            throw new \RuntimeException('Montant invalide pour cette visite.');
        }

        DB::transaction(function () use ($visit, $client, $amountXof) {
            $visit = TouristVisit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
            if ($visit->status === TouristVisit::STATUS_PAID) {
                return; // déjà payée (double clic, nouvel appel) — no-op silencieux
            }

            $wallet = $this->lockedWallet(Wallet::forUser($client)->id);
            if ($wallet->balance_available_xof < $amountXof) {
                throw new \RuntimeException('Solde insuffisant pour payer cette visite depuis votre wallet.');
            }

            try {
                $this->insertVisitRow($wallet, 'balance_available_xof', WalletTransaction::TYPE_WALLET_PAYMENT, -$amountXof, $visit, "Paiement visite — {$visit->reference}");
            } catch (QueryException $e) {
                if ($this->isDuplicateKeyViolation($e)) {
                    return; // déjà traité par un appel concurrent
                }
                throw $e;
            }

            $visit->update(['status' => TouristVisit::STATUS_PAID, 'gateway' => 'wallet', 'paid_at' => now()]);

            $this->creditSaleForVisit($visit);
        });
    }

    /**
     * Bascule pending -> available pour les crédits dont le délai configuré (après la date
     * d'arrivée) est dépassé. Appelée par la commande planifiée wallet:release-pending.
     */
    public function releasePendingCredits(): void
    {
        $delayDays = (int) (PaymentSetting::where('key', 'wallet_payout_release_delay_days')->value('value') ?? 0);
        $cutoff = now()->subDays($delayDays);

        $alreadyReleased = WalletTransaction::where('type', WalletTransaction::TYPE_PROVIDER_CREDIT_AVAILABLE)
            ->whereNotNull('reservation_id')
            ->pluck('reservation_id');

        WalletTransaction::where('type', WalletTransaction::TYPE_PROVIDER_CREDIT_PENDING)
            ->whereNotNull('reservation_id')
            ->whereNotIn('reservation_id', $alreadyReleased)
            ->with('reservation')
            ->get()
            ->filter(fn (WalletTransaction $row) => $row->reservation?->check_in?->lte($cutoff))
            ->each(fn (WalletTransaction $row) => $this->releaseSingleCredit($row));
    }

    private function releaseSingleCredit(WalletTransaction $pendingRow): void
    {
        DB::transaction(function () use ($pendingRow) {
            $alreadyReleased = WalletTransaction::where('reservation_id', $pendingRow->reservation_id)
                ->where('type', WalletTransaction::TYPE_PROVIDER_CREDIT_AVAILABLE)
                ->exists();
            if ($alreadyReleased) {
                return;
            }

            $wallet = $this->lockedWallet($pendingRow->wallet_id);
            $amount = $pendingRow->amount_xof;
            if ($wallet->balance_pending_xof < $amount) {
                return; // solde en attente déjà consommé entre-temps (ex. remboursement) — rien à libérer
            }

            $balanceBefore = $wallet->balance_available_xof;
            $wallet->decrementPending($amount);
            $wallet->incrementAvailable($amount);

            try {
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => WalletTransaction::TYPE_PROVIDER_CREDIT_AVAILABLE,
                    'status' => WalletTransaction::STATUS_COMPLETED,
                    'amount_xof' => $amount,
                    'balance_before_xof' => $balanceBefore,
                    'balance_after_xof' => $wallet->balance_available_xof,
                    'reservation_id' => $pendingRow->reservation_id,
                    'description' => "Solde disponible — {$pendingRow->reservation->reference}",
                ]);
            } catch (QueryException $e) {
                if (! $this->isDuplicateKeyViolation($e)) {
                    throw $e;
                }
            }
        });
    }

    /**
     * Bascule pending -> available pour une commande d'œuvre au moment où elle passe
     * "livrée" — déclencheur événementiel (et non temporel, contrairement aux réservations
     * dont la libération est basée sur la date d'arrivée) : appelé de façon synchrone par
     * Provider\ArtworkOrderController::updateStatus() dès que l'artiste confirme la livraison.
     */
    public function releaseArtworkOrderCredit(ArtworkOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $alreadyReleased = WalletTransaction::where('artwork_order_id', $order->id)
                ->where('type', WalletTransaction::TYPE_ART_CREDIT_AVAILABLE)
                ->exists();
            if ($alreadyReleased) {
                return;
            }

            $pendingRow = WalletTransaction::where('artwork_order_id', $order->id)
                ->where('type', WalletTransaction::TYPE_ART_CREDIT_PENDING)
                ->first();
            if (! $pendingRow) {
                return; // aucune part artiste à libérer (ex. montant net nul)
            }

            $wallet = $this->lockedWallet($pendingRow->wallet_id);
            $amount = $pendingRow->amount_xof;
            if ($wallet->balance_pending_xof < $amount) {
                return; // solde en attente déjà consommé entre-temps (ex. remboursement) — rien à libérer
            }

            $balanceBefore = $wallet->balance_available_xof;
            $wallet->decrementPending($amount);
            $wallet->incrementAvailable($amount);

            try {
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => WalletTransaction::TYPE_ART_CREDIT_AVAILABLE,
                    'status' => WalletTransaction::STATUS_COMPLETED,
                    'amount_xof' => $amount,
                    'balance_before_xof' => $balanceBefore,
                    'balance_after_xof' => $wallet->balance_available_xof,
                    'artwork_order_id' => $order->id,
                    'description' => "Solde disponible — {$order->reference}",
                ]);
            } catch (QueryException $e) {
                if (! $this->isDuplicateKeyViolation($e)) {
                    throw $e;
                }
            }
        });
    }

    /**
     * Filet de sécurité (commande planifiée art:release-pending) : libère automatiquement
     * les crédits artiste restés "expédiés" sans confirmation de livraison au-delà du délai
     * configuré — protège l'artiste si l'acheteur ne confirme jamais / si l'artiste oublie de
     * mettre à jour le statut, symétrique au risque inverse (libération prématurée) qui reste
     * couvert par le remboursement manuel admin (cf. recordManualRefundForArtworkOrder).
     */
    public function releaseStaleArtworkCredits(): void
    {
        $delayDays = (int) (PaymentSetting::where('key', 'art_auto_release_delay_days')->value('value') ?? 14);
        $cutoff = now()->subDays($delayDays);

        $alreadyReleased = WalletTransaction::where('type', WalletTransaction::TYPE_ART_CREDIT_AVAILABLE)
            ->whereNotNull('artwork_order_id')
            ->pluck('artwork_order_id');

        WalletTransaction::where('type', WalletTransaction::TYPE_ART_CREDIT_PENDING)
            ->whereNotNull('artwork_order_id')
            ->whereNotIn('artwork_order_id', $alreadyReleased)
            ->with('artworkOrder')
            ->get()
            ->filter(fn (WalletTransaction $row) => $row->artworkOrder
                && $row->artworkOrder->status === ArtworkOrder::STATUS_SHIPPED
                && $row->artworkOrder->shipped_at?->lte($cutoff))
            ->each(fn (WalletTransaction $row) => $this->releaseArtworkOrderCredit($row->artworkOrder));
    }

    public function requestPayout(Provider $provider, int $amountXof, string $method, string $destination, ?string $ipAddress = null, ?string $userAgent = null): PayoutRequest
    {
        return DB::transaction(function () use ($provider, $amountXof, $method, $destination, $ipAddress, $userAgent) {
            $wallet = $this->lockedWallet(Wallet::forProvider($provider)->id);
            $outstandingDebt = (int) ProviderDebt::outstanding()->where('provider_id', $provider->id)->sum('amount_xof');
            $netAvailable = max(0, $wallet->balance_available_xof - $outstandingDebt);

            if ($amountXof <= 0 || $amountXof > $netAvailable) {
                throw new \RuntimeException('Montant de retrait invalide ou supérieur au solde disponible (créances en cours déduites).');
            }

            $this->assertNoOpenPayout($provider->id, null);

            $payout = PayoutRequest::create([
                'provider_id' => $provider->id,
                'wallet_id' => $wallet->id,
                'amount_xof' => $amountXof,
                'method' => $method,
                'payout_destination' => $destination,
                'status' => PayoutRequest::STATUS_PENDING,
                'ip_address' => $ipAddress,
                'user_agent' => mb_substr((string) $userAgent, 0, 255),
            ]);

            $this->applyRiskDecision($payout);

            return $payout;
        });
    }

    /**
     * Retrait client — pas de notion de créance ici (contrairement au prestataire) : un
     * remboursement crédite toujours directement le wallet du client (cf. recordManualRefund),
     * il ne peut jamais se retrouver à devoir de l'argent à la plateforme.
     */
    public function requestClientPayout(User $user, int $amountXof, string $method, string $destination, ?string $ipAddress = null, ?string $userAgent = null): PayoutRequest
    {
        return DB::transaction(function () use ($user, $amountXof, $method, $destination, $ipAddress, $userAgent) {
            $wallet = $this->lockedWallet(Wallet::forUser($user)->id);

            if ($amountXof <= 0 || $amountXof > $wallet->balance_available_xof) {
                throw new \RuntimeException('Montant de retrait invalide ou supérieur au solde disponible.');
            }

            $this->assertNoOpenPayout(null, $user->id);

            $payout = PayoutRequest::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount_xof' => $amountXof,
                'method' => $method,
                'payout_destination' => $destination,
                'status' => PayoutRequest::STATUS_PENDING,
                'ip_address' => $ipAddress,
                'user_agent' => mb_substr((string) $userAgent, 0, 255),
            ]);

            $this->applyRiskDecision($payout);

            return $payout;
        });
    }

    /**
     * Signal 7 du moteur de risque, appliqué en règle dure plutôt qu'en simple pondération :
     * un seul retrait non soldé à la fois par détenteur. 'approved' est inclus : le solde
     * n'est décrémenté qu'à markPayoutPaid() (jamais à l'approbation), donc sans cette
     * inclusion un détenteur pourrait empiler plusieurs retraits auto-approuvés contre le
     * même solde non encore débité et les faire tous payer, au-delà de ce qu'il possède
     * réellement — trouvé en écrivant le test de couverture de cette règle.
     */
    private function assertNoOpenPayout(?int $providerId, ?int $userId): void
    {
        $openStatuses = [
            PayoutRequest::STATUS_PENDING,
            PayoutRequest::STATUS_PENDING_VERIFICATION,
            PayoutRequest::STATUS_SUSPENDED,
            PayoutRequest::STATUS_APPROVED,
        ];

        $query = PayoutRequest::query()->whereIn('status', $openStatuses);
        $providerId ? $query->where('provider_id', $providerId) : $query->where('user_id', $userId);

        if ($query->exists()) {
            throw new \RuntimeException('Vous avez déjà une demande de retrait en cours de traitement — merci d\'attendre son issue avant d\'en soumettre une nouvelle.');
        }
    }

    /**
     * Fait tourner le moteur de risque sur une demande fraîchement créée (status pending) et
     * bascule immédiatement son statut selon le barème — c'est ici que "l'automatisation" du
     * cahier des charges se joue : approbation instantanée, vérification complémentaire, ou
     * suspension pour examen humain. Toujours appelée à l'intérieur de la transaction qui a
     * créé la ligne.
     */
    private function applyRiskDecision(PayoutRequest $payout): void
    {
        $assessment = $this->riskEngine->assess($payout);
        $hasBlockingFlag = collect($assessment['flags'])->contains(fn ($f) => $f['code'] === 'invalid_destination_format');

        $payout->update([
            'risk_score' => $assessment['score'],
            'risk_level' => $assessment['level'],
            'risk_flags' => $assessment['flags'],
        ]);

        PayoutAuditLogger::log($payout, 'risk_assessment_run', PayoutAuditLog::ACTOR_SYSTEM, null, $assessment);

        $suspendThreshold = (int) config('payout_risk.suspend_at_or_above', 70);
        $autoApproveThreshold = (int) config('payout_risk.auto_approve_below', 25);

        if ($hasBlockingFlag || $assessment['score'] >= $suspendThreshold) {
            $payout->update(['decision' => PayoutRequest::DECISION_SUSPENDED, 'status' => PayoutRequest::STATUS_SUSPENDED]);
            PayoutAuditLogger::log($payout, 'suspended', PayoutAuditLog::ACTOR_SYSTEM);

            foreach (User::where('role', 'admin')->where('is_active', true)->get() as $admin) {
                $admin->notify(new PayoutSuspendedAdminNotification($payout));
            }
            PayoutAuditLogger::log($payout, 'admin_notified', PayoutAuditLog::ACTOR_SYSTEM);

            return;
        }

        if ($assessment['score'] < $autoApproveThreshold) {
            $payout->update(['decision' => PayoutRequest::DECISION_AUTO_APPROVED]);
            PayoutAuditLogger::log($payout, 'auto_approved', PayoutAuditLog::ACTOR_SYSTEM);
            $this->approvePayout($payout, null, null, true);

            return;
        }

        $payout->update(['decision' => PayoutRequest::DECISION_STEP_UP_REQUIRED, 'status' => PayoutRequest::STATUS_PENDING_VERIFICATION]);
        PayoutAuditLogger::log($payout, 'step_up_sent', PayoutAuditLog::ACTOR_SYSTEM);
        $this->otpService->send($payout);
    }

    /**
     * Valide le code de vérification complémentaire et fait passer la demande à 'approved'
     * en cas de succès. Retourne un message d'erreur (string) en cas d'échec, true sinon.
     */
    public function verifyPayoutOtp(PayoutRequest $payout, string $code): bool|string
    {
        if ($payout->status !== PayoutRequest::STATUS_PENDING_VERIFICATION) {
            return 'Cette demande ne nécessite pas (ou plus) de vérification.';
        }

        $result = $this->otpService->verify($payout, $code);

        if ($result !== true) {
            PayoutAuditLogger::log($payout, 'step_up_failed', PayoutAuditLog::ACTOR_USER, $payout->holderUser(), ['reason' => $result]);

            return $result;
        }

        PayoutAuditLogger::log($payout, 'step_up_verified', PayoutAuditLog::ACTOR_USER, $payout->holderUser());
        $this->approvePayout($payout, null, null, true);

        return true;
    }

    /**
     * $admin est nullable et $bySystem permet de distinguer une approbation automatique
     * (moteur de risque / OTP validé) d'une approbation humaine — dans les deux cas
     * 'approved' est le statut final, mais reviewed_by ne reflète que la décision d'un admin.
     * Autorisée aussi depuis 'suspended' : un admin peut lever une suspension manuellement,
     * jamais l'inverse (le moteur ne repasse jamais par une demande déjà suspendue).
     */
    public function approvePayout(PayoutRequest $request, ?User $admin = null, ?string $note = null, bool $bySystem = false): void
    {
        if (! in_array($request->status, [PayoutRequest::STATUS_PENDING, PayoutRequest::STATUS_PENDING_VERIFICATION, PayoutRequest::STATUS_SUSPENDED], true)) {
            throw new \RuntimeException('Seule une demande en attente peut être approuvée.');
        }

        $wasSuspended = $request->status === PayoutRequest::STATUS_SUSPENDED;
        if ($wasSuspended && ! $admin) {
            throw new \RuntimeException('Une demande suspendue ne peut être approuvée que par un administrateur.');
        }

        $request->update([
            'status' => PayoutRequest::STATUS_APPROVED,
            'reviewed_by' => $admin?->id,
            'reviewed_at' => $admin ? now() : $request->reviewed_at,
            'admin_note' => $note ?? $request->admin_note,
        ]);

        if (! $bySystem) {
            PayoutAuditLogger::log($request, 'admin_approved', PayoutAuditLog::ACTOR_ADMIN, $admin, ['note' => $note]);
        }
    }

    public function rejectPayout(PayoutRequest $request, User $admin, ?string $note = null): void
    {
        if (! in_array($request->status, [PayoutRequest::STATUS_PENDING, PayoutRequest::STATUS_PENDING_VERIFICATION, PayoutRequest::STATUS_SUSPENDED, PayoutRequest::STATUS_APPROVED], true)) {
            throw new \RuntimeException('Cette demande ne peut plus être refusée.');
        }
        $request->update([
            'status' => PayoutRequest::STATUS_REJECTED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_note' => $note,
        ]);

        PayoutAuditLogger::log($request, 'admin_rejected', PayoutAuditLog::ACTOR_ADMIN, $admin, ['note' => $note]);
    }

    public function markPayoutPaid(PayoutRequest $request, User $admin, string $paymentReference): void
    {
        DB::transaction(function () use ($request, $admin, $paymentReference) {
            $request = PayoutRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== PayoutRequest::STATUS_APPROVED) {
                throw new \RuntimeException('Seule une demande approuvée peut être marquée payée.');
            }

            $wallet = $this->lockedWallet($request->wallet_id);
            if ($wallet->balance_available_xof < $request->amount_xof) {
                throw new \RuntimeException('Solde disponible insuffisant au moment du versement — à vérifier manuellement (remboursement concurrent probable).');
            }

            $balanceBefore = $wallet->balance_available_xof;
            $wallet->decrementAvailable($request->amount_xof);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransaction::TYPE_PAYOUT,
                'status' => WalletTransaction::STATUS_COMPLETED,
                'amount_xof' => -$request->amount_xof,
                'balance_before_xof' => $balanceBefore,
                'balance_after_xof' => $wallet->balance_available_xof,
                'payout_request_id' => $request->id,
                'description' => "Retrait {$request->uuid}",
            ]);

            $request->update([
                'status' => PayoutRequest::STATUS_PAID,
                'paid_at' => now(),
                'payment_reference' => $paymentReference,
                'reviewed_by' => $request->reviewed_by ?? $admin->id,
            ]);

            PayoutAuditLogger::log($request, 'paid', PayoutAuditLog::ACTOR_ADMIN, $admin, ['payment_reference' => $paymentReference]);
        });
    }

    /**
     * Action admin manuelle sur une réservation annulée : réconcilie par le solde courant
     * du prestataire plutôt que par traçabilité exacte d'un retrait individuel (hypothèse MVP
     * documentée dans le plan — aucun mécanisme de remboursement automatique CinetPay n'existe).
     * Si le solde courant ne couvre pas la part à récupérer (déjà retirée), le manque devient
     * une créance (ProviderDebt) explicite plutôt qu'un solde wallet négatif.
     */
    public function recordManualRefund(Reservation $reservation, int $amountXof, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($reservation, $amountXof, $admin, $note) {
            // Verrou d'idempotence sur le statut de la réservation plutôt que sur l'existence
            // d'une ligne de ledger : quand tout le crédit prestataire a déjà été retiré, aucune
            // ligne refund_debit n'est créée (rien à débiter, seulement une créance), donc se
            // fier uniquement à l'existence de cette ligne laisserait un second appel dupliquer
            // la créance.
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($reservation->payment_status === Reservation::PAYMENT_REFUNDED) {
                return;
            }

            $depositXof = (int) $reservation->deposit_amount_xof;
            if ($depositXof <= 0 || $amountXof <= 0) {
                return;
            }

            $commissionXof = max(0, (int) $reservation->commission_amount_xof);
            $providerNetXof = max(0, $depositXof - $commissionXof);
            $ratio = min(1.0, $amountXof / $depositXof);
            $providerReversalXof = (int) round($providerNetXof * $ratio);

            if ($providerReversalXof > 0 && $reservation->provider_id) {
                $wallet = $this->lockedWallet(Wallet::forProvider($reservation->provider)->id);

                $fromAvailable = min($wallet->balance_available_xof, $providerReversalXof);
                $fromPending = min($wallet->balance_pending_xof, $providerReversalXof - $fromAvailable);
                $recovered = $fromAvailable + $fromPending;
                $shortfall = $providerReversalXof - $recovered;

                if ($recovered > 0) {
                    $balanceBefore = $wallet->balance_available_xof;
                    $balanceAfter = $balanceBefore - $fromAvailable; // le retrait "pending" est documenté en métadonnées, non reflété par ce champ

                    try {
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => WalletTransaction::TYPE_REFUND_DEBIT,
                            'status' => WalletTransaction::STATUS_COMPLETED,
                            'amount_xof' => -$recovered,
                            'balance_before_xof' => $balanceBefore,
                            'balance_after_xof' => $balanceAfter,
                            'reservation_id' => $reservation->id,
                            'description' => "Remboursement client — {$reservation->reference}",
                            'metadata' => [
                                'from_available_xof' => $fromAvailable,
                                'from_pending_xof' => $fromPending,
                                'shortfall_xof' => $shortfall,
                                'refunded_amount_xof' => $amountXof,
                                'recorded_by_user_id' => $admin->id,
                                'note' => $note,
                            ],
                        ]);
                    } catch (QueryException $e) {
                        if (! $this->isDuplicateKeyViolation($e)) {
                            throw $e;
                        }

                        return; // déjà traité par un appel concurrent
                    }

                    if ($fromAvailable > 0) {
                        $wallet->decrementAvailable($fromAvailable);
                    }
                    if ($fromPending > 0) {
                        $wallet->decrementPending($fromPending);
                    }
                }

                if ($shortfall > 0) {
                    ProviderDebt::create([
                        'provider_id' => $reservation->provider_id,
                        'wallet_id' => $wallet->id,
                        'amount_xof' => $shortfall,
                        'reason' => "Remboursement client — solde déjà retiré (réservation {$reservation->reference}, enregistré par {$admin->full_name}".($note ? " : {$note}" : '').')',
                        'reservation_id' => $reservation->id,
                        'status' => ProviderDebt::STATUS_OUTSTANDING,
                    ]);
                }
            }

            // Si l'acompte avait été payé depuis le wallet client, le remboursement crédite
            // directement ce wallet — aucun appel API de remboursement CinetPay nécessaire
            // (cette API n'existe pas dans l'intégration actuelle) ; pour un paiement CinetPay
            // classique, le remboursement au client reste manuel hors système, inchangé.
            $wasPaidByWallet = WalletTransaction::where('reservation_id', $reservation->id)
                ->where('type', WalletTransaction::TYPE_WALLET_PAYMENT)
                ->exists();

            if ($wasPaidByWallet && $reservation->user_id) {
                $clientWallet = $this->lockedWallet(Wallet::forUser($reservation->user)->id);

                try {
                    $this->insertRow($clientWallet, 'balance_available_xof', WalletTransaction::TYPE_REFUND_CREDIT, $amountXof, $reservation, "Remboursement crédité sur votre solde — {$reservation->reference}");
                } catch (QueryException $e) {
                    if (! $this->isDuplicateKeyViolation($e)) {
                        throw $e;
                    }
                }
            }

            $reservation->update(['payment_status' => Reservation::PAYMENT_REFUNDED]);
        });
    }

    /**
     * Action admin manuelle sur une commande d'œuvre (litige, retour) — miroir simplifié de
     * recordManualRefund() : pas de notion de créance formelle (ProviderDebt est structurée
     * autour de reservation_id) pour cette Phase 3 MVP, un manque de recouvrement est
     * simplement documenté en métadonnées sur la ligne de ledger plutôt que créer un nouveau
     * mécanisme de créance dédié aux œuvres.
     */
    public function recordManualRefundForArtworkOrder(ArtworkOrder $order, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($order, $admin, $note) {
            $order = ArtworkOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status === ArtworkOrder::STATUS_REFUNDED) {
                return;
            }

            $artistNetXof = max(0, (int) $order->artist_net_amount_xof);

            if ($artistNetXof > 0) {
                $wallet = $this->lockedWallet(Wallet::forProvider($order->provider)->id);

                $fromAvailable = min($wallet->balance_available_xof, $artistNetXof);
                $fromPending = min($wallet->balance_pending_xof, $artistNetXof - $fromAvailable);
                $recovered = $fromAvailable + $fromPending;
                $shortfall = $artistNetXof - $recovered;

                if ($recovered > 0) {
                    $balanceBefore = $wallet->balance_available_xof;
                    $balanceAfter = $balanceBefore - $fromAvailable; // la part "pending" est documentée en métadonnées, non reflétée par ce champ

                    try {
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => WalletTransaction::TYPE_REFUND_DEBIT,
                            'status' => WalletTransaction::STATUS_COMPLETED,
                            'amount_xof' => -$recovered,
                            'balance_before_xof' => $balanceBefore,
                            'balance_after_xof' => $balanceAfter,
                            'artwork_order_id' => $order->id,
                            'description' => "Remboursement acheteur — {$order->reference}",
                            'metadata' => [
                                'from_available_xof' => $fromAvailable,
                                'from_pending_xof' => $fromPending,
                                'shortfall_xof' => $shortfall,
                                'recorded_by_user_id' => $admin->id,
                                'note' => $note,
                            ],
                        ]);
                    } catch (QueryException $e) {
                        if (! $this->isDuplicateKeyViolation($e)) {
                            throw $e;
                        }

                        return; // déjà traité par un appel concurrent
                    }

                    if ($fromAvailable > 0) {
                        $wallet->decrementAvailable($fromAvailable);
                    }
                    if ($fromPending > 0) {
                        $wallet->decrementPending($fromPending);
                    }
                }
            }

            $order->update(['status' => ArtworkOrder::STATUS_REFUNDED]);
        });
    }

    /**
     * Action admin manuelle sur une visite payée (annulation, litige) — jumeau simplifié
     * de recordManualRefundForArtworkOrder() : pas d'acompte à distinguer (paiement
     * intégral), la part prestataire est toujours créditée disponible immédiatement
     * (jamais "pending"), donc pas de répartition available/pending à reverser non plus.
     */
    public function recordManualRefundForVisit(TouristVisit $visit, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($visit, $admin, $note) {
            $visit = TouristVisit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
            if ($visit->status === TouristVisit::STATUS_REFUNDED) {
                return;
            }

            $providerNetXof = max(0, (int) $visit->provider_net_amount_xof);

            if ($providerNetXof > 0 && $visit->provider_id) {
                $wallet = $this->lockedWallet(Wallet::forProvider($visit->provider)->id);
                $recovered = min($wallet->balance_available_xof, $providerNetXof);
                $shortfall = $providerNetXof - $recovered;

                if ($recovered > 0) {
                    $balanceBefore = $wallet->balance_available_xof;

                    try {
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => WalletTransaction::TYPE_REFUND_DEBIT,
                            'status' => WalletTransaction::STATUS_COMPLETED,
                            'amount_xof' => -$recovered,
                            'balance_before_xof' => $balanceBefore,
                            'balance_after_xof' => $balanceBefore - $recovered,
                            'tourist_visit_id' => $visit->id,
                            'description' => "Remboursement client — {$visit->reference}",
                            'metadata' => [
                                'shortfall_xof' => $shortfall,
                                'recorded_by_user_id' => $admin->id,
                                'note' => $note,
                            ],
                        ]);
                    } catch (QueryException $e) {
                        if (! $this->isDuplicateKeyViolation($e)) {
                            throw $e;
                        }

                        return; // déjà traité par un appel concurrent
                    }

                    $wallet->decrementAvailable($recovered);
                }

                if ($shortfall > 0) {
                    ProviderDebt::create([
                        'provider_id' => $visit->provider_id,
                        'wallet_id' => $wallet->id,
                        'amount_xof' => $shortfall,
                        'reason' => "Remboursement client — solde déjà retiré (visite {$visit->reference}, enregistré par {$admin->full_name}".($note ? " : {$note}" : '').')',
                        'reservation_id' => null,
                        'status' => ProviderDebt::STATUS_OUTSTANDING,
                    ]);
                }
            }

            $wasPaidByWallet = $visit->gateway === 'wallet';
            if ($wasPaidByWallet && $visit->user_id) {
                $clientWallet = $this->lockedWallet(Wallet::forUser($visit->user)->id);

                try {
                    $this->insertVisitRow($clientWallet, 'balance_available_xof', WalletTransaction::TYPE_REFUND_CREDIT, (int) $visit->amount_total_xof, $visit, "Remboursement crédité sur votre solde — {$visit->reference}");
                } catch (QueryException $e) {
                    if (! $this->isDuplicateKeyViolation($e)) {
                        throw $e;
                    }
                }
            }

            $visit->update(['status' => TouristVisit::STATUS_REFUNDED]);
        });
    }

    private function lockedWallet(int $walletId): Wallet
    {
        return Wallet::query()->whereKey($walletId)->lockForUpdate()->firstOrFail();
    }

    /**
     * L'insertion du ledger doit précéder la mutation du solde, pas l'inverse : c'est
     * elle qui porte la contrainte unique (reservation_id, type) servant de verrou
     * d'idempotence. Si le solde était modifié en premier, un doublon intercepté à
     * l'insertion laisserait la mutation déjà appliquée dans la transaction en cours.
     */
    private function insertRow(Wallet $wallet, string $balanceField, string $type, int $signedAmountXof, Reservation $reservation, string $description): void
    {
        $balanceBefore = $wallet->{$balanceField};
        $balanceAfter = $balanceBefore + $signedAmountXof;

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $signedAmountXof,
            'balance_before_xof' => $balanceBefore,
            'balance_after_xof' => $balanceAfter,
            'reservation_id' => $reservation->id,
            'description' => $description,
        ]);

        if ($balanceField === 'balance_pending_xof') {
            $signedAmountXof >= 0 ? $wallet->incrementPending($signedAmountXof) : $wallet->decrementPending(-$signedAmountXof);
        } else {
            $signedAmountXof >= 0 ? $wallet->incrementAvailable($signedAmountXof) : $wallet->decrementAvailable(-$signedAmountXof);
        }
    }

    /**
     * Jumeau de insertRow() pour ArtworkOrder plutôt qu'une généralisation du typage de
     * insertRow() — évite de toucher une méthode déjà utilisée par le chemin réservation
     * (zéro risque de régression sur un code qui fonctionne), au prix d'une petite
     * duplication assumée.
     */
    private function insertArtworkRow(Wallet $wallet, string $balanceField, string $type, int $signedAmountXof, ArtworkOrder $order, string $description): void
    {
        $balanceBefore = $wallet->{$balanceField};
        $balanceAfter = $balanceBefore + $signedAmountXof;

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $signedAmountXof,
            'balance_before_xof' => $balanceBefore,
            'balance_after_xof' => $balanceAfter,
            'artwork_order_id' => $order->id,
            'description' => $description,
        ]);

        if ($balanceField === 'balance_pending_xof') {
            $signedAmountXof >= 0 ? $wallet->incrementPending($signedAmountXof) : $wallet->decrementPending(-$signedAmountXof);
        } else {
            $signedAmountXof >= 0 ? $wallet->incrementAvailable($signedAmountXof) : $wallet->decrementAvailable(-$signedAmountXof);
        }
    }

    /**
     * Jumeau de insertRow()/insertArtworkRow() pour TouristVisit — même raisonnement :
     * évite de toucher une méthode déjà utilisée par les chemins réservation/œuvre
     * (zéro risque de régression sur un code qui fonctionne), au prix d'une petite
     * duplication assumée.
     */
    private function insertVisitRow(Wallet $wallet, string $balanceField, string $type, int $signedAmountXof, TouristVisit $visit, string $description): void
    {
        $balanceBefore = $wallet->{$balanceField};
        $balanceAfter = $balanceBefore + $signedAmountXof;

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $signedAmountXof,
            'balance_before_xof' => $balanceBefore,
            'balance_after_xof' => $balanceAfter,
            'tourist_visit_id' => $visit->id,
            'description' => $description,
        ]);

        if ($balanceField === 'balance_pending_xof') {
            $signedAmountXof >= 0 ? $wallet->incrementPending($signedAmountXof) : $wallet->decrementPending(-$signedAmountXof);
        } else {
            $signedAmountXof >= 0 ? $wallet->incrementAvailable($signedAmountXof) : $wallet->decrementAvailable(-$signedAmountXof);
        }
    }

    private function isDuplicateKeyViolation(QueryException $e): bool
    {
        return (string) $e->getCode() === '23000';
    }
}

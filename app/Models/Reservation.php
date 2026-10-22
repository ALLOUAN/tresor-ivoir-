<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\URL;

class Reservation extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_NEW => 'Nouvelle',
        self::STATUS_CONFIRMED => 'Confirmée',
        self::STATUS_CANCELLED => 'Annulée',
    ];

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_DEPOSIT_PAID = 'deposit_paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_REFUNDED = 'refunded';

    /** @var array<string, string> */
    public const PAYMENT_STATUS_LABELS = [
        self::PAYMENT_UNPAID => 'Sans paiement',
        self::PAYMENT_PENDING => 'Paiement en attente',
        self::PAYMENT_DEPOSIT_PAID => 'Acompte payé',
        self::PAYMENT_FAILED => 'Paiement échoué',
        self::PAYMENT_REFUNDED => 'Remboursée',
    ];

    public const COMPUTED_STATUS_UPCOMING = 'upcoming';

    public const COMPUTED_STATUS_ONGOING = 'ongoing';

    public const COMPUTED_STATUS_COMPLETED = 'completed';

    public const COMPUTED_STATUS_CANCELLED = 'cancelled';

    /** @var array<string, string> */
    public const COMPUTED_STATUS_LABELS = [
        self::COMPUTED_STATUS_UPCOMING => 'À venir',
        self::COMPUTED_STATUS_ONGOING => 'En cours',
        self::COMPUTED_STATUS_COMPLETED => 'Terminée',
        self::COMPUTED_STATUS_CANCELLED => 'Annulée',
    ];

    protected $fillable = [
        'user_id',
        'accommodation_id',
        'accommodation_name',
        'provider_id',
        'room_name',
        'room_id',
        'room_price_xof',
        'check_in',
        'check_out',
        'nights',
        'rooms_count',
        'guests_count',
        'total_xof',
        'full_name',
        'email',
        'phone',
        'message',
        'status',
        'payment_status',
        'payment_method',
        'deposit_amount_xof',
        'commission_rate_percent',
        'commission_amount_xof',
        'cancelled_by',
        'cancelled_at',
        'refund_due_xof',
        'promo_code',
        'promo_discount_xof',
    ];

    /** Délai d'annulation gratuite appliqué quand ni l'hébergement ni l'admin n'en fixent un. */
    public const DEFAULT_FREE_CANCELLATION_DAYS = 3;

    public const CANCELLED_BY_CLIENT = 'client';

    public const CANCELLED_BY_PROVIDER = 'provider';

    public const CANCELLED_BY_ADMIN = 'admin';

    public const CANCELLED_BY_SYSTEM = 'system';

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'cancelled_at' => 'datetime',
            'refund_due_xof' => 'integer',
        ];
    }

    /**
     * Jours avant l'arrivée jusqu'auxquels le client récupère tout son acompte en annulant.
     * Priorité : réglage de l'hébergement, puis réglage plateforme (PaymentSetting), puis défaut.
     */
    public function freeCancellationDays(): int
    {
        $days = $this->accommodation?->free_cancellation_days;
        if ($days === null) {
            $setting = PaymentSetting::where('key', 'free_cancellation_days')->value('value');
            $days = $setting !== null && $setting !== '' ? (int) $setting : self::DEFAULT_FREE_CANCELLATION_DAYS;
        }

        return max(0, (int) $days);
    }

    /** Dernier jour (inclus) où l'annulation par le client est encore remboursée en totalité. */
    public function freeCancellationDeadline(): \Carbon\CarbonInterface
    {
        return $this->check_in->copy()->subDays($this->freeCancellationDays())->startOfDay();
    }

    /** Le client peut-il encore annuler lui-même (séjour non commencé, pas de paiement en cours) ? */
    public function clientCanCancel(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_CONFIRMED], true)
            && $this->payment_status !== self::PAYMENT_PENDING
            && $this->payment_status !== self::PAYMENT_REFUNDED
            && now()->startOfDay()->lt($this->check_in);
    }

    /** Montant de l'acompte rendu au client s'il annule aujourd'hui (0 = hors délai, non remboursable). */
    public function clientRefundIfCancelledNow(): int
    {
        if ($this->payment_status !== self::PAYMENT_DEPOSIT_PAID) {
            return 0;
        }

        return now()->startOfDay()->lte($this->freeCancellationDeadline()) ? (int) $this->deposit_amount_xof : 0;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ReservationPayment::class);
    }

    public function guestRegistration(): HasOne
    {
        return $this->hasOne(GuestRegistration::class);
    }

    /** Référence lisible affichée au client (ex: RES-000042). */
    public function getReferenceAttribute(): string
    {
        return 'RES-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Statut affiché côté client, calculé à partir des dates de séjour — même principe
     * que le badge "à venir / en cours / terminé" déjà utilisé sur events/show.blade.php.
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return self::COMPUTED_STATUS_CANCELLED;
        }

        $today = now()->startOfDay();

        if ($today->lt($this->check_in)) {
            return self::COMPUTED_STATUS_UPCOMING;
        }

        if ($today->lte($this->check_out)) {
            return self::COMPUTED_STATUS_ONGOING;
        }

        return self::COMPUTED_STATUS_COMPLETED;
    }

    public function labelForComputedStatus(): string
    {
        return __(self::COMPUTED_STATUS_LABELS[$this->computed_status] ?? $this->computed_status);
    }

    /**
     * Règle de propriété unifiée : par user_id si renseigné, sinon (réservations
     * invité historiques, avant l'ajout de ce champ) par correspondance d'email.
     */
    public function isOwnedBy(User $user): bool
    {
        if ($this->user_id !== null) {
            return $this->user_id === $user->id;
        }

        return $this->email !== null && strcasecmp($this->email, $user->email) === 0;
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere(function (Builder $q2) use ($user) {
                    $q2->whereNull('user_id')->where('email', $user->email);
                });
        });
    }

    /**
     * URLs signées (accès sans compte visiteur — la signature est la seule preuve
     * de propriété de la réservation, cf. correctif IDOR appliqué précédemment).
     */
    public function confirmationUrl(int $days = 30): string
    {
        return URL::temporarySignedRoute('reservations.payment.confirmation', now()->addDays($days), ['reservation' => $this->id]);
    }

    public function receiptUrl(int $days = 30): string
    {
        return URL::temporarySignedRoute('reservations.receipt', now()->addDays($days), ['reservation' => $this->id]);
    }

    public function receiptPdfUrl(int $days = 30): string
    {
        return URL::temporarySignedRoute('reservations.receipt.pdf', now()->addDays($days), ['reservation' => $this->id]);
    }

    public function guestRegistrationUrl(int $days = 60): string
    {
        return URL::temporarySignedRoute('reservations.guest-registration', now()->addDays($days), ['reservation' => $this->id]);
    }

    public function paymentUrl(int $days = 30): string
    {
        return URL::temporarySignedRoute('reservations.payment.pay', now()->addDays($days), ['reservation' => $this->id]);
    }

    /**
     * Page « établissement » vers laquelle renvoyer le client (fiche prestataire si liée,
     * sinon fiche hébergement — cf. hebergements sans prestataire lié, cas fréquent dans ce
     * projet — puis accueil en dernier recours). route('providers.show', null) lève sinon une
     * UrlGenerationException (paramètre requis manquant), pas juste un lien cassé.
     */
    public function establishmentUrl(): string
    {
        if ($this->provider?->slug) {
            return route('providers.show', $this->provider->slug);
        }

        if ($this->accommodation?->slug) {
            return route('accommodations.show', $this->accommodation->slug);
        }

        return route('home');
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function paymentStatusOptions(): array
    {
        return self::PAYMENT_STATUS_LABELS;
    }

    public function labelForStatus(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function labelForPaymentStatus(): string
    {
        return __(self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? $this->payment_status);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('full_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('accommodation_name', 'like', $like)
                ->orWhere('room_name', 'like', $like);
        });
    }

    public function scopeStatusFilter(Builder $query, ?string $status): Builder
    {
        if ($status === null || $status === '' || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeCreatedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Vérifie si une chambre d'un hébergement a déjà une réservation active
     * (non annulée, pas un paiement en échec/remboursé) qui chevauche la
     * période donnée.
     *
     * Chaque type de chambre (room_id, ou room_name à défaut pour les
     * réservations créées avant l'ajout de cet identifiant) peut déclarer un
     * stock ($units). Sans stock renseigné, il est traité comme une unité
     * réservable unique (comportement historique).
     *
     * Le filtre sur payment_status exclut seulement les échecs/remboursements
     * (la place se libère alors réellement) — une demande simple sans paiement
     * en ligne (payment_status par défaut 'unpaid', flux reservations.store)
     * reste bloquante : c'est une demande active sur la chambre, pas une
     * réservation annulée, elle doit donc empêcher un double-booking comme
     * n'importe quelle autre.
     *
     * Quand $roomId est fourni, on cible cette chambre précisément par son
     * identifiant stable, tout en restant compatible avec les réservations
     * historiques (sans room_id) qui portent sur la même chambre par son nom —
     * sinon une chambre renommée ou une réservation ancienne pourrait laisser
     * passer un double-booking non détecté.
     */
    public static function hasConflict(
        int $accommodationId,
        string $roomName,
        string $checkIn,
        string $checkOut,
        ?int $ignoreId = null,
        ?string $roomId = null,
        ?int $units = null,
        int $requestedRooms = 1
    ): bool {
        $query = static::activeForRoom($accommodationId, $roomName, $roomId)
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn);

        // Stock non renseigné : comportement historique, la chambre est une unité unique.
        if ($units === null) {
            return $query->exists();
        }

        // Stock renseigné : refus seulement si, une nuit du séjour, les chambres déjà
        // occupées plus celles demandées dépassent le nombre d'unités de ce type.
        $occupancy = static::dailyOccupancy($query->get(['check_in', 'check_out', 'rooms_count']), $checkIn, $checkOut);

        return (max($occupancy ?: [0]) + max(1, $requestedRooms)) > max(1, $units);
    }

    /** Réservations actives (bloquantes) d'une chambre — périmètre commun à hasConflict() et blockedRanges(). */
    private static function activeForRoom(int $accommodationId, string $roomName, ?string $roomId): Builder
    {
        return static::where('accommodation_id', $accommodationId)
            ->where(function (Builder $q) use ($roomName, $roomId) {
                if ($roomId) {
                    $q->where('room_id', $roomId)
                        ->orWhere(function (Builder $q2) use ($roomName) {
                            $q2->whereNull('room_id')->where('room_name', $roomName);
                        });
                } else {
                    $q->where('room_name', $roomName);
                }
            })
            ->whereIn('status', [self::STATUS_NEW, self::STATUS_CONFIRMED])
            ->whereNotIn('payment_status', [self::PAYMENT_FAILED, self::PAYMENT_REFUNDED]);
    }

    /**
     * Nombre de chambres occupées par nuit (clé Y-m-d) sur [from, to[.
     *
     * @return array<string, int>
     */
    private static function dailyOccupancy(iterable $reservations, string $from, string $to): array
    {
        $start = \Carbon\CarbonImmutable::parse($from)->startOfDay();
        $end = \Carbon\CarbonImmutable::parse($to)->startOfDay();
        $days = [];
        foreach ($reservations as $r) {
            $day = \Carbon\CarbonImmutable::parse($r->check_in)->startOfDay()->max($start);
            $last = \Carbon\CarbonImmutable::parse($r->check_out)->startOfDay()->min($end);
            for (; $day < $last; $day = $day->addDay()) {
                $key = $day->format('Y-m-d');
                $days[$key] = ($days[$key] ?? 0) + max(1, (int) $r->rooms_count);
            }
        }

        return $days;
    }

    /**
     * Périodes déjà bloquées (réservations actives) pour une chambre donnée —
     * alimente le calendrier de disponibilité affiché côté client. Même
     * périmètre de statuts que hasConflict(), pour rester cohérent : toute
     * période affichée comme indisponible ici serait aussi refusée à la
     * validation serveur.
     *
     * @return array<int, array{check_in: string, check_out: string}>
     */
    public static function blockedRanges(int $accommodationId, string $roomName, ?string $roomId = null, ?int $units = null): array
    {
        $reservations = static::activeForRoom($accommodationId, $roomName, $roomId)
            ->orderBy('check_in')
            ->get(['check_in', 'check_out', 'rooms_count']);

        if ($units === null) {
            return $reservations->map(fn (self $r) => [
                'check_in' => $r->check_in->format('Y-m-d'),
                'check_out' => $r->check_out->format('Y-m-d'),
            ])->all();
        }

        if ($reservations->isEmpty()) {
            return [];
        }

        // Avec un stock, seules les nuits complètes (toutes les unités prises) sont bloquées ;
        // les nuits consécutives sont regroupées en périodes [check_in, check_out[.
        $occupancy = static::dailyOccupancy(
            $reservations,
            $reservations->min('check_in')->format('Y-m-d'),
            $reservations->max('check_out')->format('Y-m-d')
        );
        ksort($occupancy);

        $ranges = [];
        foreach ($occupancy as $day => $count) {
            if ($count < max(1, $units)) {
                continue;
            }
            $next = \Carbon\CarbonImmutable::parse($day)->addDay()->format('Y-m-d');
            $lastIndex = count($ranges) - 1;
            if ($lastIndex >= 0 && $ranges[$lastIndex]['check_out'] === $day) {
                $ranges[$lastIndex]['check_out'] = $next;
            } else {
                $ranges[] = ['check_in' => $day, 'check_out' => $next];
            }
        }

        return $ranges;
    }

    /**
     * Périodes fermées à la vente par l'établissement (travaux, fermeture annuelle…), au format
     * du calendrier : check_out = lendemain de la dernière nuit fermée.
     *
     * @return array<int, array{check_in: string, check_out: string}>
     */
    public static function closedRanges(?array $room): array
    {
        $closed = collect($room['closed_ranges'] ?? [])
            ->filter(fn ($r) => ! empty($r['from']) && ! empty($r['to']))
            ->map(fn ($r) => [
                'check_in' => \Carbon\CarbonImmutable::parse($r['from'])->format('Y-m-d'),
                'check_out' => \Carbon\CarbonImmutable::parse($r['to'])->addDay()->format('Y-m-d'),
            ]);
        // Dates réservées sur une autre plateforme (synchronisation iCal) : fin déjà exclusive.
        $external = collect($room['external_blocks'] ?? [])
            ->filter(fn ($r) => ! empty($r['from']) && ! empty($r['to']))
            ->map(fn ($r) => ['check_in' => $r['from'], 'check_out' => $r['to']]);

        return $closed->merge($external)->values()->all();
    }

    /** Le séjour [checkIn, checkOut[ touche-t-il une période fermée de la chambre ? */
    public static function roomClosedDuring(?array $room, string $checkIn, string $checkOut): bool
    {
        foreach (static::closedRanges($room) as $r) {
            if ($r['check_in'] < $checkOut && $r['check_out'] > $checkIn) {
                return true;
            }
        }

        return false;
    }

    /** Nombre d'unités d'un type de chambre (null = ancien comportement, une seule unité). */
    public static function roomUnits(?array $room): ?int
    {
        $units = $room['units'] ?? null;

        return $units === null || $units === '' ? null : max(1, (int) $units);
    }
}

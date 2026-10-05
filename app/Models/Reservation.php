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
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
        ];
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
        return self::COMPUTED_STATUS_LABELS[$this->computed_status] ?? $this->computed_status;
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
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function labelForPaymentStatus(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? $this->payment_status;
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
     * Hypothèse MVP : chaque chambre (room_id, ou room_name à défaut pour les
     * réservations créées avant l'ajout de cet identifiant) est traitée comme
     * une unité réservable unique — pas de gestion de quantité d'inventaire.
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
        ?string $roomId = null
    ): bool {
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
            ->whereNotIn('payment_status', [self::PAYMENT_FAILED, self::PAYMENT_REFUNDED])
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->exists();
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
    public static function blockedRanges(int $accommodationId, string $roomName, ?string $roomId = null): array
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
            ->whereNotIn('payment_status', [self::PAYMENT_FAILED, self::PAYMENT_REFUNDED])
            ->orderBy('check_in')
            ->get(['check_in', 'check_out'])
            ->map(fn (self $r) => [
                'check_in' => $r->check_in->format('Y-m-d'),
                'check_out' => $r->check_out->format('Y-m-d'),
            ])
            ->all();
    }
}

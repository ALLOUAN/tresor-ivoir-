<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Réservation de transport en ligne (transfert, chauffeur, location, ligne) : prix figé, véhicule réservé,
 * chauffeur assigné, statut du trajet, annulation gratuite et modification, billet QR pour les lignes.
 */
class TransportBooking extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_LABELS = [
        self::STATUS_PENDING_PAYMENT => 'Paiement en attente',
        self::STATUS_PAID => 'Confirmée',
        self::STATUS_CANCELLED => 'Annulée',
        self::STATUS_REFUNDED => 'Annulée et remboursée',
        self::STATUS_FAILED => 'Paiement échoué',
    ];

    public const TRIP_STATUSES = [
        'assigned' => 'Chauffeur assigné',
        'en_route' => 'Chauffeur en route',
        'arrived' => 'Chauffeur arrivé au point de prise en charge',
        'in_progress' => 'Trajet en cours',
        'completed' => 'Trajet terminé',
        'no_show' => 'Client absent',
    ];

    protected $fillable = [
        'transport_offer_id', 'provider_id', 'user_id', 'transport_vehicle_id', 'transport_driver_id', 'transport_trip_id', 'mode',
        'offer_name', 'pickup_label', 'pickup_lat', 'pickup_lng', 'dropoff_label', 'dropoff_lat', 'dropoff_lng', 'distance_km',
        'starts_at', 'ends_at', 'round_trip', 'return_at', 'flight_number', 'passengers', 'luggage', 'seats', 'extras', 'price_lines',
        'amount_total_xof', 'deposit_on_site_xof', 'commission_percent', 'status', 'trip_status', 'gateway', 'gateway_txn_id',
        'paid_at', 'cancellable_until', 'cancelled_at', 'refund_xof', 'documents', 'contract_accepted_at', 'inspection',
        'ticket_code', 'checked_in_at', 'full_name', 'email', 'phone', 'message',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime', 'ends_at' => 'datetime', 'return_at' => 'datetime', 'round_trip' => 'boolean',
            'passengers' => 'integer', 'luggage' => 'integer', 'seats' => 'array', 'extras' => 'array', 'price_lines' => 'array',
            'amount_total_xof' => 'integer', 'deposit_on_site_xof' => 'integer', 'commission_percent' => 'float',
            'distance_km' => 'float', 'paid_at' => 'datetime', 'cancellable_until' => 'datetime', 'cancelled_at' => 'datetime',
            'refund_xof' => 'integer', 'documents' => 'array', 'contract_accepted_at' => 'datetime', 'inspection' => 'array',
            'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (TransportBooking $b) => $b->uuid ??= (string) Str::uuid());
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(TransportOffer::class, 'transport_offer_id')->withTrashed();
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TransportVehicle::class, 'transport_vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(TransportDriver::class, 'transport_driver_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TransportTrip::class, 'transport_trip_id');
    }

    public function getReferenceAttribute(): string
    {
        return 'TRS-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function tripStatusLabel(): ?string
    {
        return $this->trip_status ? __(self::TRIP_STATUSES[$this->trip_status] ?? $this->trip_status) : null;
    }

    public function isCancellableByClient(): bool
    {
        return $this->status === self::STATUS_PAID && ! in_array($this->trip_status, ['in_progress', 'completed', 'no_show'], true)
            && $this->cancellable_until && now()->lt($this->cancellable_until);
    }

    public function canBeModified(): bool
    {
        return $this->isCancellableByClient() && in_array($this->mode, ['transfer', 'chauffeur'], true);
    }

    public function needsDocuments(): bool
    {
        return $this->mode === 'rental' && $this->status === self::STATUS_PAID
            && (empty($this->documents['license']) || empty($this->documents['id_card']) || ! $this->contract_accepted_at);
    }

    public function ticketCode(): ?string
    {
        if ($this->status !== self::STATUS_PAID) {
            return null;
        }
        if (! $this->ticket_code) {
            do {
                $code = strtoupper(Str::random(10));
            } while (static::where('ticket_code', $code)->exists() || TouristVisit::where('ticket_code', $code)->exists() || ActivityBooking::where('ticket_code', $code)->exists());
            $this->forceFill(['ticket_code' => $code])->save();
        }

        return $this->ticket_code;
    }

    public function ticketCheckUrl(): ?string
    {
        return ($code = $this->ticketCode()) ? route('provider.tickets.show', $code) : null;
    }

    public function confirmationUrl(): string
    {
        return URL::temporarySignedRoute('transport-booking.show', $this->starts_at->copy()->addDays(30)->max(now()->addDays(30)), ['transportBooking' => $this->id]);
    }
}

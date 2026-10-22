<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'provider_id', 'user_id', 'reservation_id', 'is_verified', 'rating',
        'rating_quality', 'rating_price', 'rating_welcome', 'rating_clean',
        'title', 'comment', 'author_name', 'visit_date',
        'status', 'moderated_by', 'moderated_at', 'rejection_reason', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'moderated_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function replies()
    {
        return $this->hasMany(ReviewReply::class)->latest();
    }

    public function reply()
    {
        return $this->hasOne(ReviewReply::class)->latestOfMany();
    }

    public function latestReply()
    {
        return $this->hasOne(ReviewReply::class)->latestOfMany();
    }

    /**
     * Preuve de passage de l'auteur chez ce prestataire : un séjour payé déjà commencé, ou une
     * visite payée. Retourne [reservation_id|null, vérifié?].
     *
     * @return array{0: ?int, 1: bool}
     */
    public static function verifiedStayFor(User $user, Provider $provider): array
    {
        $reservation = Reservation::ownedBy($user)
            ->where('provider_id', $provider->id)
            ->where('status', '!=', Reservation::STATUS_CANCELLED)
            ->where('payment_status', Reservation::PAYMENT_DEPOSIT_PAID)
            ->whereDate('check_in', '<=', now()->toDateString())
            ->latest('check_in')
            ->first();
        if ($reservation) {
            return [$reservation->id, true];
        }

        $booked = BookingRequest::where('user_id', $user->id)
            ->where('provider_id', $provider->id)
            ->where('status', BookingRequest::STATUS_CONFIRMED)
            ->whereDate('booking_date', '<=', now()->toDateString())
            ->exists();
        if ($booked) {
            return [null, true];
        }

        $visited = TouristVisit::where('user_id', $user->id)
            ->where('provider_id', $provider->id)
            ->where('status', TouristVisit::STATUS_PAID)
            ->exists();

        return [null, $visited];
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}

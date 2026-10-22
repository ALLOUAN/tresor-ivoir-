<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Provider extends Model
{
    use \App\Models\Concerns\HasLocalizedFields, SoftDeletes;

    /**
     * Prestataire géré par cet utilisateur : celui qu'il possède, ou celui dont il est membre de l'équipe.
     * Point d'entrée unique de l'espace prestataire (remplace where('user_id', Auth::id())).
     */
    public static function forUserQuery(?int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()->where(fn ($q) => $q->where('user_id', $userId)
            ->orWhereIn('id', ProviderMember::where('user_id', $userId)->select('provider_id')));
    }

    public static function forUser(?int $userId): ?self
    {
        return $userId ? static::forUserQuery($userId)->orderByRaw('user_id = ? desc', [$userId])->first() : null;
    }

    /** Prestataire de la catégorie Art & Créations (accepte les commandes sur mesure). */
    public function isArtist(): bool
    {
        $cat = $this->category;

        return ($cat?->parent_id ? $cat->parent?->slug : $cat?->slug) === 'art-creations';
    }

    /** Réponses rapides par défaut, proposées tant que l'établissement n'a pas écrit les siennes. */
    public const DEFAULT_QUICK_REPLIES = [
        'Bonjour {client}, merci pour votre message ! Nous revenons vers vous très vite.',
        'Oui, c\'est disponible à la date demandée. Vous pouvez réserver directement depuis notre fiche.',
        'Désolé {client}, nous sommes complets à cette date. Pouvons-nous vous proposer un autre jour ?',
        'Nous sommes ouverts tous les jours. N\'hésitez pas à nous appeler pour toute précision.',
        'Merci {client}, à très bientôt chez {etablissement} !',
    ];

    /** @return list<string> */
    public function quickReplies(): array
    {
        return array_values(array_filter($this->quick_replies ?? [])) ?: self::DEFAULT_QUICK_REPLIES;
    }

    /** Badge « Répond en moins de… » (cf. providers:response-stats), si l'établissement répond à au moins 80 % des demandes. */
    public function responseBadge(): ?string
    {
        if ($this->response_minutes === null || ($this->response_rate !== null && $this->response_rate < 80)) {
            return null;
        }

        return match (true) {
            $this->response_minutes <= 60 => __('Répond en moins d\'1 h'),
            $this->response_minutes <= 180 => __('Répond en moins de 3 h'),
            $this->response_minutes <= 1440 => __('Répond en moins de 24 h'),
            default => null,
        };
    }

    /** L'utilisateur est-il le propriétaire (et pas un simple membre de l'équipe) ? */
    public static function isOwner(?int $userId): bool
    {
        return $userId && static::where('user_id', $userId)->exists();
    }

    public function members()
    {
        return $this->hasMany(ProviderMember::class);
    }

    protected $fillable = [
        'uuid', 'user_id', 'category_id', 'name', 'slug',
        'description_fr', 'description_en', 'short_desc_fr',
        'logo_url', 'cover_url', 'address', 'city', 'region',
        'latitude', 'longitude', 'phone', 'phone2', 'email',
        'website', 'facebook_url', 'instagram_url', 'tiktok_url', 'linkedin_url',
        'price_range', 'price_min', 'price_max',
        'status', 'is_verified', 'is_featured', 'gift_cards_enabled', 'quick_replies',
        'response_minutes', 'response_rate', 'response_stats_at',
        'payout_method', 'payout_account_number', 'payout_account_name',
        'meta_title_fr', 'meta_desc_fr', 'meta_title_en', 'meta_desc_en',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'gift_cards_enabled' => 'boolean',
            'quick_replies' => 'array',
            'response_minutes' => 'integer',
            'response_rate' => 'integer',
            'response_stats_at' => 'datetime',
            'is_featured' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Provider $p) => $p->uuid ??= (string) Str::uuid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ProviderCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->hasMany(ProviderTag::class);
    }

    public function hours()
    {
        return $this->hasMany(ProviderHour::class)->orderBy('day_of_week');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function debts()
    {
        return $this->hasMany(ProviderDebt::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function themeTags()
    {
        return $this->belongsToMany(Tag::class, 'provider_tag')->withPivot('created_at');
    }

    public function analytics()
    {
        return $this->hasMany(ProviderAnalytic::class);
    }

    public function sponsoredArticles()
    {
        return $this->hasMany(Article::class, 'sponsor_id');
    }

    public function conversations()
    {
        return $this->hasMany(ProviderConversation::class);
    }

    public function clientConversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function accommodation()
    {
        return $this->hasOne(Accommodation::class);
    }

    public function leisureVenue()
    {
        return $this->hasOne(LeisureVenue::class);
    }

    public function restaurant()
    {
        return $this->hasOne(Restaurant::class);
    }

    public function touristExperience()
    {
        return $this->hasOne(TouristExperience::class);
    }

    public function travelAgency()
    {
        return $this->hasOne(TravelAgency::class);
    }

    public function transportCompany()
    {
        return $this->hasOne(TransportCompany::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function tourPackages()
    {
        return $this->hasMany(TourPackage::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function transportOffers()
    {
        return $this->hasMany(TransportOffer::class);
    }

    public function artworks()
    {
        return $this->hasMany(Artwork::class);
    }
}

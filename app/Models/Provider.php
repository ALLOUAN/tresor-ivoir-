<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Provider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'user_id', 'category_id', 'name', 'slug',
        'description_fr', 'description_en', 'short_desc_fr',
        'logo_url', 'cover_url', 'address', 'city', 'region',
        'latitude', 'longitude', 'phone', 'phone2', 'email',
        'website', 'facebook_url', 'instagram_url', 'tiktok_url', 'linkedin_url',
        'price_range', 'price_min', 'price_max',
        'status', 'is_verified', 'is_featured',
        'meta_title_fr', 'meta_desc_fr', 'meta_title_en', 'meta_desc_en',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'uuid', 'category_id', 'created_by', 'provider_id',
        'title_fr', 'title_en', 'slug', 'description_fr', 'description_en',
        'cover_url', 'cover_alt', 'starts_at', 'ends_at',
        'is_recurring', 'recurrence_rule',
        'location_name', 'address', 'city', 'latitude', 'longitude',
        'price', 'is_free', 'ticket_url',
        'organizer_name', 'organizer_phone', 'organizer_email',
        'capacity', 'registration_deadline', 'timezone',
        'status', 'meta_title_fr', 'meta_desc_fr', 'meta_title_en', 'meta_desc_en', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'registration_deadline' => 'datetime',
            'published_at' => 'datetime',
            'is_recurring' => 'boolean',
            'is_free' => 'boolean',
            'capacity' => 'integer',
            'price' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Event $e) => $e->uuid ??= (string) Str::uuid());
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at->isFuture() && $this->status === 'published';
    }
}

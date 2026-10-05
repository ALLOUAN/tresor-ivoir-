<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'uuid', 'category_id', 'created_by', 'provider_id',
        'title_fr', 'title_en', 'subtitle_fr', 'subtitle_en', 'slug', 'description_fr', 'description_en',
        'cover_url', 'cover_alt', 'starts_at', 'ends_at',
        'is_recurring', 'recurrence_rule',
        'location_name', 'address', 'city', 'audience', 'latitude', 'longitude',
        'price', 'is_free', 'ticket_url',
        'organizer_name', 'organizer_phone', 'organizer_email',
        'capacity', 'registration_deadline', 'timezone', 'program',
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
            'program' => 'array',
        ];
    }

    /** Description riche de l'éditeur : nettoyée (HTML autorisé uniquement) à chaque écriture. */
    protected function descriptionFr(): Attribute
    {
        return Attribute::set(fn ($value) => HtmlSanitizer::forStorage($value));
    }

    protected function descriptionEn(): Attribute
    {
        return Attribute::set(fn ($value) => HtmlSanitizer::forStorage($value));
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

    public function photos()
    {
        return $this->media()->where('type', 'image')->orderBy('sort_order');
    }

    public function videos()
    {
        return $this->media()->where('type', 'video')->orderBy('sort_order');
    }

    public function hasProgram(): bool
    {
        return ! empty($this->program);
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at->isFuture() && $this->status === 'published';
    }
}

<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use \App\Models\Concerns\HasLocalizedFields;

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

    /**
     * À venir ou en cours : pas encore terminé. Fin = ends_at, ou à défaut la fin du jour de début
     * (un événement commencé ce matin reste affiché jusqu'au soir).
     */
    public function scopeNotFinished(\Illuminate\Database\Eloquent\Builder $q): \Illuminate\Database\Eloquent\Builder
    {
        return $q->where(fn ($w) => $w
            ->where(fn ($x) => $x->whereNotNull('ends_at')->where('ends_at', '>=', now()))
            ->orWhere(fn ($x) => $x->whereNull('ends_at')->where('starts_at', '>=', now()->startOfDay())));
    }

    /**
     * Période des listes publiques (site et API) : upcoming = à venir ou en cours (ordre chronologique),
     * past = terminés (plus récents d'abord), all = tous (plus récents d'abord).
     */
    public function scopeForPeriod(\Illuminate\Database\Eloquent\Builder $q, string $period): \Illuminate\Database\Eloquent\Builder
    {
        return match ($period) {
            'upcoming' => $q->notFinished()->orderBy('starts_at'),
            'past' => $q->finished()->orderByDesc('starts_at'),
            default => $q->orderByDesc('starts_at'),
        };
    }

    /** Recherche texte des listes publiques (site et API) : titre, description, ville. */
    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $q, string $term): \Illuminate\Database\Eloquent\Builder
    {
        return $q->where(fn ($w) => $w
            ->where('title_fr', 'like', "%{$term}%")
            ->orWhere('title_en', 'like', "%{$term}%")
            ->orWhere('description_fr', 'like', "%{$term}%")
            ->orWhere('city', 'like', "%{$term}%"));
    }

    /** Terminé (complément exact de notFinished). */
    public function scopeFinished(\Illuminate\Database\Eloquent\Builder $q): \Illuminate\Database\Eloquent\Builder
    {
        return $q->where(fn ($w) => $w
            ->where(fn ($x) => $x->whereNotNull('ends_at')->where('ends_at', '<', now()))
            ->orWhere(fn ($x) => $x->whereNull('ends_at')->where('starts_at', '<', now()->startOfDay())));
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

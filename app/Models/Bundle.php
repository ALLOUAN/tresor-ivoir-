<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/** Offre groupée : forfait multi-prestations vendu à un prix par personne. */
class Bundle extends Model
{
    use \App\Models\Concerns\HasLocalizedFields;

    /** Éléments du catalogue qu'une offre peut inclure : type => [modèle, libellé, route publique]. */
    public const INCLUDABLE = [
        'accommodation' => [Accommodation::class, 'Hébergement', 'accommodations.show'],
        'experience' => [TouristExperience::class, 'Site / expérience', 'tourist-experience.show'],
        'restaurant' => [Restaurant::class, 'Restaurant', 'restaurant.show'],
        'leisure' => [LeisureVenue::class, 'Loisir', 'leisure.show'],
        'transport' => [TransportCompany::class, 'Transport', 'transport-company.show'],
        'agency' => [TravelAgency::class, 'Agence / guide', 'travel-agency.show'],
    ];

    protected $fillable = [
        'provider_id', 'city_id', 'title', 'title_en', 'slug', 'summary', 'summary_en', 'description', 'description_en',
        'cover_image', 'duration_days', 'price_xof', 'min_people', 'max_people', 'includes', 'highlights',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'includes' => 'array',
            'highlights' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'price_xof' => 'integer',
            'duration_days' => 'integer',
            'min_people' => 'integer',
            'max_people' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(TouristCity::class, 'city_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Éléments inclus résolus (seulement ceux encore actifs), dans l'ordre saisi.
     *
     * @return Collection<int, array{type: string, label: string, model: Model, url: ?string}>
     */
    public function includedItems(): Collection
    {
        return collect($this->includes ?? [])->map(function ($item) {
            [$class, $label, $route] = self::INCLUDABLE[$item['type'] ?? ''] ?? [null, null, null];
            if (! $class) return null;
            $model = $class::query()->where('is_active', true)->find($item['id'] ?? 0);
            if (! $model) return null;

            return [
                'type' => $item['type'],
                'label' => __($label),
                'model' => $model,
                'url' => $model->slug ? route($route, $model->slug) : null,
            ];
        })->filter()->values();
    }

    /** Prix total pour un groupe. */
    public function totalFor(int $people): int
    {
        return $this->price_xof * max(1, $people);
    }

    public function getCoverUrlAttribute(): ?string
    {
        $img = $this->cover_image;
        if (! $img) return null;

        return preg_match('#^(https?:)?//|^/#', $img) ? $img : '/storage/'.ltrim($img, '/');
    }
}

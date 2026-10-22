<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Page publique d'un artiste : biographie, démarche, parcours, œuvres, abonnés ; réglages de vente
 * (livraison, assurance, retours) et de commande sur mesure.
 */
class ArtistProfile extends Model
{
    protected $fillable = [
        'provider_id', 'slug', 'display_name', 'tagline', 'bio', 'statement', 'city', 'birth_year', 'education', 'exhibitions',
        'photo', 'studio_photo', 'accepts_commissions', 'commission_from_xof', 'commission_deposit_percent',
        'pickup_address', 'abidjan_delivery_xof', 'country_delivery_xof', 'large_supplement_xof', 'insurance_percent', 'returns_accepted',
    ];

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer', 'accepts_commissions' => 'boolean', 'commission_from_xof' => 'integer',
            'commission_deposit_percent' => 'integer', 'abidjan_delivery_xof' => 'integer', 'country_delivery_xof' => 'integer',
            'large_supplement_xof' => 'integer', 'insurance_percent' => 'float', 'returns_accepted' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /** Profil de l'artiste, créé à la volée à partir de sa fiche prestataire. */
    public static function forProvider(Provider $provider): self
    {
        return static::firstOrCreate(['provider_id' => $provider->id], [
            'slug' => Str::slug($provider->name).'-'.$provider->id,
            'display_name' => $provider->name,
            'bio' => $provider->description_fr,
            'city' => $provider->city,
            'photo' => $provider->logo_url,
        ]);
    }

    public function followersCount(): int
    {
        return ArtistFollow::where('provider_id', $this->provider_id)->count();
    }

    public static function lines(?string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text))->map(fn ($l) => trim(ltrim(trim($l), '-•*')))->filter()->values()->all();
    }

    /**
     * Modes de livraison proposés avec leur prix pour une œuvre.
     *
     * @return array<string, array{label: string, fee: int}>
     */
    public function deliveryOptions(Artwork $artwork): array
    {
        $large = $artwork->isLarge() ? (int) $this->large_supplement_xof : 0;
        $modes = ['pickup' => ['label' => __('Retrait à l\'atelier').($this->pickup_address ? ' — '.$this->pickup_address : ''), 'fee' => 0]];
        if ($this->abidjan_delivery_xof !== null) {
            $modes['abidjan'] = ['label' => __('Livraison à Abidjan'), 'fee' => (int) $this->abidjan_delivery_xof + $large];
        }
        if ($this->country_delivery_xof !== null) {
            $modes['country'] = ['label' => __('Livraison en Côte d\'Ivoire (hors Abidjan)'), 'fee' => (int) $this->country_delivery_xof + $large];
        }

        return $modes;
    }

    public function insuranceFor(int $price): int
    {
        return (int) (ceil($price * (float) $this->insurance_percent / 100 / 100) * 100);
    }
}

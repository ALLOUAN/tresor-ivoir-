<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Circuit d'une agence. Fonctions « marché » : programme jour par jour, inclus / non inclus,
 * lieux de prise en charge, tarifs (enfant, chambre individuelle, dégressif, options),
 * acompte + solde, départ garanti à partir d'un minimum, conditions d'annulation.
 */
class TourPackage extends Model
{
    use SoftDeletes;

    public const DIFFICULTIES = ['facile' => 'Facile', 'modere' => 'Modéré', 'sportif' => 'Sportif'];

    protected $fillable = [
        'provider_id', 'category_id', 'name', 'slug', 'description', 'summary', 'price_xof',
        'duration_days', 'max_participants', 'images', 'is_available', 'is_featured', 'sort_order',
        'itinerary', 'included', 'not_included', 'pickup_points', 'child_price_xof', 'child_age_max',
        'single_supplement_xof', 'group_prices', 'extras', 'min_participants', 'guarantee_days',
        'deposit_percent', 'balance_due_days', 'cancellation_policy', 'instant_booking', 'difficulty', 'languages',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'price_xof' => 'integer',
            'duration_days' => 'integer',
            'max_participants' => 'integer',
            'itinerary' => 'array',
            'pickup_points' => 'array',
            'child_price_xof' => 'integer',
            'child_age_max' => 'integer',
            'single_supplement_xof' => 'integer',
            'group_prices' => 'array',
            'extras' => 'array',
            'min_participants' => 'integer',
            'guarantee_days' => 'integer',
            'deposit_percent' => 'integer',
            'balance_due_days' => 'integer',
            'cancellation_policy' => 'array',
            'instant_booking' => 'boolean',
            'languages' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Slug stable : créé une fois, jamais modifié (les liens partagés restent valables).
        static::created(function (TourPackage $tour) {
            if (! $tour->slug) {
                $tour->forceFill(['slug' => Str::slug($tour->name).'-'.$tour->id])->saveQuietly();
            }
        });
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'category_id');
    }

    public function departures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TourDeparture::class)->orderBy('departure_date');
    }

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TourBooking::class);
    }

    /** Départs à venir, ouverts et non annulés. */
    public function upcomingDepartures()
    {
        return $this->departures()->where('is_active', true)->whereNull('cancelled_at')
            ->whereDate('departure_date', '>=', now()->toDateString());
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /** Agence publique du circuit (fiche TravelAgency du prestataire). */
    public function agency(): ?TravelAgency
    {
        return TravelAgency::query()->where('provider_id', $this->provider_id)->where('is_active', true)->first();
    }

    public function publicUrl(): ?string
    {
        $agency = $this->agency();

        return $agency && $this->slug ? route('travel-agency.tour', [$agency->slug, $this->slug]) : null;
    }

    /* ── Contenu ── */

    /** @return array<int, array{title: string, description: string, meals: string, accommodation: string}> */
    public function days(): array
    {
        return collect($this->itinerary ?? [])
            ->filter(fn ($d) => is_array($d) && trim((string) ($d['title'] ?? '')) !== '')
            ->map(fn ($d) => [
                'title' => trim((string) $d['title']),
                'description' => trim((string) ($d['description'] ?? '')),
                'meals' => trim((string) ($d['meals'] ?? '')),
                'accommodation' => trim((string) ($d['accommodation'] ?? '')),
            ])->values()->all();
    }

    /** @return array<int, array{label: string, time: string, note: string}> */
    public function pickups(): array
    {
        return collect($this->pickup_points ?? [])
            ->filter(fn ($p) => is_array($p) && trim((string) ($p['label'] ?? '')) !== '')
            ->map(fn ($p) => ['label' => trim((string) $p['label']), 'time' => trim((string) ($p['time'] ?? '')), 'note' => trim((string) ($p['note'] ?? ''))])
            ->values()->all();
    }

    /** @return array<int, array{label: string, price_xof: int, per: string}> */
    public function extrasList(): array
    {
        return collect($this->extras ?? [])
            ->filter(fn ($e) => is_array($e) && trim((string) ($e['label'] ?? '')) !== '')
            ->map(fn ($e) => ['label' => trim((string) $e['label']), 'price_xof' => max(0, (int) ($e['price_xof'] ?? 0)), 'per' => ($e['per'] ?? 'person') === 'booking' ? 'booking' : 'person'])
            ->values()->all();
    }

    /** Paliers du tarif dégressif, triés par taille de groupe croissante. @return array<int, array{min: int, price_xof: int}> */
    public function groupPrices(): array
    {
        return collect($this->group_prices ?? [])
            ->filter(fn ($g) => is_array($g) && (int) ($g['min'] ?? 0) >= 1 && is_numeric($g['price_xof'] ?? null))
            ->map(fn ($g) => ['min' => (int) $g['min'], 'price_xof' => max(0, (int) $g['price_xof'])])
            ->sortBy('min')->values()->all();
    }

    /**
     * Barème d'annulation trié du plus tôt au plus tard : « plus de X jours avant : Y % remboursé ».
     *
     * @return array<int, array{days: int, percent: int}>
     */
    public function cancellationRules(): array
    {
        return collect($this->cancellation_policy ?? [])
            ->filter(fn ($r) => is_array($r) && is_numeric($r['days'] ?? null) && is_numeric($r['percent'] ?? null))
            ->map(fn ($r) => ['days' => max(0, (int) $r['days']), 'percent' => max(0, min(100, (int) $r['percent']))])
            ->sortByDesc('days')->values()->all();
    }

    public static function lines(?string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text))->map(fn ($l) => trim(ltrim(trim($l), '-•*')))->filter()->values()->all();
    }

    public function languagesList(): array
    {
        return collect($this->languages ?? [])->map(fn ($l) => trim((string) $l))->filter()->unique()->values()->all();
    }

    /* ── Prix ── */

    /**
     * Prix adulte par personne : prix du départ s'il est fixé, sinon palier dégressif selon le nombre
     * de voyageurs, sinon prix de base.
     */
    public function adultPrice(int $travelers, ?TourDeparture $departure = null): int
    {
        if ($departure && $departure->price_xof) {
            return (int) $departure->price_xof;
        }
        $tier = collect($this->groupPrices())->filter(fn ($g) => $g['min'] <= $travelers)->last();

        return $tier ? $tier['price_xof'] : (int) $this->price_xof;
    }

    /** Prix « à partir de » : le plus bas des paliers et des départs. */
    public function fromPrice(): int
    {
        $prices = collect([(int) $this->price_xof])
            ->merge(collect($this->groupPrices())->pluck('price_xof'))
            ->merge($this->upcomingDepartures()->whereNotNull('price_xof')->pluck('price_xof'))
            ->filter(fn ($p) => $p > 0);

        return (int) ($prices->min() ?? 0);
    }

    /**
     * Devis complet d'une réservation. Lève \InvalidArgumentException si la demande n'est pas valable.
     *
     * @param  array<int, int|string>  $extraIndexes
     * @return array{lines: array<int, array{label: string, amount_xof: int}>, total: int, deposit: int, adult_price: int, extras: array, travelers: int}
     */
    public function quote(?TourDeparture $departure, int $adults, int $children, int $singleRooms, array $extraIndexes = []): array
    {
        $travelers = $adults + $children;
        if ($adults < 1) {
            throw new \InvalidArgumentException(__('Au moins un adulte doit voyager.'));
        }
        if ($children > 0 && $this->child_price_xof === null) {
            $adults += $children; // pas de tarif enfant : tout le monde paie le tarif adulte
            $children = 0;
        }
        if ($singleRooms > $travelers) {
            throw new \InvalidArgumentException(__('Il ne peut pas y avoir plus de chambres individuelles que de voyageurs.'));
        }

        $adultPrice = $this->adultPrice($travelers, $departure);
        $lines = [['label' => $adults.' × '.__('Adulte').' ('.number_format($adultPrice, 0, ',', ' ').' FCFA)', 'amount_xof' => $adults * $adultPrice]];
        if ($children > 0) {
            $lines[] = ['label' => $children.' × '.__('Enfant').' ('.number_format((int) $this->child_price_xof, 0, ',', ' ').' FCFA)', 'amount_xof' => $children * (int) $this->child_price_xof];
        }
        if ($singleRooms > 0 && $this->single_supplement_xof) {
            $lines[] = ['label' => $singleRooms.' × '.__('Supplément chambre individuelle'), 'amount_xof' => $singleRooms * (int) $this->single_supplement_xof];
        }

        $offered = $this->extrasList();
        $extras = collect($extraIndexes)->map(fn ($i) => (int) $i)->unique()->filter(fn ($i) => isset($offered[$i]))
            ->map(fn ($i) => ['label' => $offered[$i]['label'], 'qty' => $offered[$i]['per'] === 'person' ? $travelers : 1, 'unit_price_xof' => $offered[$i]['price_xof']])
            ->values()->all();
        foreach ($extras as $e) {
            $lines[] = ['label' => ($e['qty'] > 1 ? $e['qty'].' × ' : '').$e['label'], 'amount_xof' => $e['qty'] * $e['unit_price_xof']];
        }

        $total = (int) collect($lines)->sum('amount_xof');

        return [
            'adults' => $adults, 'children' => $children, 'lines' => $lines, 'total' => $total, 'adult_price' => $adultPrice, 'extras' => $extras, 'travelers' => $travelers,
            'deposit' => $this->deposit_percent ? (int) (ceil($total * $this->deposit_percent / 100 / 100) * 100) : $total,
        ];
    }

    /** Acompte possible pour ce départ ? Non si le solde serait déjà dû (réservation tardive). */
    public function depositAllowedFor(\Carbon\CarbonInterface $departureDate): bool
    {
        return (bool) $this->deposit_percent
            && now('Africa/Abidjan')->startOfDay()->addDays((int) $this->balance_due_days)->lt($departureDate->copy()->startOfDay());
    }
}

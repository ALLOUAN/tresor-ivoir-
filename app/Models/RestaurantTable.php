<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Table du plan de salle d'un restaurant. */
class RestaurantTable extends Model
{
    public const SHAPES = ['round' => 'Ronde', 'square' => 'Carrée', 'long' => 'Rectangulaire'];

    /** Zones proposées par défaut (le restaurant peut en saisir d'autres). */
    public const ZONES = ['Salle', 'Terrasse', 'VIP', 'Bar', 'Étage', 'Jardin'];

    protected $fillable = ['restaurant_id', 'label', 'seats', 'zone', 'shape', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['seats' => 'integer', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('zone')->orderBy('sort_order')->orderBy('label');
    }

    /** Libellé lisible, ex. « Table T4 · Terrasse (4 places) ». */
    public function displayName(): string
    {
        return __('Table :label', ['label' => $this->label]).' · '.$this->zone.' ('.trans_choice(':count place|:count places', $this->seats).')';
    }
}

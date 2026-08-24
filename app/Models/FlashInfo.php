<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FlashInfo extends Model
{
    public const TYPES = ['info', 'success', 'warning', 'urgent'];

    protected $fillable = [
        'message', 'link_url', 'link_label', 'type',
        'is_active', 'is_dismissible', 'display_order',
        'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** Messages actifs et dans leur fenêtre de diffusion, prêts à être affichés publiquement. */
    public function scopeCurrentlyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}

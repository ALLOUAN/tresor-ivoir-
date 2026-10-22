<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Carte multi-séances (sessions = n) ou abonnement (sessions = null, illimité pendant la validité). */
class LeisurePass extends Model
{
    protected $fillable = ['provider_id', 'name', 'description', 'sessions', 'validity_days', 'price_xof', 'activity_ids', 'is_active'];

    protected function casts(): array
    {
        return ['sessions' => 'integer', 'validity_days' => 'integer', 'price_xof' => 'integer', 'activity_ids' => 'array', 'is_active' => 'boolean'];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function coversActivity(int $activityId): bool
    {
        return empty($this->activity_ids) || in_array($activityId, array_map('intval', $this->activity_ids), true);
    }

    public function summary(): string
    {
        return ($this->sessions ? trans_choice(':count séance|:count séances', $this->sessions) : __('Accès illimité'))
            .' · '.__('valable :n jours', ['n' => $this->validity_days]);
    }
}

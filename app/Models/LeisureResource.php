<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Ressource partagée d'un lieu (salle, terrain, couloir, lot de matériel) : `units` exemplaires disponibles. */
class LeisureResource extends Model
{
    protected $fillable = ['provider_id', 'name', 'units', 'is_active'];

    protected function casts(): array
    {
        return ['units' => 'integer', 'is_active' => 'boolean'];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_resource')->withPivot(['units', 'per_participant']);
    }
}

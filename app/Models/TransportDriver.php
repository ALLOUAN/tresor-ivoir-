<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Chauffeur d'une compagnie : communiqué au client quand il est assigné à un trajet. */
class TransportDriver extends Model
{
    protected $fillable = ['provider_id', 'name', 'phone', 'languages', 'photo', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}

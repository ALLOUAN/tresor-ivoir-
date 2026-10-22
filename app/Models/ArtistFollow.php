<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Abonnement d'un visiteur à un artiste : alerte à chaque nouvelle œuvre publiée. */
class ArtistFollow extends Model
{
    protected $fillable = ['user_id', 'provider_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Membre de l'équipe d'un prestataire (accès limité aux opérations du quotidien). */
class ProviderMember extends Model
{
    protected $fillable = ['provider_id', 'user_id', 'invited_by', 'job_title'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Édition limitée d'une œuvre dans un format : tirage de `edition_size` exemplaires numérotés. */
class ArtworkEdition extends Model
{
    protected $fillable = ['artwork_id', 'label', 'width_cm', 'height_cm', 'price_xof', 'edition_size', 'sold_count', 'is_active'];

    protected function casts(): array
    {
        return ['width_cm' => 'float', 'height_cm' => 'float', 'price_xof' => 'integer', 'edition_size' => 'integer', 'sold_count' => 'integer', 'is_active' => 'boolean'];
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function remaining(): int
    {
        return max(0, $this->edition_size - $this->sold_count);
    }
}

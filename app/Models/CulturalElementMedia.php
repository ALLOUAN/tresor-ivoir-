<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CulturalElementMedia extends Model
{
    protected $fillable = [
        'element_id', 'type', 'url', 'thumbnail_url',
        'caption', 'alt_text', 'duree_secondes', 'sort_order',
    ];

    public function element(): BelongsTo
    {
        return $this->belongsTo(CulturalElement::class, 'element_id');
    }
}

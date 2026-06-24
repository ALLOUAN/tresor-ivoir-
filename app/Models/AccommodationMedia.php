<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationMedia extends Model
{
    protected $table = 'accommodation_media';

    protected $fillable = [
        'accommodation_id', 'type',
        'url', 'caption', 'alt_text', 'sort_order',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function isPhoto(): bool
    {
        return $this->type === 'photo';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function scopePhotos($query)
    {
        return $query->where('type', 'photo');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }
}

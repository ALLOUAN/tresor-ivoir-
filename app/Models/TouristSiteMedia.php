<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TouristSiteMedia extends Model
{
    protected $table = 'tourist_site_media';

    protected $fillable = [
        'site_id', 'type', 'url',
        'thumbnail_url', 'caption', 'alt_text', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(TouristSite::class, 'site_id');
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryLike extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'liker_key',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(SiteMediaItem::class, 'media_id');
    }
}

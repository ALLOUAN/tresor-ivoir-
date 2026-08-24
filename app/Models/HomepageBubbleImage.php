<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageBubbleImage extends Model
{
    protected $fillable = [
        'homepage_bubble_id', 'image_url', 'display_order',
    ];

    public function bubble(): BelongsTo
    {
        return $this->belongsTo(HomepageBubble::class, 'homepage_bubble_id');
    }
}

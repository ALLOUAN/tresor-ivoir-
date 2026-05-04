<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppearanceSlide extends Model
{
    protected $fillable = [
        'media_type',
        'title',
        'subtitle',
        'description',
        'desktop_image_url',
        'tablet_image_url',
        'mobile_image_url',
        'video_desktop_url',
        'video_tablet_url',
        'video_mobile_url',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'media_type' => 'string',
        ];
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }
}

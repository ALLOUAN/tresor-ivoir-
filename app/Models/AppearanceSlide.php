<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppearanceSlide extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'desktop_image_url',
        'tablet_image_url',
        'mobile_image_url',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

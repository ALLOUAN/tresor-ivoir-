<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSocialSetting extends Model
{
    protected $fillable = [
        'facebook_url',
        'twitter_url',
        'linkedin_url',
        'instagram_url',
        'youtube_url',
        'whatsapp_phone',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            []
        );
    }
}

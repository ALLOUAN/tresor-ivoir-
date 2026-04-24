<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContactSetting extends Model
{
    protected $fillable = [
        'phone_1',
        'phone_2',
        'email_primary',
        'email_secondary',
        'contact_form_email',
        'opening_hours',
        'address',
        'latitude',
        'longitude',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            []
        );
    }
}

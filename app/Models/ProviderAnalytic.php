<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderAnalytic extends Model
{
    public $timestamps = false;

    protected $table = 'provider_analytics';

    protected $fillable = [
        'provider_id', 'date', 'views_count', 'clicks_phone',
        'clicks_website', 'clicks_direction', 'new_reviews', 'search_appearances',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}

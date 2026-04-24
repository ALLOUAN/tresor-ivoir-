<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderTag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider_id', 'tag', 'tag_en', 'icon', 'sort_order',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}

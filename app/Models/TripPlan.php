<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripPlan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'params' => 'array',
        'plan' => 'array',
    ];
}

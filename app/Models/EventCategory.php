<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug', 'name_fr', 'name_en', 'icon', 'color_hex', 'sort_order',
    ];

    public function events()
    {
        return $this->hasMany(Event::class, 'category_id');
    }
}

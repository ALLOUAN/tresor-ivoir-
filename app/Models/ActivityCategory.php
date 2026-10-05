<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug', 'name_fr', 'name_en', 'icon', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'category_id');
    }
}

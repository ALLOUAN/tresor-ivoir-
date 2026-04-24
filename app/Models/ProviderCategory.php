<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug', 'name_fr', 'name_en', 'icon', 'color_hex',
        'description_fr', 'description_en', 'parent_id',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(ProviderCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProviderCategory::class, 'parent_id');
    }

    public function providers()
    {
        return $this->hasMany(Provider::class, 'category_id');
    }
}

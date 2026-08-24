<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvidersHeroImage extends Model
{
    protected $fillable = [
        'image_url', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * NB : on n'utilise pas firstOrCreate(['id' => 1], []) — voir PrestationSetting::singleton()
     * pour l'explication du bug évité ici.
     */
    public static function singleton(): self
    {
        $instance = static::query()->find(1);

        if (! $instance) {
            $instance = new static();
            $instance->id = 1;
            $instance->save();
        }

        return $instance;
    }

    public function isVisible(): bool
    {
        return $this->is_active && (bool) $this->image_url;
    }
}

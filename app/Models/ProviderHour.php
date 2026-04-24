<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderHour extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider_id', 'day_of_week', 'open_time', 'close_time', 'is_closed', 'note',
    ];

    protected function casts(): array
    {
        return [
            'is_closed' => 'boolean',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function isOpen(): bool
    {
        return ! $this->is_closed && $this->open_time !== null;
    }
}

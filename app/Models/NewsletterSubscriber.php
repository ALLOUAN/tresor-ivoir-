<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email', 'first_name', 'user_id', 'locale', 'source',
        'status', 'confirmed_at', 'unsubscribed_at', 'confirm_token', 'ip_address',
    ];

    protected $hidden = ['confirm_token'];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function unsubscribe(): void
    {
        $this->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
    }
}

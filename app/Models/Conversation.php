<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = [
        'uuid',
        'client_id',
        'provider_id',
        'context_type',
        'context_id',
        'subject',
        'status',
        'client_blocked_at',
        'provider_blocked_at',
        'last_message_at',
        'last_message_preview',
    ];

    protected function casts(): array
    {
        return [
            'client_blocked_at' => 'datetime',
            'provider_blocked_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Conversation $c) => $c->uuid ??= (string) Str::uuid());
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ConversationReport::class);
    }

    public function isBlocked(): bool
    {
        return $this->client_blocked_at !== null || $this->provider_blocked_at !== null;
    }
}

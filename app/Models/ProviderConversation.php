<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderConversation extends Model
{
    protected $fillable = [
        'provider_id',
        'assigned_admin_id',
        'subject',
        'status',
        'priority',
        'last_message_at',
        'last_message_preview',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ProviderConversationMessage::class, 'conversation_id');
    }
}


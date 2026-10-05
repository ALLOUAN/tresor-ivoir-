<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConversationMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'is_flagged',
        'flagged_reason',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ConversationAttachment::class, 'message_id');
    }
}

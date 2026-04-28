<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderConversationAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'thumbnail_path',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'scan_result',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ProviderConversationMessage::class, 'message_id');
    }
}


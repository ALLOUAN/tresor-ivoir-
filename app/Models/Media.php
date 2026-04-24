<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Media extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uuid', 'mediable_type', 'mediable_id', 'collection', 'type',
        'mime_type', 'original_name', 'file_path', 'url', 'thumb_url',
        'size_bytes', 'width', 'height', 'duration_sec',
        'alt_text', 'caption', 'sort_order', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Media $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function mediable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}

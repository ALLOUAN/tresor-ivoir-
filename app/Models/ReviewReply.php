<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewReply extends Model
{
    protected $fillable = [
        'review_id', 'provider_id', 'reply_text', 'replied_by', 'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}

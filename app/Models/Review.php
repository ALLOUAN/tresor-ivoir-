<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'provider_id', 'user_id', 'rating',
        'rating_quality', 'rating_price', 'rating_welcome', 'rating_clean',
        'title', 'comment', 'author_name', 'visit_date',
        'status', 'moderated_by', 'moderated_at', 'rejection_reason', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'moderated_at' => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function replies()
    {
        return $this->hasMany(ReviewReply::class)->latest();
    }

    public function reply()
    {
        return $this->hasOne(ReviewReply::class)->latestOfMany();
    }

    public function latestReply()
    {
        return $this->hasOne(ReviewReply::class)->latestOfMany();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'title', 'subject_fr', 'subject_en', 'content_fr', 'content_en',
        'type', 'status', 'scheduled_at', 'sent_at',
        'recipients_count', 'opens_count', 'clicks_count', 'bounces_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function openRate(): float
    {
        if ($this->recipients_count === 0) {
            return 0.0;
        }

        return round($this->opens_count / $this->recipients_count * 100, 2);
    }

    public function clickRate(): float
    {
        if ($this->recipients_count === 0) {
            return 0.0;
        }

        return round($this->clicks_count / $this->recipients_count * 100, 2);
    }
}

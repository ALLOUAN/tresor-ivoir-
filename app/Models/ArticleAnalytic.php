<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleAnalytic extends Model
{
    public $timestamps = false;

    protected $table = 'article_analytics';

    protected $fillable = [
        'article_id', 'date', 'views_count', 'shares_count',
        'unique_visitors', 'avg_read_time_sec', 'bounce_rate', 'referrer_source',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'bounce_rate' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}

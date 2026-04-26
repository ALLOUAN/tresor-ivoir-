<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';

    protected $description = 'Publish pending articles whose publication date has passed';

    public function handle(): int
    {
        $now = now();

        $articles = Article::query()
            ->where('status', '!=', 'published')
            ->where(function ($query) use ($now) {
                $query->whereNotNull('published_at')
                    ->where('published_at', '<=', $now)
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->whereNotNull('scheduled_at')
                            ->where('scheduled_at', '<=', $now);
                    });
            })
            ->get();

        $published = 0;

        foreach ($articles as $article) {
            $publicationDate = $article->published_at ?? $article->scheduled_at ?? $now;

            $article->update([
                'status' => 'published',
                'published_at' => $publicationDate,
            ]);

            $published++;
        }

        $this->info("Articles publiés automatiquement: {$published}");

        return self::SUCCESS;
    }
}

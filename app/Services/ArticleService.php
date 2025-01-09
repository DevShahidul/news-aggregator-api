<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Models\Source;
use App\Services\NewsAPI\NewsAPIService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleService
{
    public function __construct(
        private readonly NewsAPIService $newsAPIService
    ) {}

    public function fetchAndStoreTopHeadlines(array $parameters = []): Collection
    {
        $articles = $this->newsAPIService->getTopHeadlines($parameters);
        return $this->storeArticles($articles);
    }

    public function searchAndStoreArticles(string $query, array $parameters = []): Collection
    {
        $articles = $this->newsAPIService->searchArticles($query, $parameters);
        return $this->storeArticles($articles);
    }

    private function storeArticles(Collection $articles): Collection
    {
        return $articles->map(function ($articleData) {
            try {
                return DB::transaction(function () use ($articleData) {
                    // Find or create the source
                    $source = Source::firstOrCreate(
                        ['name' => $articleData['source_data']['name']],
                        [
                            'external_id' => $articleData['source_data']['external_id'] ?? null,
                            'is_active' => true,
                        ]
                    );

                    // Create or update the article
                    return Article::updateOrCreate(
                        ['url' => $articleData['url']],
                        [
                            'title' => $articleData['title'],
                            'content' => $articleData['content'],
                            'image_url' => $articleData['image_url'],
                            'published_at' => $articleData['published_at'],
                            'author' => $articleData['author'],
                            'source_id' => $source->id,
                            'status' => 'published',
                        ]
                    );
                });
            } catch (\Exception $e) {
                Log::error('Error storing article: ' . $e->getMessage(), [
                    'article_data' => $articleData,
                    'trace' => $e->getTraceAsString(),
                ]);
                return null;
            }
        })->filter();
    }
} 
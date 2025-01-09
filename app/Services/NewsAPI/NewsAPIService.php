<?php

declare(strict_types=1);

namespace App\Services\NewsAPI;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NewsAPIService
{
    private PendingRequest $client;
    private const BASE_URL = 'https://newsapi.org/v2';
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct()
    {
        $this->client = Http::baseUrl(self::BASE_URL)
            ->withHeaders([
                'X-Api-Key' => config('services.news_api.key'),
            ])
            ->timeout(30);
    }

    public function getTopHeadlines(array $parameters = []): Collection
    {
        $cacheKey = 'news_api_headlines_' . md5(serialize($parameters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($parameters) {
            try {
                $response = $this->client->get('/top-headlines', $parameters);
                $response->throw();

                return collect($response->json('articles'))->map(function ($article) {
                    return $this->formatArticle($article);
                });
            } catch (\Exception $e) {
                Log::error('NewsAPI Error: ' . $e->getMessage(), [
                    'parameters' => $parameters,
                    'trace' => $e->getTraceAsString(),
                ]);
                return collect();
            }
        });
    }

    public function searchArticles(string $query, array $parameters = []): Collection
    {
        $parameters['q'] = $query;
        $cacheKey = 'news_api_search_' . md5(serialize($parameters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($parameters) {
            try {
                $response = $this->client->get('/everything', $parameters);
                $response->throw();

                return collect($response->json('articles'))->map(function ($article) {
                    return $this->formatArticle($article);
                });
            } catch (\Exception $e) {
                Log::error('NewsAPI Search Error: ' . $e->getMessage(), [
                    'parameters' => $parameters,
                    'trace' => $e->getTraceAsString(),
                ]);
                return collect();
            }
        });
    }

    private function formatArticle(array $articleData): array
    {
        return [
            'title' => $articleData['title'],
            'content' => $articleData['content'] ?? $articleData['description'],
            'url' => $articleData['url'],
            'image_url' => $articleData['urlToImage'] ?? null,
            'published_at' => $articleData['publishedAt'],
            'author' => $articleData['author'],
            'source_data' => [
                'name' => $articleData['source']['name'],
                'external_id' => $articleData['source']['id'],
            ],
        ];
    }
} 
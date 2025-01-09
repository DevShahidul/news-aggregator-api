<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ArticleService;
use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    protected $signature = 'articles:fetch';
    protected $description = 'Fetch articles from the News API';

    public function __construct(
        private readonly ArticleService $articleService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Fetching articles from News API...');

        try {
            $articles = $this->articleService->fetchAndStoreTopHeadlines();
            $this->info("Successfully fetched and stored {$articles->count()} articles.");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error fetching articles: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
} 
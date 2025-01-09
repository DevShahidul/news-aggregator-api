<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupArticlesCommand extends Command
{
    protected $signature = 'articles:cleanup';
    protected $description = 'Clean up articles older than 30 days';

    public function handle(): int
    {
        $this->info('Starting article cleanup...');

        try {
            $cutoffDate = now()->subDays(30);
            $count = Article::where('published_at', '<', $cutoffDate)->delete();

            $this->info("Successfully deleted {$count} old articles.");
            return self::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Error cleaning up articles: ' . $e->getMessage());
            $this->error('Failed to clean up articles: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
} 
<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Str;

class ArticleObserver
{
    public function creating(Article $article): void
    {
        if (empty($article->status)) {
            $article->status = 'draft';
        }

        // Generate a slug from the title if not set
        if (empty($article->slug)) {
            $article->slug = Str::slug($article->title);
        }
    }

    public function updating(Article $article): void
    {
        // Update slug if title has changed
        if ($article->isDirty('title')) {
            $article->slug = Str::slug($article->title);
        }
    }

    public function deleted(Article $article): void
    {
        // Clean up any related data when an article is deleted
        // For example, remove associated files, cache, etc.
    }
} 
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->per_page ?? 15;
        $query = $request->query('query');

        if ($query || $request->has(['category_id', 'source_id'])) {
            return $this->search($request);
        }
        
        $articles = Article::query()
            ->published()
            ->latest('published_at')
            ->with(['source', 'category'])
            ->paginate($perPage);

        return response()->json($articles);
    }

    public function show(Article $article): JsonResponse
    {
        $article->load(['source', 'category']);
        return response()->json($article);
    }

    public function search(Request $request): JsonResponse
    {
        $perPage = $request->per_page ?? 15;
        $query = $request->query('query');

        $searchQuery = Article::search($query)
            ->where('status', 'published');

        if ($request->category_id) {
            $searchQuery->where('category_id', $request->category_id);
        }

        if ($request->source_id) {
            $searchQuery->where('source_id', $request->source_id);
        }

        if ($request->from_date) {
            $searchQuery->where('published_at', '>=', strtotime($request->from_date));
        }

        if ($request->to_date) {
            $searchQuery->where('published_at', '<=', strtotime($request->to_date));
        }

        $articles = $searchQuery
            ->orderBy($request->sort_by ?? 'published_at', $request->sort_direction ?? 'desc')
            ->paginate($perPage);

        return response()->json($articles);
    }

    public function fetchTopHeadlines(Request $request): JsonResponse
    {
        $articles = $this->articleService->fetchAndStoreTopHeadlines(
            $request->only(['country', 'category', 'sources', 'language'])
        );

        return response()->json([
            'data' => $articles,
            'total' => $articles->count(),
        ]);
    }
} 
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
        
        $articles = Article::query()
            ->when($request->category_id, fn($query) => $query->where('category_id', $request->category_id))
            ->when($request->source_id, fn($query) => $query->where('source_id', $request->source_id))
            ->published()
            ->latest('published_at');

        // If there are no articles, return an empty paginated response
        if ($articles->count() === 0) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'per_page' => $perPage,
                'current_page' => 1,
                'last_page' => 1,
                'from' => null,
                'to' => null,
            ]);
        }

        // Load relationships only if there are articles
        $articles = $articles->with(['source', 'category'])->paginate($perPage);

        return response()->json($articles);
    }

    public function show(Article $article): JsonResponse
    {
        $article->load(['source', 'category']);
        return response()->json($article);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:3'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $articles = Article::search($request->query('query'))
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->source_id, function ($query) use ($request) {
                $query->where('source_id', $request->source_id);
            })
            ->when($request->from_date, function ($query) use ($request) {
                $query->where('published_at', '>=', $request->from_date);
            })
            ->when($request->to_date, function ($query) use ($request) {
                $query->where('published_at', '<=', $request->to_date);
            })
            ->orderBy($request->sort_by ?? 'published_at', $request->sort_direction ?? 'desc')
            ->paginate($request->per_page ?? 15);

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
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
} 
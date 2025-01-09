<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\JsonResponse;

class SourceController extends Controller
{
    public function index(): JsonResponse
    {
        $sources = Source::query()
            ->active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $sources,
        ]);
    }
} 
<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SourceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json(['message' => 'Welcome to the News Aggregator API']);
});

// Public routes
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// Articles routes (public)
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/search', [ArticleController::class, 'search'])->name('articles.search');
Route::get('/articles/headlines', [ArticleController::class, 'fetchTopHeadlines'])->name('articles.headlines');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('auth.user');

    // User preferences routes
    Route::prefix('preferences')->group(function () {
        Route::get('/', [UserPreferenceController::class, 'index'])->name('preferences.index');
        Route::post('/', [UserPreferenceController::class, 'store'])->name('preferences.store');
        Route::delete('/{preference}', [UserPreferenceController::class, 'destroy'])->name('preferences.destroy');
        Route::post('/bulk', [UserPreferenceController::class, 'bulkUpdate'])->name('preferences.bulk-update');
    });

    // Categories routes
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

    // Sources routes
    Route::get('/sources', [SourceController::class, 'index'])->name('sources.index');
}); 
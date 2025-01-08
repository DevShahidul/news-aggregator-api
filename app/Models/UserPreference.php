<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'source_id',
        'preference_type',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
        'preference_type' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function scopeFavorites(Builder $query): Builder
    {
        return $query->where('preference_type', 'favorite');
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('preference_type', 'blocked');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc');
    }
} 
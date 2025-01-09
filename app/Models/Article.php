<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Article extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'title',
        'content',
        'url',
        'image_url',
        'published_at',
        'author',
        'source_id',
        'category_id',
        'status',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'string',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'author' => $this->author,
            'source' => $this->source?->name,
            'category' => $this->category?->name,
            'published_at' => $this->published_at?->timestamp,
            'status' => $this->status,
            'source_id' => $this->source_id,
            'category_id' => $this->category_id,
        ];
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return $this->id;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): mixed
    {
        return 'id';
    }

    /**
     * Configure the searchable settings.
     */
    public function searchableAs(): string
    {
        return 'articles';
    }

    /**
     * Get the indexable settings for the model.
     */
    public function searchableSettings(): array
    {
        return [
            'filterableAttributes' => [
                'source_id',
                'category_id',
                'status',
                'published_at',
            ],
            'sortableAttributes' => [
                'published_at',
                'id',
            ],
            'searchableAttributes' => [
                'title',
                'content',
                'author',
                'source',
                'category',
            ],
        ];
    }
} 
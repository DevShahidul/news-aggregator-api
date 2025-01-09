<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $sources = Source::all();

        foreach ($sources as $source) {
            foreach ($categories as $category) {
                // Create 2 articles for each source-category combination
                for ($i = 1; $i <= 2; $i++) {
                    $title = "Test Article {$i} from {$source->name} in {$category->name}";
                    
                    Article::create([
                        'title' => $title,
                        'slug' => Str::slug($title),
                        'description' => "This is a test article {$i} from {$source->name} in the {$category->name} category.",
                        'content' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
                        'author' => 'Test Author',
                        'url' => $source->base_url . '/' . Str::slug($title),
                        'image_url' => 'https://via.placeholder.com/800x400',
                        'published_at' => now()->subHours(rand(1, 48)),
                        'source_id' => $source->id,
                        'category_id' => $category->id,
                        'status' => 'published',
                        'is_featured' => rand(0, 1),
                        'view_count' => rand(0, 1000),
                    ]);
                }
            }
        }
    }
} 
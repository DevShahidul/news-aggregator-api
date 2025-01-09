<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default categories
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Technology news and updates'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business and finance news'],
            ['name' => 'Science', 'slug' => 'science', 'description' => 'Science and research news'],
            ['name' => 'Health', 'slug' => 'health', 'description' => 'Health and medical news'],
            ['name' => 'Entertainment', 'slug' => 'entertainment', 'description' => 'Entertainment and media news'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'is_active' => true,
            ]);
        }

        // Create default sources
        $sources = [
            [
                'name' => 'TechCrunch',
                'slug' => 'techcrunch',
                'description' => 'Technology news and analysis',
                'base_url' => 'https://techcrunch.com',
                'logo_url' => 'https://techcrunch.com/wp-content/uploads/2015/02/cropped-cropped-favicon-gradient.png',
            ],
            [
                'name' => 'Reuters',
                'slug' => 'reuters',
                'description' => 'Global news coverage',
                'base_url' => 'https://www.reuters.com',
                'logo_url' => 'https://www.reuters.com/pf/resources/images/reuters/logo-vertical-default.svg',
            ],
            [
                'name' => 'BBC News',
                'slug' => 'bbc-news',
                'description' => 'British Broadcasting Corporation',
                'base_url' => 'https://www.bbc.com/news',
                'logo_url' => 'https://nav.files.bbci.co.uk/orbit/3.0.0-672.b2a9bb5/img/blq-orbit-blocks_grey.svg',
            ],
            [
                'name' => 'CNN',
                'slug' => 'cnn',
                'description' => 'Cable News Network',
                'base_url' => 'https://www.cnn.com',
                'logo_url' => 'https://cdn.cnn.com/cnn/.e/img/3.0/global/misc/cnn-logo.png',
            ],
            [
                'name' => 'The Verge',
                'slug' => 'the-verge',
                'description' => 'Technology, science, and culture',
                'base_url' => 'https://www.theverge.com',
                'logo_url' => 'https://cdn.vox-cdn.com/uploads/chorus_asset/file/7395367/favicon-64x64.0.ico',
            ],
        ];

        foreach ($sources as $source) {
            Source::create([
                'name' => $source['name'],
                'slug' => $source['slug'],
                'description' => $source['description'],
                'base_url' => $source['base_url'],
                'logo_url' => $source['logo_url'],
                'is_active' => true,
                'api_config' => json_encode(['version' => '1.0']),
            ]);
        }

        // Seed test articles
        $this->call(ArticleSeeder::class);
    }
}

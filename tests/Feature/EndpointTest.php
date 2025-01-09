<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Source;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;
    private Source $source;
    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->category = Category::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Tech news',
            'is_active' => true,
        ]);

        $this->source = Source::create([
            'name' => 'TechNews',
            'slug' => 'tech-news',
            'description' => 'Tech news source',
            'base_url' => 'https://technews.com',
            'is_active' => true,
        ]);

        $this->article = Article::create([
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'url' => 'https://test.com/article',
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_public_endpoints(): void
    {
        // Test welcome endpoint
        $response = $this->getJson('/api');
        $response->assertStatus(200)
            ->assertJson(['message' => 'Welcome to the News Aggregator API']);

        // Test articles index
        $response = $this->getJson('/api/articles');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'current_page', 'total']);

        // Test article search
        $response = $this->getJson('/api/articles/search?query=test');
        $response->assertStatus(200);

        // Test article headlines
        $response = $this->getJson('/api/articles/headlines');
        $response->assertStatus(200);

        // Test single article
        $response = $this->getJson("/api/articles/{$this->article->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Test Article']);
    }

    public function test_authentication_endpoints(): void
    {
        // Test registration
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'access_token']);

        // Test login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'access_token']);
    }

    public function test_protected_endpoints(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Test user profile
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');
        $response->assertStatus(200)
            ->assertJsonFragment(['email' => 'test@example.com']);

        // Test categories list
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/categories');
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Technology']);

        // Test sources list
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/sources');
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'TechNews']);

        // Test preferences endpoints
        $this->test_preference_endpoints($token);

        // Test logout
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');
        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);
    }

    private function test_preference_endpoints(string $token): void
    {
        try {
            // Test create preference
            $response = $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson('/api/preferences', [
                    'category_id' => $this->category->id,
                    'preference_type' => 'favorite',
                    'priority' => 1,
                ]);
            
            if ($response->status() !== 201) {
                dump('Create preference failed:', $response->json());
            }
            
            $response->assertStatus(201)
                ->assertJsonStructure(['message', 'data']);

            // Test list preferences
            $response = $this->withHeader('Authorization', "Bearer {$token}")
                ->getJson('/api/preferences');
            
            if ($response->status() !== 200) {
                dump('List preferences failed:', $response->json());
            }
            
            $response->assertStatus(200)
                ->assertJsonStructure(['data']);

            // Get the created preference ID
            $preferenceId = $response->json('data.0.id');

            // Test delete preference
            $response = $this->withHeader('Authorization', "Bearer {$token}")
                ->deleteJson("/api/preferences/{$preferenceId}");
            
            if ($response->status() !== 200) {
                dump('Delete preference failed:', $response->json());
            }
            
            $response->assertStatus(200)
                ->assertJson(['message' => 'Preference deleted successfully']);

            // Test bulk update preferences
            $response = $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson('/api/preferences/bulk', [
                    'clear_existing' => true,
                    'preferences' => [
                        [
                            'source_id' => $this->source->id,
                            'preference_type' => 'favorite',
                            'priority' => 1,
                        ],
                    ],
                ]);
            
            if ($response->status() !== 200) {
                dump('Bulk update failed:', $response->json());
            }
            
            $response->assertStatus(200)
                ->assertJsonStructure(['message', 'data']);
        } catch (\Exception $e) {
            dump('Test failed with exception:', $e->getMessage(), $e->getTraceAsString());
            throw $e;
        }
    }
} 
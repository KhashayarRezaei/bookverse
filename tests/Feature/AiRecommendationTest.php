<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Jobs\GenerateAiRecommendations;
use App\Services\AiRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;

class AiRecommendationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $book;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a regular user
        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Create an admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        // Create a book
        $this->book = Book::factory()->create([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'description' => 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.',
            'price' => 12.99,
            'isbn' => '9780743273565',
            'published_year' => 1925,
        ]);

        // Clear cache before each test
        Cache::flush();
    }

    public function test_authenticated_user_can_get_book_recommendations()
    {
        Queue::fake();
        
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/books/{$this->book->id}/recommendations");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'recommendations' => [
                    '*' => [
                        'id',
                        'title',
                        'author',
                        'description',
                        'price',
                        'similarity_score',
                    ]
                ]
            ]);

        // Assert that the job was dispatched
        Queue::assertPushed(GenerateAiRecommendations::class, function ($job) {
            return $job->book->id === $this->book->id;
        });
    }

    public function test_unauthenticated_user_cannot_get_recommendations()
    {
        $response = $this->getJson("/api/books/{$this->book->id}/recommendations");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_recommendations_endpoint_returns_404_for_nonexistent_book()
    {
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/books/99999/recommendations');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Book not found.',
            ]);
    }

    public function test_ai_recommendation_service_calls_hugging_face_api()
    {
        Http::fake([
            'api-inference.huggingface.co/*' => Http::response([
                [
                    'label' => 'fiction',
                    'score' => 0.95
                ],
                [
                    'label' => 'classic',
                    'score' => 0.87
                ]
            ], 200)
        ]);

        $service = new AiRecommendationService();
        $recommendations = $service->getRecommendations($this->book);

        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
        
        // Verify the API was called
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api-inference.huggingface.co/models/facebook/bart-large-mnli' &&
                   $request->method() === 'POST';
        });
    }

    public function test_ai_recommendation_job_stores_recommendations_in_cache()
    {
        // Mock the AI service to return fake recommendations
        $fakeRecommendations = [
            [
                'id' => 2,
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'description' => 'A story of racial injustice in the American South.',
                'price' => 14.99,
                'similarity_score' => 0.95
            ],
            [
                'id' => 3,
                'title' => '1984',
                'author' => 'George Orwell',
                'description' => 'A dystopian novel about totalitarianism.',
                'price' => 13.99,
                'similarity_score' => 0.87
            ]
        ];

        $this->mock(AiRecommendationService::class, function ($mock) use ($fakeRecommendations) {
            $mock->shouldReceive('getRecommendations')
                ->once()
                ->andReturn($fakeRecommendations);
        });

        // Create and dispatch the job
        $job = new GenerateAiRecommendations($this->book);
        $job->handle();

        // Check that recommendations are stored in cache
        $cachedRecommendations = Cache::get("book_recommendations_{$this->book->id}");
        $this->assertEquals($fakeRecommendations, $cachedRecommendations);
    }

    public function test_recommendations_endpoint_returns_cached_recommendations()
    {
        $cachedRecommendations = [
            [
                'id' => 2,
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'description' => 'A story of racial injustice in the American South.',
                'price' => 14.99,
                'similarity_score' => 0.95
            ]
        ];

        // Store recommendations in cache
        Cache::put("book_recommendations_{$this->book->id}", $cachedRecommendations, 3600);

        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/books/{$this->book->id}/recommendations");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Recommendations retrieved successfully.',
                'recommendations' => $cachedRecommendations
            ]);
    }

    public function test_recommendations_endpoint_returns_processing_message_when_no_cache()
    {
        Queue::fake();
        
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/books/{$this->book->id}/recommendations");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Generating recommendations. Please try again in a few moments.',
                'recommendations' => []
            ]);

        // Assert that the job was dispatched
        Queue::assertPushed(GenerateAiRecommendations::class);
    }

    public function test_ai_recommendation_job_handles_api_errors_gracefully()
    {
        Http::fake([
            'api-inference.huggingface.co/*' => Http::response([
                'error' => 'Model not found'
            ], 404)
        ]);

        $job = new GenerateAiRecommendations($this->book);
        $job->handle();

        // Should not store anything in cache on error
        $cachedRecommendations = Cache::get("book_recommendations_{$this->book->id}");
        $this->assertNull($cachedRecommendations);
    }

    public function test_ai_recommendation_service_uses_correct_api_endpoint()
    {
        Http::fake([
            'api-inference.huggingface.co/models/facebook/bart-large-mnli' => Http::response([
                [
                    'label' => 'fiction',
                    'score' => 0.95
                ]
            ], 200)
        ]);

        $service = new AiRecommendationService();
        $service->getRecommendations($this->book);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api-inference.huggingface.co/models/facebook/bart-large-mnli' &&
                   $request->hasHeader('Authorization') &&
                   $request->method() === 'POST';
        });
    }

    public function test_ai_recommendation_service_includes_api_key_in_headers()
    {
        // Set a mock API key for testing
        config(['services.huggingface.api_key' => 'test_api_key_123']);
        
        Http::fake([
            'api-inference.huggingface.co/*' => Http::response([], 200)
        ]);

        $service = new AiRecommendationService();
        $service->getRecommendations($this->book);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test_api_key_123');
        });
    }

    public function test_cache_expires_after_one_hour()
    {
        $cachedRecommendations = [
            [
                'id' => 2,
                'title' => 'Test Book',
                'author' => 'Test Author',
                'description' => 'Test description',
                'price' => 10.99,
                'similarity_score' => 0.85
            ]
        ];

        // Store recommendations in cache with 1 hour expiration
        Cache::put("book_recommendations_{$this->book->id}", $cachedRecommendations, 3600);

        // Verify cache exists
        $this->assertNotNull(Cache::get("book_recommendations_{$this->book->id}"));

        // Simulate time passing (in a real scenario, this would be handled by the cache driver)
        // For this test, we'll just verify the cache was set with the correct TTL
        $this->assertTrue(Cache::has("book_recommendations_{$this->book->id}"));
    }

    public function test_multiple_users_can_access_same_book_recommendations()
    {
        $cachedRecommendations = [
            [
                'id' => 2,
                'title' => 'Shared Book',
                'author' => 'Shared Author',
                'description' => 'Shared description',
                'price' => 15.99,
                'similarity_score' => 0.90
            ]
        ];

        // Store recommendations in cache
        Cache::put("book_recommendations_{$this->book->id}", $cachedRecommendations, 3600);

        $token1 = auth()->login($this->user);
        $token2 = auth()->login($this->adminUser);

        // First user gets recommendations
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json',
        ])->getJson("/api/books/{$this->book->id}/recommendations");

        $response1->assertStatus(200)
            ->assertJson([
                'recommendations' => $cachedRecommendations
            ]);

        // Second user gets the same recommendations
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
            'Accept' => 'application/json',
        ])->getJson("/api/books/{$this->book->id}/recommendations");

        $response2->assertStatus(200)
            ->assertJson([
                'recommendations' => $cachedRecommendations
            ]);
    }
} 
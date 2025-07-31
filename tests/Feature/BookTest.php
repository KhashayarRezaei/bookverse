<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private User $user;
    private array $validBookData;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'email' => 'admin@bookverse.com',
            'is_admin' => true,
        ]);

        // Create regular user
        $this->user = User::factory()->create([
            'email' => 'user@bookverse.com',
            'is_admin' => false,
        ]);

        // Valid book data
        $this->validBookData = [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'published_year' => $this->faker->numberBetween(1900, 2024),
            'isbn' => $this->faker->isbn13(),
        ];
    }

    public function test_authenticated_admin_can_create_book_with_valid_data()
    {
        $token = auth()->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/books', $this->validBookData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'book' => [
                    'id',
                    'title',
                    'author',
                    'description',
                    'price',
                    'published_year',
                    'isbn',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('books', [
            'title' => $this->validBookData['title'],
            'author' => $this->validBookData['author'],
            'isbn' => $this->validBookData['isbn'],
        ]);
    }

    public function test_unauthenticated_users_cannot_create_books()
    {
        $response = $this->postJson('/api/books', $this->validBookData);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_non_admin_users_cannot_create_books()
    {
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/books', $this->validBookData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied. Admin privileges required.',
            ]);
    }

    public function test_get_books_returns_list_of_books()
    {
        // Create some books
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'author',
                        'description',
                        'price',
                        'published_year',
                        'isbn',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_books_returns_empty_array_when_no_books()
    {
        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_get_book_returns_book_details()
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'author',
                    'description',
                    'price',
                    'published_year',
                    'isbn',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                ],
            ]);
    }

    public function test_get_book_returns_404_for_nonexistent_book()
    {
        $response = $this->getJson('/api/books/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Book not found.',
            ]);
    }

    public function test_validation_errors_for_missing_required_fields()
    {
        $token = auth()->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/books', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'author',
                'description',
                'price',
                'published_year',
                'isbn',
            ]);
    }

    public function test_validation_errors_for_invalid_price()
    {
        $token = auth()->login($this->admin);

        $invalidData = $this->validBookData;
        $invalidData['price'] = -10;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/books', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_validation_errors_for_invalid_published_year()
    {
        $token = auth()->login($this->admin);

        $invalidData = $this->validBookData;
        $invalidData['published_year'] = 2030; // Future year

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/books', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['published_year']);
    }

    public function test_validation_errors_for_invalid_isbn()
    {
        $token = auth()->login($this->admin);

        $invalidData = $this->validBookData;
        $invalidData['isbn'] = 'invalid-isbn';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/books', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    public function test_admin_can_update_book()
    {
        $book = Book::factory()->create();
        $token = auth()->login($this->admin);

        $updateData = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'description' => 'Updated description',
            'price' => 29.99,
            'published_year' => 2023,
            'isbn' => '978-0-7475-3269-9',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/books/{$book->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Book updated successfully.',
                'book' => [
                    'title' => 'Updated Title',
                    'author' => 'Updated Author',
                ],
            ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
            'author' => 'Updated Author',
        ]);
    }

    public function test_admin_can_delete_book()
    {
        $book = Book::factory()->create();
        $token = auth()->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Book deleted successfully.',
            ]);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_non_admin_cannot_update_book()
    {
        $book = Book::factory()->create();
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/books/{$book->id}", $this->validBookData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied. Admin privileges required.',
            ]);
    }

    public function test_non_admin_cannot_delete_book()
    {
        $book = Book::factory()->create();
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied. Admin privileges required.',
            ]);
    }
} 
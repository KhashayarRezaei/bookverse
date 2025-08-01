<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminBookManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // Create regular user
        $this->regularUser = User::factory()->create([
            'is_admin' => false,
        ]);
    }

    /** @test */
    public function admin_can_view_all_books()
    {
        $books = Book::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/admin/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'title', 'author', 'description',
                        'price', 'isbn', 'published_year', 'created_at', 'updated_at',
                    ],
                ],
                'current_page', 'per_page', 'total',
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function regular_user_cannot_access_admin_books()
    {
        $response = $this->actingAs($this->regularUser, 'api')
            ->getJson('/api/admin/books');

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_admin_books()
    {
        $response = $this->getJson('/api/admin/books');

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_create_new_book()
    {
        $bookData = [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'description' => 'A test book description',
            'price' => 29.99,
            'isbn' => '978-1234567890',
            'published_year' => 2023,
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/admin/books', $bookData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Book created successfully',
                'data' => [
                    'title' => 'Test Book',
                    'author' => 'Test Author',
                    'price' => 29.99,
                    'isbn' => '978-1234567890',
                ],
            ]);

        $this->assertDatabaseHas('books', $bookData);
    }

    /** @test */
    public function admin_cannot_create_book_with_invalid_data()
    {
        $invalidData = [
            'title' => '', // Empty title
            'author' => 'Test Author',
            'description' => 'A test book description',
            'price' => -10, // Negative price
            'isbn' => 'invalid-isbn',
            'published_year' => 1800, // Too old
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/admin/books', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'price']);
    }

    /** @test */
    public function admin_cannot_create_book_with_duplicate_isbn()
    {
        $existingBook = Book::factory()->create(['isbn' => '978-1234567890']);

        $bookData = [
            'title' => 'Another Book',
            'author' => 'Another Author',
            'description' => 'Another book description',
            'price' => 19.99,
            'isbn' => '978-1234567890', // Duplicate ISBN
            'published_year' => 2023,
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/admin/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    /** @test */
    public function admin_can_view_specific_book()
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->admin, 'api')
            ->getJson("/api/admin/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                ],
            ]);
    }

    /** @test */
    public function admin_can_update_book()
    {
        $book = Book::factory()->create();
        $updateData = [
            'title' => 'Updated Book Title',
            'price' => 39.99,
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->putJson("/api/admin/books/{$book->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Book updated successfully',
                'data' => [
                    'id' => $book->id,
                    'title' => 'Updated Book Title',
                    'price' => 39.99,
                ],
            ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Book Title',
            'price' => 39.99,
        ]);
    }

    /** @test */
    public function admin_cannot_update_book_with_invalid_data()
    {
        $book = Book::factory()->create();
        $invalidData = [
            'price' => -5, // Negative price
            'published_year' => 3000, // Future year
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->putJson("/api/admin/books/{$book->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price', 'published_year']);
    }

    /** @test */
    public function admin_can_delete_book()
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson("/api/admin/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Book deleted successfully',
            ]);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /** @test */
    public function admin_can_paginate_books()
    {
        Book::factory()->count(25)->create();

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/admin/books?page=2&per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data', 'current_page', 'per_page', 'total',
            ]);

        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(2, $response->json('current_page'));
    }

    /** @test */
    public function regular_user_cannot_perform_admin_book_operations()
    {
        $book = Book::factory()->create();
        $bookData = [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'description' => 'A test book description',
            'price' => 29.99,
            'isbn' => '978-1234567890',
            'published_year' => 2023,
        ];

        // Try to view all books
        $response = $this->actingAs($this->regularUser, 'api')
            ->getJson('/api/admin/books');
        $response->assertStatus(403);

        // Try to create a book
        $response = $this->actingAs($this->regularUser, 'api')
            ->postJson('/api/admin/books', $bookData);
        $response->assertStatus(403);

        // Try to update a book
        $response = $this->actingAs($this->regularUser, 'api')
            ->putJson("/api/admin/books/{$book->id}", ['title' => 'Updated']);
        $response->assertStatus(403);

        // Try to delete a book
        $response = $this->actingAs($this->regularUser, 'api')
            ->deleteJson("/api/admin/books/{$book->id}");
        $response->assertStatus(403);
    }
}

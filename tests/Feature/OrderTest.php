<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private Book $book1;

    private Book $book2;

    private Book $book3;

    private array $validOrderData;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user
        $this->user = User::factory()->create([
            'email' => 'user@bookverse.com',
            'is_admin' => false,
        ]);

        // Create books
        $this->book1 = Book::factory()->create([
            'title' => 'Book 1',
            'price' => 15.99,
        ]);

        $this->book2 = Book::factory()->create([
            'title' => 'Book 2',
            'price' => 24.50,
        ]);

        $this->book3 = Book::factory()->create([
            'title' => 'Book 3',
            'price' => 9.99,
        ]);

        // Valid order data
        $this->validOrderData = [
            'items' => [
                ['book_id' => $this->book1->id, 'quantity' => 2],
                ['book_id' => $this->book3->id, 'quantity' => 1],
            ],
            'payment_method' => 'stripe',
        ];
    }

    public function test_authenticated_user_can_create_order_with_valid_data()
    {
        Event::fake();

        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', $this->validOrderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order' => [
                    'id',
                    'user_id',
                    'total_amount',
                    'payment_method',
                    'status',
                    'created_at',
                    'updated_at',
                    'items' => [
                        '*' => [
                            'id',
                            'order_id',
                            'book_id',
                            'quantity',
                            'unit_price',
                            'total_price',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]);

        // Check database
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'stripe',
            'status' => 'paid',
        ]);

        // Check order items
        $this->assertDatabaseHas('order_items', [
            'book_id' => $this->book1->id,
            'quantity' => 2,
            'unit_price' => 15.99,
            'total_price' => 31.98,
        ]);

        $this->assertDatabaseHas('order_items', [
            'book_id' => $this->book3->id,
            'quantity' => 1,
            'unit_price' => 9.99,
            'total_price' => 9.99,
        ]);

        // Check total amount calculation
        $expectedTotal = (15.99 * 2) + (9.99 * 1); // 31.98 + 9.99 = 41.97
        $this->assertDatabaseHas('orders', [
            'total_amount' => $expectedTotal,
        ]);

        // Check event was fired
        Event::assertDispatched(OrderPlaced::class);
    }

    public function test_unauthenticated_users_cannot_create_orders()
    {
        $response = $this->postJson('/api/orders', $this->validOrderData);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_can_fetch_their_own_orders()
    {
        // Create orders for the user
        $order1 = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 50.00,
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 75.00,
        ]);

        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'total_amount',
                        'payment_method',
                        'status',
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

        $this->assertCount(2, $response->json('data'));
    }

    public function test_user_cannot_fetch_orders_belonging_to_other_users()
    {
        // Create another user
        $otherUser = User::factory()->create();

        // Create order for other user
        Order::factory()->create([
            'user_id' => $otherUser->id,
            'total_amount' => 100.00,
        ]);

        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/orders');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_user_can_fetch_specific_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 50.00,
        ]);

        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'total_amount',
                    'payment_method',
                    'status',
                    'created_at',
                    'updated_at',
                    'items',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'user_id' => $this->user->id,
                    'total_amount' => 50.00,
                ],
            ]);
    }

    public function test_user_cannot_fetch_order_belonging_to_other_user()
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'total_amount' => 100.00,
        ]);

        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Order not found.',
            ]);
    }

    public function test_validation_errors_for_missing_items()
    {
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', [
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_validation_errors_for_empty_items_array()
    {
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', [
            'items' => [],
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_validation_errors_for_invalid_book_id()
    {
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', [
            'items' => [
                ['book_id' => 999, 'quantity' => 1],
            ],
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.book_id']);
    }

    public function test_validation_errors_for_invalid_quantity()
    {
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', [
            'items' => [
                ['book_id' => $this->book1->id, 'quantity' => 0],
            ],
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_validation_errors_for_missing_payment_method()
    {
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', [
            'items' => [
                ['book_id' => $this->book1->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_validation_errors_for_invalid_payment_method()
    {
        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', [
            'items' => [
                ['book_id' => $this->book1->id, 'quantity' => 1],
            ],
            'payment_method' => 'invalid_method',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_order_creation_with_paypal_payment_method()
    {
        Event::fake();

        $token = auth()->login($this->user);

        $orderData = [
            'items' => [
                ['book_id' => $this->book2->id, 'quantity' => 1],
            ],
            'payment_method' => 'paypal',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJson([
                'order' => [
                    'payment_method' => 'paypal',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'paypal',
            'total_amount' => 24.50,
        ]);

        Event::assertDispatched(OrderPlaced::class);
    }

    public function test_order_creation_with_multiple_items_calculates_total_correctly()
    {
        Event::fake();

        $token = auth()->login($this->user);

        $orderData = [
            'items' => [
                ['book_id' => $this->book1->id, 'quantity' => 3], // 15.99 * 3 = 47.97
                ['book_id' => $this->book2->id, 'quantity' => 2], // 24.50 * 2 = 49.00
                ['book_id' => $this->book3->id, 'quantity' => 1], // 9.99 * 1 = 9.99
            ],
            'payment_method' => 'stripe',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        $expectedTotal = (15.99 * 3) + (24.50 * 2) + (9.99 * 1); // 47.97 + 49.00 + 9.99 = 106.96

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => $expectedTotal,
        ]);

        Event::assertDispatched(OrderPlaced::class);
    }

    public function test_order_items_are_saved_with_correct_prices()
    {
        Event::fake();

        $token = auth()->login($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/orders', $this->validOrderData);

        $response->assertStatus(201);

        // Check first item
        $this->assertDatabaseHas('order_items', [
            'book_id' => $this->book1->id,
            'quantity' => 2,
            'unit_price' => 15.99,
            'total_price' => 31.98,
        ]);

        // Check second item
        $this->assertDatabaseHas('order_items', [
            'book_id' => $this->book3->id,
            'quantity' => 1,
            'unit_price' => 9.99,
            'total_price' => 9.99,
        ]);
    }
}

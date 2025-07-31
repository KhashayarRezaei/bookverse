<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderPlaced;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymentFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Order management and payment processing"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="List user orders",
     *     description="Get a paginated list of orders for the authenticated user",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=59.97),
     *                 @OA\Property(property="payment_method", type="string", example="stripe"),
     *                 @OA\Property(property="status", type="string", example="paid"),
     *                 @OA\Property(property="transaction_id", type="string", example="stripe_abc123_1234567890"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="order_id", type="integer", example=1),
     *                     @OA\Property(property="book_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=19.99),
     *                     @OA\Property(property="total_price", type="number", format="float", example=39.98),
     *                     @OA\Property(property="book", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="The Great Gatsby"),
     *                         @OA\Property(property="author", type="string", example="F. Scott Fitzgerald")
     *                     )
     *                 ))
     *             )),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=1),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="next_page_url", type="string"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="prev_page_url", type="string"),
     *             @OA\Property(property="to", type="integer", example=1),
     *             @OA\Property(property="total", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items.book')->paginate(10);

        return response()->json($orders);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     description="Create a new order with payment processing using the factory pattern",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items","payment_method"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="book_id", type="integer", example=1, description="Book ID"),
     *                 @OA\Property(property="quantity", type="integer", example=2, description="Quantity of books")
     *             )),
     *             @OA\Property(property="payment_method", type="string", example="stripe", description="Payment method (stripe or paypal)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order created successfully."),
     *             @OA\Property(property="order", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=59.97),
     *                 @OA\Property(property="payment_method", type="string", example="stripe"),
     *                 @OA\Property(property="status", type="string", example="paid"),
     *                 @OA\Property(property="transaction_id", type="string", example="stripe_abc123_1234567890"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="order_id", type="integer", example=1),
     *                     @OA\Property(property="book_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=19.99),
     *                     @OA\Property(property="total_price", type="number", format="float", example=39.98),
     *                     @OA\Property(property="book", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="The Great Gatsby"),
     *                         @OA\Property(property="author", type="string", example="F. Scott Fitzgerald")
     *                     )
     *                 ))
     *             ),
     *             @OA\Property(property="payment", type="object",
     *                 @OA\Property(property="status", type="string", example="success"),
     *                 @OA\Property(property="transaction_id", type="string", example="stripe_abc123_1234567890"),
     *                 @OA\Property(property="amount", type="number", format="float", example=59.97),
     *                 @OA\Property(property="gateway", type="string", example="stripe"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="timestamp", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Payment failed or validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment failed. Please try again."),
     *             @OA\Property(property="payment_error", type="object",
     *                 @OA\Property(property="status", type="string", example="error"),
     *                 @OA\Property(property="error", type="string", example="Payment processing failed")
     *             )
     *         )
     *     )
     * )
     */
    public function store(OrderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $orderItems = [];

            // Calculate total and prepare order items
            foreach ($request->items as $item) {
                $book = Book::findOrFail($item['book_id']);
                $itemTotal = $book->price * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'book_id' => $book->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $book->price,
                    'total_price' => $itemTotal,
                ];
            }

            // Process payment using the factory pattern
            $paymentFactory = new PaymentFactory();
            $paymentService = $paymentFactory->create($request->payment_method);
            $paymentResult = $paymentService->charge($request->user(), $totalAmount);

            // Check if payment was successful
            if ($paymentResult['status'] !== 'success') {
                return response()->json([
                    'message' => 'Payment failed. Please try again.',
                    'payment_error' => $paymentResult,
                ], 422);
            }

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'paid', // Update status to paid since payment was successful
                'transaction_id' => $paymentResult['transaction_id'] ?? null,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            // Load relationships for response
            $order->load('items.book');

            // Fire event
            event(new OrderPlaced($order));

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order,
                'payment' => $paymentResult,
            ], 201);
        });
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get a specific order",
     *     description="Retrieve details of a specific order by ID for the authenticated user",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="total_amount", type="number", format="float", example=59.97),
     *             @OA\Property(property="payment_method", type="string", example="stripe"),
     *             @OA\Property(property="status", type="string", example="paid"),
     *             @OA\Property(property="transaction_id", type="string", example="stripe_abc123_1234567890"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_id", type="integer", example=1),
     *                 @OA\Property(property="book_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=2),
     *                 @OA\Property(property="unit_price", type="number", format="float", example=19.99),
     *                 @OA\Property(property="total_price", type="number", format="float", example=39.98),
     *                 @OA\Property(property="book", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="The Great Gatsby"),
     *                     @OA\Property(property="author", type="string", example="F. Scott Fitzgerald")
     *                 )
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order not found.")
     *         )
     *     )
     * )
     */
    public function show(Request $request, string $id)
    {
        $order = $request->user()->orders()->with('items.book')->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'data' => $order,
        ]);
    }
}

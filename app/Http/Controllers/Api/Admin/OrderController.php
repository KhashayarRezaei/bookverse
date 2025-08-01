<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Administrative operations (admin only)"
 * )
 */
class OrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/admin/orders",
     *     summary="Get all orders (Admin)",
     *     description="Retrieve all orders with pagination for admin management",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by order status",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"pending", "paid", "shipped", "delivered", "cancelled"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');

        $query = Order::with(['user', 'items.book']);

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/orders/{id}",
     *     summary="Get a specific order (Admin)",
     *     description="Retrieve a specific order by ID for admin management",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json([
            'data' => $order->load(['user', 'items.book']),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/orders/{id}",
     *     summary="Update order status (Admin)",
     *     description="Update order status with admin privileges",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"status"},
     *
     *             @OA\Property(property="status", type="string", enum={"pending", "paid", "shipped", "delivered", "cancelled"}, example="shipped")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Order status updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,paid,shipped,delivered,cancelled',
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => $order->load(['user', 'items.book']),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/orders/stats/summary",
     *     summary="Get order statistics (Admin)",
     *     description="Retrieve order statistics for admin dashboard",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order statistics retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="total_orders", type="integer", example=150),
     *             @OA\Property(property="total_revenue", type="number", format="float", example=12500.50),
     *             @OA\Property(property="pending_orders", type="integer", example=5),
     *             @OA\Property(property="shipped_orders", type="integer", example=12),
     *             @OA\Property(property="delivered_orders", type="integer", example=120),
     *             @OA\Property(property="cancelled_orders", type="integer", example=3),
     *             @OA\Property(property="average_order_value", type="number", format="float", example=83.34)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function stats(): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return response()->json([
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'shipped_orders' => $shippedOrders,
            'delivered_orders' => $deliveredOrders,
            'cancelled_orders' => $cancelledOrders,
            'average_order_value' => round($averageOrderValue, 2),
        ]);
    }
}

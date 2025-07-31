<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\AiAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Analytics",
 *     description="Analytics and reporting operations"
 * )
 */
class AnalyticsController extends Controller
{
    protected $aiAnalyticsService;

    public function __construct(AiAnalyticsService $aiAnalyticsService)
    {
        $this->aiAnalyticsService = $aiAnalyticsService;
    }

    /**
     * @OA\Get(
     *     path="/api/analytics",
     *     summary="Get analytics dashboard data",
     *     description="Retrieve comprehensive analytics data for the dashboard",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Time period for analytics (7d, 30d, 90d, 1y)",
     *         required=false,
     *         @OA\Schema(type="string", default="30d")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Analytics data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="sales", type="object",
     *                 @OA\Property(property="total_revenue", type="number"),
     *                 @OA\Property(property="total_orders", type="integer"),
     *                 @OA\Property(property="average_order_value", type="number"),
     *                 @OA\Property(property="daily_sales", type="array", @OA\Items(type="object"))
     *             ),
     *             @OA\Property(property="books", type="object",
     *                 @OA\Property(property="total_books", type="integer"),
     *                 @OA\Property(property="top_selling", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="low_stock", type="array", @OA\Items(type="object"))
     *             ),
     *             @OA\Property(property="users", type="object",
     *                 @OA\Property(property="total_users", type="integer"),
     *                 @OA\Property(property="new_users", type="integer"),
     *                 @OA\Property(property="active_users", type="integer")
     *             ),
     *             @OA\Property(property="ai_insights", type="object",
     *                 @OA\Property(property="trends", type="string"),
     *                 @OA\Property(property="recommendations", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $period = $request->get('period', '30d');
        $startDate = $this->getStartDate($period);

        // Sales Analytics
        $salesData = $this->getSalesAnalytics($startDate);
        
        // Book Analytics
        $bookData = $this->getBookAnalytics();
        
        // User Analytics
        $userData = $this->getUserAnalytics($startDate);
        
        // AI Insights
        $aiInsights = $this->aiAnalyticsService->generateInsights($salesData, $bookData, $userData);

        return response()->json([
            'sales' => $salesData,
            'books' => $bookData,
            'users' => $userData,
            'ai_insights' => $aiInsights,
            'period' => $period,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get sales analytics data
     */
    private function getSalesAnalytics(Carbon $startDate): array
    {
        // Total revenue and orders
        $totalRevenue = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate)
            ->sum('total_amount');

        $totalOrders = Order::where('created_at', '>=', $startDate)->count();

        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Daily sales data
        $dailySales = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'orders' => $item->orders,
                    'revenue' => (float) $item->revenue
                ];
            });

        // Top selling books
        $topSellingBooks = DB::table('order_items')
            ->join('books', 'order_items.book_id', '=', 'books.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', $startDate)
            ->select(
                'books.id',
                'books.title',
                'books.author',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('books.id', 'books.title', 'books.author')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order_value' => round($averageOrderValue, 2),
            'daily_sales' => $dailySales,
            'top_selling_books' => $topSellingBooks
        ];
    }

    /**
     * Get book analytics data
     */
    private function getBookAnalytics(): array
    {
        $totalBooks = Book::count();

        // Books with most orders
        $popularBooks = Book::withCount(['orderItems as total_orders'])
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'price' => $book->price,
                    'total_orders' => $book->total_orders
                ];
            });

        return [
            'total_books' => $totalBooks,
            'popular_books' => $popularBooks
        ];
    }

    /**
     * Get user analytics data
     */
    private function getUserAnalytics(Carbon $startDate): array
    {
        $totalUsers = User::count();
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        
        // Active users (users with orders in the period)
        $activeUsers = User::whereHas('orders', function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        })->count();

        // User registration trend
        $userRegistrationTrend = User::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as registrations')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'registrations' => $item->registrations
                ];
            });

        return [
            'total_users' => $totalUsers,
            'new_users' => $newUsers,
            'active_users' => $activeUsers,
            'registration_trend' => $userRegistrationTrend
        ];
    }

    /**
     * Get start date based on period
     */
    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30),
        };
    }
} 
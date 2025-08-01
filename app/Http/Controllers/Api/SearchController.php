<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\AiSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Tag(
 *     name="Search",
 *     description="AI-powered semantic search operations"
 * )
 */
class SearchController extends Controller
{
    protected $aiSearchService;

    public function __construct(AiSearchService $aiSearchService)
    {
        $this->aiSearchService = $aiSearchService;
    }

    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Search books semantically",
     *     description="Search books using AI-powered semantic search with Hugging Face embeddings",
     *     tags={"Search"},
     *
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *
     *         @OA\Schema(type="string", example="science fiction space adventure")
     *     ),
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of results to return",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_score",
     *         in="query",
     *         description="Minimum similarity score (0-1)",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float", default=0.3)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="query", type="string"),
     *             @OA\Property(property="total_results", type="integer"),
     *             @OA\Property(property="results", type="array", @OA\Items(
     *                 @OA\Property(property="book", ref="#/components/schemas/Book"),
     *                 @OA\Property(property="score", type="number", format="float"),
     *                 @OA\Property(property="highlights", type="array", @OA\Items(type="string"))
     *             )),
     *             @OA\Property(property="processing_time", type="number", format="float")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid search query"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Search service error"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:500',
            'limit' => 'sometimes|integer|min:1|max:50',
            'min_score' => 'sometimes|numeric|min:0|max:1',
        ]);

        $query = $request->get('q');
        $limit = $request->get('limit', 10);
        $minScore = $request->get('min_score', 0.3);

        // Generate cache key based on search parameters
        $cacheKey = 'search:'.md5($query.$limit.$minScore);

        // Try to get cached results first
        $cachedResults = Cache::get($cacheKey);
        if ($cachedResults) {
            return response()->json($cachedResults);
        }

        $startTime = microtime(true);

        try {
            // Perform semantic search
            $results = $this->aiSearchService->search($query, $limit, $minScore);

            $processingTime = microtime(true) - $startTime;

            $response = [
                'query' => $query,
                'total_results' => count($results),
                'results' => $results,
                'processing_time' => round($processingTime, 3),
                'cached' => false,
            ];

            // Cache results for 1 hour
            Cache::put($cacheKey, $response, 3600);

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search service temporarily unavailable',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/search/suggestions",
     *     summary="Get search suggestions",
     *     description="Get search suggestions based on partial query",
     *     tags={"Search"},
     *
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Partial search query",
     *         required=true,
     *
     *         @OA\Schema(type="string", example="sci")
     *     ),
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of suggestions to return",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Search suggestions retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="query", type="string"),
     *             @OA\Property(property="suggestions", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        $query = $request->get('q');
        $limit = $request->get('limit', 5);

        // Generate suggestions based on book titles and authors
        $suggestions = Book::where('title', 'like', "%{$query}%")
            ->orWhere('author', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit($limit * 2) // Get more to filter
            ->get()
            ->map(function ($book) use ($query) {
                // Prioritize title matches
                if (stripos($book->title, $query) !== false) {
                    return $book->title;
                }
                if (stripos($book->author, $query) !== false) {
                    return $book->author;
                }

                return null;
            })
            ->filter()
            ->unique()
            ->take($limit)
            ->values()
            ->toArray();

        return response()->json([
            'query' => $query,
            'suggestions' => $suggestions,
        ]);
    }
}

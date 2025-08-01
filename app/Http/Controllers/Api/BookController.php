<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Jobs\GenerateAiRecommendations;
use App\Jobs\GenerateBookSummaryJob;
use App\Models\Book;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Tag(
 *     name="Books",
 *     description="Book management endpoints"
 * )
 * @OA\Tag(
 *     name="AI Features",
 *     description="AI-powered recommendations and summaries"
 * )
 */
class BookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/books",
     *     summary="List all books",
     *     description="Get a paginated list of all books",
     *     tags={"Books"},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of books retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="The Great Gatsby"),
     *                 @OA\Property(property="author", type="string", example="F. Scott Fitzgerald"),
     *                 @OA\Property(property="description", type="string", example="A story of the fabulously wealthy Jay Gatsby..."),
     *                 @OA\Property(property="price", type="number", format="float", example=19.99),
     *                 @OA\Property(property="published_year", type="integer", example=1925),
     *                 @OA\Property(property="isbn", type="string", example="978-0743273565"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=5),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="next_page_url", type="string"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="prev_page_url", type="string"),
     *             @OA\Property(property="to", type="integer", example=10),
     *             @OA\Property(property="total", type="integer", example=50)
     *         )
     *     )
     * )
     */
    public function index()
    {
        $books = Book::paginate(10);

        return response()->json($books);
    }

    /**
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a new book",
     *     description="Create a new book (Admin only)",
     *     tags={"Books"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"title","author","description","price","published_year","isbn"},
     *
     *             @OA\Property(property="title", type="string", example="The Great Gatsby", description="Book title"),
     *             @OA\Property(property="author", type="string", example="F. Scott Fitzgerald", description="Book author"),
     *             @OA\Property(property="description", type="string", example="A story of the fabulously wealthy Jay Gatsby...", description="Book description"),
     *             @OA\Property(property="price", type="number", format="float", example=19.99, description="Book price"),
     *             @OA\Property(property="published_year", type="integer", example=1925, description="Publication year"),
     *             @OA\Property(property="isbn", type="string", example="978-0743273565", description="ISBN number")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Book created successfully."),
     *             @OA\Property(property="book", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="The Great Gatsby"),
     *                 @OA\Property(property="author", type="string", example="F. Scott Fitzgerald"),
     *                 @OA\Property(property="description", type="string", example="A story of the fabulously wealthy Jay Gatsby..."),
     *                 @OA\Property(property="price", type="number", format="float", example=19.99),
     *                 @OA\Property(property="published_year", type="integer", example=1925),
     *                 @OA\Property(property="isbn", type="string", example="978-0743273565"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Access denied. Admin privileges required.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="The title field is required.")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string", example="The price must be a number."))
     *             )
     *         )
     *     )
     * )
     */
    public function store(BookRequest $request)
    {
        $book = Book::create($request->validated());

        return response()->json([
            'message' => 'Book created successfully.',
            'book' => $book,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Get a specific book",
     *     description="Retrieve details of a specific book by ID",
     *     tags={"Books"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Book ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Book retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="The Great Gatsby"),
     *                 @OA\Property(property="author", type="string", example="F. Scott Fitzgerald"),
     *                 @OA\Property(property="description", type="string", example="A story of the fabulously wealthy Jay Gatsby..."),
     *                 @OA\Property(property="price", type="number", format="float", example=19.99),
     *                 @OA\Property(property="published_year", type="integer", example=1925),
     *                 @OA\Property(property="isbn", type="string", example="978-0743273565"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Book not found.")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'message' => 'Book not found.',
            ], 404);
        }

        return response()->json([
            'data' => $book,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update a book",
     *     description="Update an existing book (Admin only)",
     *     tags={"Books"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Book ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="title", type="string", example="The Great Gatsby (Updated)", description="Book title"),
     *             @OA\Property(property="author", type="string", example="F. Scott Fitzgerald", description="Book author"),
     *             @OA\Property(property="description", type="string", example="A story of the fabulously wealthy Jay Gatsby...", description="Book description"),
     *             @OA\Property(property="price", type="number", format="float", example=24.99, description="Book price"),
     *             @OA\Property(property="published_year", type="integer", example=1925, description="Publication year"),
     *             @OA\Property(property="isbn", type="string", example="978-0743273565", description="ISBN number")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Book updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Book updated successfully."),
     *             @OA\Property(property="book", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="The Great Gatsby (Updated)"),
     *                 @OA\Property(property="author", type="string", example="F. Scott Fitzgerald"),
     *                 @OA\Property(property="description", type="string", example="A story of the fabulously wealthy Jay Gatsby..."),
     *                 @OA\Property(property="price", type="number", format="float", example=24.99),
     *                 @OA\Property(property="published_year", type="integer", example=1925),
     *                 @OA\Property(property="isbn", type="string", example="978-0743273565"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Access denied. Admin privileges required.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Book not found.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="The title field is required.")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string", example="The price must be a number."))
     *             )
     *         )
     *     )
     * )
     */
    public function update(BookRequest $request, string $id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'message' => 'Book not found.',
            ], 404);
        }

        $book->update($request->validated());

        return response()->json([
            'message' => 'Book updated successfully.',
            'book' => $book,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="Delete a book",
     *     description="Delete an existing book (Admin only)",
     *     tags={"Books"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Book ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Book deleted successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Access denied. Admin privileges required.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Book not found.")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'message' => 'Book not found.',
            ], 404);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/books/{id}/recommendations",
     *     summary="Get AI recommendations for a book",
     *     description="Get AI-powered book recommendations based on the specified book",
     *     tags={"AI Features"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Book ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Recommendations response",
     *
     *         @OA\JsonContent(
     *             oneOf={
     *
     *                 @OA\Schema(
     *
     *                     @OA\Property(property="message", type="string", example="Recommendations retrieved successfully."),
     *                     @OA\Property(property="recommendations", type="array", @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="title", type="string", example="To Kill a Mockingbird"),
     *                         @OA\Property(property="author", type="string", example="Harper Lee"),
     *                         @OA\Property(property="description", type="string", example="A powerful story of racial injustice..."),
     *                         @OA\Property(property="price", type="number", format="float", example=15.99),
     *                         @OA\Property(property="similarity_score", type="number", format="float", example=0.85),
     *                         @OA\Property(property="reason", type="string", example="Similar themes and writing style")
     *                     ))
     *                 ),
     *
     *                 @OA\Schema(
     *
     *                     @OA\Property(property="message", type="string", example="Generating recommendations. Please try again in a few moments."),
     *                     @OA\Property(property="recommendations", type="array", @OA\Items())
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Book not found.")
     *         )
     *     )
     * )
     */
    public function recommendations(string $id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'message' => 'Book not found.',
            ], 404);
        }

        // Check if recommendations are already cached
        $cachedRecommendations = Cache::get("book_recommendations_{$book->id}");

        if ($cachedRecommendations) {
            return response()->json([
                'message' => 'Recommendations retrieved successfully.',
                'recommendations' => $cachedRecommendations,
            ]);
        }

        // Dispatch job to generate recommendations
        GenerateAiRecommendations::dispatch($book);

        return response()->json([
            'message' => 'Generating recommendations. Please try again in a few moments.',
            'recommendations' => [],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/books/{id}/summary",
     *     summary="Get AI summary for a book",
     *     description="Get AI-powered book summary using Hugging Face GPT-2 model",
     *     tags={"AI Features"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Book ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Summary response",
     *
     *         @OA\JsonContent(
     *             oneOf={
     *
     *                 @OA\Schema(
     *
     *                     @OA\Property(property="message", type="string", example="Summary retrieved successfully."),
     *                     @OA\Property(property="summary", type="object",
     *                         @OA\Property(property="status", type="string", example="completed"),
     *                         @OA\Property(property="content", type="string", example="This book explores themes of wealth, love, and the American Dream..."),
     *                         @OA\Property(property="generated_at", type="string", format="date-time", example="2025-07-31T10:30:00.000000Z"),
     *                         @OA\Property(property="word_count", type="integer", example=45),
     *                         @OA\Property(property="reading_time", type="integer", example=1),
     *                         @OA\Property(property="book_id", type="integer", example=1),
     *                         @OA\Property(property="book_title", type="string", example="The Great Gatsby")
     *                     )
     *                 ),
     *
     *                 @OA\Schema(
     *
     *                     @OA\Property(property="message", type="string", example="Generating summary. Please try again in a few moments."),
     *                     @OA\Property(property="summary", type="object",
     *                         @OA\Property(property="status", type="string", example="processing"),
     *                         @OA\Property(property="content", type="string", example=null),
     *                         @OA\Property(property="generated_at", type="string", example=null)
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Book not found.")
     *         )
     *     )
     * )
     */
    public function summary(string $id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'message' => 'Book not found.',
            ], 404);
        }

        // Check if summary is already cached
        $cachedSummary = Cache::get("book_summary_{$book->id}");

        if ($cachedSummary) {
            return response()->json([
                'message' => 'Summary retrieved successfully.',
                'summary' => $cachedSummary,
            ]);
        }

        // Dispatch job to generate summary
        GenerateBookSummaryJob::dispatch($book);

        return response()->json([
            'message' => 'Generating summary. Please try again in a few moments.',
            'summary' => [
                'status' => 'processing',
                'content' => null,
                'generated_at' => null,
            ],
        ]);
    }
}

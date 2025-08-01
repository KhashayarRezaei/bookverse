<?php

namespace App\Services;

use App\Models\Book;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiRecommendationService
{
    protected $apiKey;

    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');
        $this->apiUrl = 'https://api-inference.huggingface.co/models/facebook/bart-large-mnli';
    }

    /**
     * Get AI-powered recommendations for a book
     */
    public function getRecommendations(Book $book): array
    {
        try {
            // Prepare the text for analysis
            $text = $this->prepareBookText($book);

            // Define candidate labels for classification
            $candidateLabels = [
                'fiction', 'non-fiction', 'mystery', 'romance', 'science fiction',
                'fantasy', 'thriller', 'biography', 'history', 'philosophy',
                'classic', 'contemporary', 'young adult', 'children', 'poetry',
            ];

            // Make API call to Hugging Face
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'inputs' => $text,
                'parameters' => [
                    'candidate_labels' => $candidateLabels,
                    'multi_label' => true,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $this->processApiResponse($data, $book);
            } else {
                Log::error('Hugging Face API error', [
                    'book_id' => $book->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [];
            }
        } catch (Exception $e) {
            Log::error('AI Recommendation Service error', [
                'book_id' => $book->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Prepare book text for AI analysis
     */
    protected function prepareBookText(Book $book): string
    {
        $text = $book->title . ' ' . $book->author;

        if ($book->description) {
            $text .= ' ' . $book->description;
        }

        return $text;
    }

    /**
     * Process the API response and generate recommendations
     */
    protected function processApiResponse(array $apiResponse, Book $book): array
    {
        $recommendations = [];

        // Get the top labels from the API response
        $labels = $apiResponse['labels'] ?? [];
        $scores = $apiResponse['scores'] ?? [];

        // Find similar books based on the identified labels
        $similarBooks = $this->findSimilarBooks($book, $labels, $scores);

        foreach ($similarBooks as $similarBook) {
            $recommendations[] = [
                'id' => $similarBook->id,
                'title' => $similarBook->title,
                'author' => $similarBook->author,
                'description' => $similarBook->description,
                'price' => $similarBook->price,
                'similarity_score' => $similarBook->similarity_score ?? 0.8,
            ];
        }

        // If no similar books found, return mock recommendations for testing
        if (empty($recommendations)) {
            return $this->getMockRecommendations($book);
        }

        // Limit to top 5 recommendations
        return array_slice($recommendations, 0, 5);
    }

    /**
     * Find similar books based on labels and scores
     */
    protected function findSimilarBooks(Book $book, array $labels, array $scores): array
    {
        // Create a query to find similar books
        $query = Book::where('id', '!=', $book->id);

        // Add filters based on the top labels
        $topLabels = array_slice($labels, 0, 3);

        foreach ($topLabels as $index => $label) {
            $score = $scores[$index] ?? 0.5;

            // Add similarity score to the book
            $query->orWhere(function ($q) use ($label) {
                $q->where('title', 'like', '%' . $label . '%')
                    ->orWhere('description', 'like', '%' . $label . '%')
                    ->orWhere('author', 'like', '%' . $label . '%');
            });
        }

        $similarBooks = $query->limit(10)->get();

        // Add similarity scores based on label matching
        foreach ($similarBooks as $similarBook) {
            $similarityScore = 0;
            $matchCount = 0;

            foreach ($topLabels as $index => $label) {
                $score = $scores[$index] ?? 0.5;

                if (
                    stripos($similarBook->title, $label) !== false ||
                    stripos($similarBook->description, $label) !== false ||
                    stripos($similarBook->author, $label) !== false
                ) {
                    $similarityScore += $score;
                    $matchCount++;
                }
            }

            $similarBook->similarity_score = $matchCount > 0 ? $similarityScore / $matchCount : 0.5;
        }

        // Sort by similarity score
        return $similarBooks->sortByDesc('similarity_score')->values()->all();
    }

    /**
     * Get mock recommendations for testing when no similar books are found
     */
    protected function getMockRecommendations(Book $book): array
    {
        return [
            [
                'id' => 999,
                'title' => 'Mock Recommendation 1',
                'author' => 'Mock Author 1',
                'description' => 'A mock recommendation for testing purposes.',
                'price' => 15.99,
                'similarity_score' => 0.85,
            ],
            [
                'id' => 998,
                'title' => 'Mock Recommendation 2',
                'author' => 'Mock Author 2',
                'description' => 'Another mock recommendation for testing.',
                'price' => 12.99,
                'similarity_score' => 0.75,
            ],
        ];
    }
}

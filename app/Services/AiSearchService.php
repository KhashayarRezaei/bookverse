<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSearchService
{
    protected $apiUrl = 'https://api-inference.huggingface.co/models/sentence-transformers/all-MiniLM-L6-v2';

    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');
    }

    /**
     * Perform semantic search on books
     */
    public function search(string $query, int $limit = 10, float $minScore = 0.3): array
    {
        try {
            // Get all books for comparison
            $books = Book::all();

            if ($books->isEmpty()) {
                return [];
            }

            // Get query embedding
            $queryEmbedding = $this->getEmbedding($query);

            if (! $queryEmbedding) {
                return $this->fallbackSearch($query, $limit);
            }

            // Get book embeddings and calculate similarities
            $results = [];

            foreach ($books as $book) {
                $bookText = $this->prepareBookText($book);
                $bookEmbedding = $this->getBookEmbedding($book, $bookText);

                if ($bookEmbedding) {
                    $similarity = $this->calculateCosineSimilarity($queryEmbedding, $bookEmbedding);

                    if ($similarity >= $minScore) {
                        $results[] = [
                            'book' => $book,
                            'score' => $similarity,
                            'highlights' => $this->generateHighlights($query, $bookText),
                        ];
                    }
                }
            }

            // Sort by similarity score (highest first)
            usort($results, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // Return limited results
            return array_slice($results, 0, $limit);
        } catch (\Exception $e) {
            Log::error('AI Search error: ' . $e->getMessage());

            return $this->fallbackSearch($query, $limit);
        }
    }

    /**
     * Get embedding for a text using Hugging Face API
     */
    protected function getEmbedding(string $text): ?array
    {
        $cacheKey = 'embedding:' . md5($text);

        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'inputs' => $text,
            ]);

            if ($response->successful()) {
                $embedding = $response->json();

                // Cache the embedding for 24 hours
                Cache::put($cacheKey, $embedding, 86400);

                return $embedding;
            }

            Log::warning('Hugging Face API error: ' . $response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Embedding API error: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get or create embedding for a book
     */
    protected function getBookEmbedding(Book $book, string $bookText): ?array
    {
        $cacheKey = 'book_embedding:' . $book->id;

        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $embedding = $this->getEmbedding($bookText);

        if ($embedding) {
            // Cache for 24 hours
            Cache::put($cacheKey, $embedding, 86400);
        }

        return $embedding;
    }

    /**
     * Prepare book text for embedding
     */
    protected function prepareBookText(Book $book): string
    {
        return implode(' ', [
            $book->title,
            $book->author,
            $book->description,
            $book->published_year,
        ]);
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    protected function calculateCosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Generate highlights for search results
     */
    protected function generateHighlights(string $query, string $text): array
    {
        $highlights = [];
        $queryWords = explode(' ', strtolower($query));
        $textWords = explode(' ', $text);

        foreach ($queryWords as $queryWord) {
            if (strlen($queryWord) < 3) {
                continue;
            }

            foreach ($textWords as $textWord) {
                if (stripos($textWord, $queryWord) !== false) {
                    $highlights[] = $textWord;
                    break;
                }
            }
        }

        return array_unique(array_slice($highlights, 0, 3));
    }

    /**
     * Fallback to basic text search if AI search fails
     */
    protected function fallbackSearch(string $query, int $limit): array
    {
        $books = Book::where('title', 'like', "%{$query}%")
            ->orWhere('author', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit($limit)
            ->get();

        return $books->map(function ($book) {
            return [
                'book' => $book,
                'score' => 0.5, // Default score for fallback
                'highlights' => [],
            ];
        })->toArray();
    }
}

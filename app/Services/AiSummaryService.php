<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSummaryService
{
    private string $apiKey;

    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key') ?? 'test_api_key_123';
        $this->apiUrl = 'https://api-inference.huggingface.co/models/gpt2';
    }

    public function generateSummary(Book $book): array
    {
        try {
            $bookText = $this->prepareBookText($book);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'inputs' => $bookText,
                'parameters' => [
                    'max_length' => 200,
                    'min_length' => 50,
                    'do_sample' => true,
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                ],
            ]);

            if ($response->successful()) {
                $summaryData = $response->json();

                return $this->processApiResponse($summaryData, $book);
            } else {
                Log::error('Hugging Face API error', [
                    'book_id' => $book->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'status' => 'error',
                    'content' => 'Failed to generate summary due to API error.',
                    'error' => $response->body(),
                    'generated_at' => now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('AI Summary Service error', [
                'book_id' => $book->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'content' => 'Failed to generate summary due to service error.',
                'error' => $e->getMessage(),
                'generated_at' => now()->toISOString(),
            ];
        }
    }

    private function prepareBookText(Book $book): string
    {
        $text = "Title: {$book->title}\n";
        $text .= "Author: {$book->author}\n";
        $text .= "Description: {$book->description}\n";
        $text .= 'Generate a concise summary: ';

        // Limit text length to avoid API limits
        if (strlen($text) > 4000) {
            $text = substr($text, 0, 4000).'...';
        }

        return $text;
    }

    private function processApiResponse(array $apiResponse, Book $book): array
    {
        if (empty($apiResponse) || ! isset($apiResponse[0]['generated_text'])) {
            return [
                'status' => 'error',
                'content' => 'No summary generated from API response.',
                'generated_at' => now()->toISOString(),
            ];
        }

        $summaryText = $apiResponse[0]['generated_text'];

        // Clean up the generated text (remove the input prompt)
        $inputText = $this->prepareBookText($book);
        $summaryText = str_replace($inputText, '', $summaryText);
        $summaryText = trim($summaryText);

        // If the summary is too short or empty, provide a fallback
        if (strlen($summaryText) < 20) {
            $summaryText = "This book by {$book->author} explores various themes and provides an engaging narrative.";
        }

        $wordCount = str_word_count($summaryText);
        $readingTime = max(1, ceil($wordCount / 200)); // Average reading speed

        return [
            'status' => 'completed',
            'content' => $summaryText,
            'generated_at' => now()->toISOString(),
            'word_count' => $wordCount,
            'reading_time' => $readingTime,
            'book_id' => $book->id,
            'book_title' => $book->title,
        ];
    }

    public function getMockSummary(Book $book): array
    {
        return [
            'status' => 'completed',
            'content' => "This is a mock summary for '{$book->title}' by {$book->author}. ".
                        'The book explores various themes and provides an engaging narrative '.
                        'that captivates readers from beginning to end.',
            'generated_at' => now()->toISOString(),
            'word_count' => 25,
            'reading_time' => 1,
            'book_id' => $book->id,
            'book_title' => $book->title,
        ];
    }
}

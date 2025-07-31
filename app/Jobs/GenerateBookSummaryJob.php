<?php

namespace App\Jobs;

use App\Models\Book;
use App\Services\AiSummaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateBookSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Book $book;
    public int $timeout = 60;

    public function __construct(Book $book)
    {
        $this->book = $book;
    }

    public function handle(): void
    {
        try {
            $aiService = app(AiSummaryService::class);
            $summary = $aiService->generateSummary($this->book);

            // Store the summary in cache for 24 hours
            Cache::put("book_summary_{$this->book->id}", $summary, 86400);

            if ($summary['status'] === 'completed') {
                Log::info('Book summary generated and cached', [
                    'book_id' => $this->book->id,
                    'word_count' => $summary['word_count'] ?? 0
                ]);
            } else {
                Log::warning('Book summary generation failed', [
                    'book_id' => $this->book->id,
                    'error' => $summary['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('GenerateBookSummaryJob failed', [
                'book_id' => $this->book->id,
                'error' => $e->getMessage()
            ]);

            // Cache error response to prevent repeated failures
            $errorSummary = [
                'status' => 'error',
                'content' => 'Failed to generate summary due to job processing error.',
                'error' => $e->getMessage(),
                'generated_at' => now()->toISOString()
            ];

            Cache::put("book_summary_{$this->book->id}", $errorSummary, 1800); // Cache error for 30 minutes
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateBookSummaryJob failed permanently', [
            'book_id' => $this->book->id,
            'error' => $exception->getMessage()
        ]);

        // Cache error response
        $errorSummary = [
            'status' => 'error',
            'content' => 'Failed to generate summary due to job failure.',
            'error' => $exception->getMessage(),
            'generated_at' => now()->toISOString()
        ];

        Cache::put("book_summary_{$this->book->id}", $errorSummary, 1800);
    }
} 
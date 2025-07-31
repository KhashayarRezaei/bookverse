<?php

namespace App\Jobs;

use App\Models\Book;
use App\Services\AiRecommendationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateAiRecommendations implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $book;
    public $timeout = 60; // 60 seconds timeout

    /**
     * Create a new job instance.
     */
    public function __construct(Book $book)
    {
        $this->book = $book;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $aiService = app(AiRecommendationService::class);

        try {
            // Get recommendations from AI service
            $recommendations = $aiService->getRecommendations($this->book);

            // Store recommendations in cache for 1 hour
            if (!empty($recommendations)) {
                Cache::put(
                    "book_recommendations_{$this->book->id}",
                    $recommendations,
                    3600 // 1 hour
                );

                Log::info('AI recommendations generated and cached', [
                    'book_id' => $this->book->id,
                    'recommendations_count' => count($recommendations)
                ]);
            } else {
                Log::warning('No AI recommendations generated', [
                    'book_id' => $this->book->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate AI recommendations', [
                'book_id' => $this->book->id,
                'error' => $e->getMessage()
            ]);

            // Don't store anything in cache on error
            // This will allow the endpoint to retry later
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AI recommendations job failed', [
            'book_id' => $this->book->id,
            'error' => $exception->getMessage()
        ]);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnalyticsService
{
    protected $apiUrl = 'https://api-inference.huggingface.co/models/gpt2';

    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');
    }

    /**
     * Generate AI insights from analytics data
     */
    public function generateInsights(array $salesData, array $bookData, array $userData): array
    {
        try {
            $context = $this->prepareContext($salesData, $bookData, $userData);
            $prompt = $this->createPrompt($context);

            $insights = $this->generateInsightsFromAI($prompt);

            return [
                'trends' => $insights['trends'] ?? 'No significant trends detected.',
                'recommendations' => $insights['recommendations'] ?? ['Continue monitoring performance'],
                'generated_at' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('AI Analytics error: ' . $e->getMessage());

            return [
                'trends' => $this->generateBasicTrends($salesData, $bookData, $userData),
                'recommendations' => $this->generateBasicRecommendations($salesData, $bookData, $userData),
                'generated_at' => now()->toISOString(),
            ];
        }
    }

    /**
     * Prepare context data for AI analysis
     */
    private function prepareContext(array $salesData, array $bookData, array $userData): array
    {
        return [
            'total_revenue' => $salesData['total_revenue'] ?? 0,
            'total_orders' => $salesData['total_orders'] ?? 0,
            'average_order_value' => $salesData['average_order_value'] ?? 0,
            'total_books' => $bookData['total_books'] ?? 0,
            'total_users' => $userData['total_users'] ?? 0,
            'new_users' => $userData['new_users'] ?? 0,
            'active_users' => $userData['active_users'] ?? 0,
            'top_selling_books' => $salesData['top_selling_books'] ?? [],
            'daily_sales' => $salesData['daily_sales'] ?? [],
        ];
    }

    /**
     * Create prompt for AI analysis
     */
    private function createPrompt(array $context): string
    {
        $topBooks = collect($context['top_selling_books'])->take(3)->map(function ($book) {
            return "{$book->title} by {$book->author}";
        })->join(', ');

        return "Analyze this e-commerce bookstore data and provide insights:

Revenue: \${$context['total_revenue']}
Orders: {$context['total_orders']}
Average Order Value: \${$context['average_order_value']}
Total Books: {$context['total_books']}
Total Users: {$context['total_users']}
New Users: {$context['new_users']}
Active Users: {$context['active_users']}
Top Selling Books: {$topBooks}

Please provide:
1. Key trends and patterns
2. 3-5 actionable recommendations for business growth

Format as JSON with 'trends' and 'recommendations' fields.";
    }

    /**
     * Generate insights using AI
     */
    private function generateInsightsFromAI(string $prompt): array
    {
        $cacheKey = 'ai_insights:' . md5($prompt);

        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'inputs' => $prompt,
                'parameters' => [
                    'max_length' => 500,
                    'temperature' => 0.7,
                    'return_full_text' => false,
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $insights = $this->parseAIResponse($result[0]['generated_text'] ?? '');

                // Cache for 1 hour
                Cache::put($cacheKey, $insights, 3600);

                return $insights;
            }

            throw new \Exception('AI API request failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse AI response
     */
    private function parseAIResponse(string $response): array
    {
        // Try to extract JSON from response
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            try {
                $json = json_decode($matches[0], true);
                if ($json && isset($json['trends']) && isset($json['recommendations'])) {
                    return $json;
                }
            } catch (\Exception $e) {
                // Continue to fallback parsing
            }
        }

        // Fallback parsing
        $trends = $this->extractTrends($response);
        $recommendations = $this->extractRecommendations($response);

        return [
            'trends' => $trends,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Extract trends from AI response
     */
    private function extractTrends(string $response): string
    {
        // Simple pattern matching for trends
        if (preg_match('/(?:trends?|patterns?|insights?)[:\s]+(.+?)(?=\n|recommendations?|$)/i', $response, $matches)) {
            return trim($matches[1]);
        }

        return 'Analysis of current performance metrics shows mixed results.';
    }

    /**
     * Extract recommendations from AI response
     */
    private function extractRecommendations(string $response): array
    {
        $recommendations = [];

        // Look for numbered or bulleted recommendations
        if (preg_match_all('/(?:recommendations?|suggestions?)[:\s]*\n?((?:\d+\.|\*|\-)\s*.+?)(?=\n\d+\.|\n\*|\n\-|\n\n|$)/is', $response, $matches)) {
            foreach ($matches[1] as $match) {
                $clean = trim(preg_replace('/^\d+\.\s*|\*\s*|\-\s*/', '', $match));
                if (! empty($clean)) {
                    $recommendations[] = $clean;
                }
            }
        }

        return $recommendations ?: ['Continue monitoring performance and user engagement'];
    }

    /**
     * Generate basic trends without AI
     */
    private function generateBasicTrends(array $salesData, array $bookData, array $userData): string
    {
        $trends = [];

        if ($salesData['total_revenue'] > 0) {
            $trends[] = "Revenue generation is active with \${$salesData['total_revenue']} in sales";
        }

        if ($userData['new_users'] > 0) {
            $trends[] = "User acquisition is positive with {$userData['new_users']} new users";
        }

        if ($salesData['average_order_value'] > 0) {
            $trends[] = "Average order value is \${$salesData['average_order_value']}";
        }

        return $trends ? implode('. ', $trends) . '.' : 'No significant trends detected in the current data.';
    }

    /**
     * Generate basic recommendations without AI
     */
    private function generateBasicRecommendations(array $salesData, array $bookData, array $userData): array
    {
        $recommendations = [];

        if ($salesData['total_orders'] < 10) {
            $recommendations[] = 'Focus on increasing order volume through marketing campaigns';
        }

        if ($userData['new_users'] < 5) {
            $recommendations[] = 'Implement user acquisition strategies to grow customer base';
        }

        if ($salesData['average_order_value'] < 50) {
            $recommendations[] = 'Consider upselling strategies to increase average order value';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Continue monitoring performance metrics';
            $recommendations[] = 'Maintain current business strategies';
        }

        return $recommendations;
    }
}

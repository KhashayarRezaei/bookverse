<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock external API calls for all tests
        Http::fake([
            'https://api-inference.huggingface.co/*' => Http::response([
                [
                    'generated_text' => 'This is a mock response from the AI service for testing purposes.'
                ]
            ], 200)
        ]);
    }
}

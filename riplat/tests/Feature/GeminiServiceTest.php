<?php

namespace Tests\Feature;

use App\Services\IA\GeminiApiException;
use App\Services\IA\GeminiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    public function test_rate_limit_error_explains_that_the_project_quota_was_reached(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['status' => 'RESOURCE_EXHAUSTED'],
            ], 429),
        ]);

        try {
            app(GeminiService::class)->analyzeIntent('test', []);
            $this->fail('Expected the service to report the rate limit.');
        } catch (GeminiApiException $exception) {
            $this->assertSame(429, $exception->httpStatus);
            $this->assertStringContainsString('cuota', $exception->getMessage());
        }
    }

    public function test_unavailable_model_returns_a_retryable_user_message(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['status' => 'UNAVAILABLE'],
            ], 503),
        ]);

        try {
            app(GeminiService::class)->analyzeIntent('test', []);
            $this->fail('Expected the service to report model unavailability.');
        } catch (GeminiApiException $exception) {
            $this->assertSame(503, $exception->httpStatus);
            $this->assertStringContainsString('temporalmente', $exception->getMessage());
        }
    }

    public function test_missing_api_key_fails_before_sending_a_request(): void
    {
        config(['services.gemini.key' => '']);
        Http::fake();

        try {
            app(GeminiService::class)->analyzeIntent('test', []);
            $this->fail('Expected the service to report missing configuration.');
        } catch (GeminiApiException $exception) {
            $this->assertSame(503, $exception->httpStatus);
            $this->assertStringContainsString('GEMINI_API_KEY', $exception->getMessage());
        }

        Http::assertNothingSent();
    }
}

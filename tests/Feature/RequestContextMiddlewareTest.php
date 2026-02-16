<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RequestContextMiddlewareTest extends TestCase
{
    public function test_request_id_header_is_added_and_present()
    {
        $response = $this->getJson('/api/v1/tags/options');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('X-Request-Id'));
        $this->assertNotEmpty($response->headers->get('X-Request-Id'));
    }

    public function test_uncaught_exception_is_logged_and_response_is_sanitized()
    {
        // Expect the centralized handler to log the unhandled exception
        Log::shouldReceive('error')
            ->once()
            ->with('Unhandled exception', \Mockery::on(function ($ctx) {
                return is_array($ctx) && isset($ctx['exception']);
            }));

        // Define a temporary route that will throw an exception (so it bubbles to handler)
        \Illuminate\Support\Facades\Route::get('/api/v1/test-exception', function () {
            throw new \Exception('sensitive internal detail');
        });

        $response = $this->getJson('/api/v1/test-exception');

        $response->assertStatus(500);
        $json = $response->json();
        $this->assertEquals('An error occurred', $json['message']);
        $this->assertArrayNotHasKey('error', $json);

        // X-Request-Id header should be present
        $this->assertTrue($response->headers->has('X-Request-Id'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the /api/v1/me endpoint returns user data with profile picture
     */
    public function test_me_endpoint_returns_profile_picture(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'status' => 'active',
            'profile_image' => 'test-image.jpg',
        ]);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Make request to /api/v1/me
        $response = $this->getJson('/api/v1/me');

        // Assert successful response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'status',
                        'profile_image',
                        'role',
                        'permissions'
                    ]
                ]);

        // Assert that profile_image is included
        $responseData = $response->json();
        $this->assertArrayHasKey('profile_image', $responseData['user']);
        // The profile_image should be a full URL when image exists
        $this->assertStringStartsWith('http', $responseData['user']['profile_image']);
    }

    /**
     * Test the /api/v1/me endpoint returns empty profile_image when no image is set
     */
    public function test_me_endpoint_returns_empty_profile_image_when_none_set(): void
    {
        // Create a test user without profile image
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'profile_image' => null,
        ]);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Make request to /api/v1/me
        $response = $this->getJson('/api/v1/me');

        // Assert successful response
        $response->assertStatus(200);

        // Assert that profile_image is empty string when no image is set
        $responseData = $response->json();
        $this->assertEquals('', $responseData['user']['profile_image']);
    }
}
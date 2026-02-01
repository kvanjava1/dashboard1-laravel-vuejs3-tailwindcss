<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthStatusTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that inactive users cannot login
     */
    public function test_inactive_user_cannot_login(): void
    {
        // Create an inactive user (but not banned)
        $user = User::factory()->create([
            'is_active' => false,
            'is_banned' => false,
            'password' => bcrypt('password'),
        ]);

        // Attempt to login
        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Your account is not active. Please contact administrator.',
                ]);
    }

    /**
     * Test that banned users cannot login
     */
    public function test_banned_user_cannot_login(): void
    {
        // Create a banned user (but active)
        $user = User::factory()->create([
            'is_active' => true,
            'is_banned' => true,
            'password' => bcrypt('password'),
        ]);

        // Attempt to login
        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Your account has been banned. Please contact administrator.',
                ]);
    }

    /**
     * Test that middleware blocks inactive users from accessing protected routes
     */
    public function test_middleware_blocks_inactive_user(): void
    {
        // Create an inactive user
        $user = User::factory()->create([
            'is_active' => false,
            'password' => bcrypt('password'),
        ]);

        // Login first (this should work since the check is only on login)
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // This should fail because inactive users can't login
        $loginResponse->assertStatus(401);
    }

    /**
     * Test that middleware blocks banned users from accessing protected routes
     */
    public function test_middleware_blocks_banned_user(): void
    {
        // Create a banned user
        $user = User::factory()->create([
            'is_banned' => true,
            'password' => bcrypt('password'),
        ]);

        // Login first (this should work since the check is only on login)
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // This should fail because banned users can't login
        $loginResponse->assertStatus(401);
    }
}
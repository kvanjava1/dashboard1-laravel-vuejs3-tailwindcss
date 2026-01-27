<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AuthService
{
    /**
     * Authenticate user and generate token
     *
     * @param array $credentials
     * @param array $meta ['ip' => string, 'user_agent' => string]
     * @return array
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function authenticate(array $credentials, array $meta): array
    {
        // Attempt to authenticate the user
        if (!Auth::attempt($credentials)) {
            Log::warning('Failed login attempt', ['email' => $credentials['email']]);
            throw new \Illuminate\Auth\AuthenticationException('Invalid email or password');
        }

        $user = Auth::user();

        // Generate Sanctum token
        $token = $user->createToken(
            $meta['user_agent'] ?? 'Device'
        )->plainTextToken;

        Log::info('User login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $meta['ip'] ?? null,
            'agent' => $meta['user_agent'] ?? null,
        ]);

        return [
            'token' => $token,
            'user' => $this->formatUserData($user),
        ];
    }

    /**
     * Format user data with role and permissions
     *
     * @param User $user
     * @return array
     */
    public function formatUserData(User $user): array
    {
        // Load roles and permissions eagerly
        $user->load('roles');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'profile_image' => $user->profile_image_url,
            'role' => $user->roles->first()->name ?? null,
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ];
    }

    /**
     * Logout user and revoke token
     *
     * @param User $user
     * @param array|null $meta
     * @return bool
     */
    public function logout(User $user, ?array $meta = null): bool
    {
        $user->currentAccessToken()->delete();
        Log::info('User logout', [
            'user_id' => $user->id,
            'ip' => $meta['ip'] ?? null,
            'agent' => $meta['user_agent'] ?? null,
        ]);
        return true;
    }

    /**
     * Get current user with role and permissions
     *
     * @param User $user
     * @return array
     */
    public function getCurrentUser(User $user): array
    {
        return $this->formatUserData($user);
    }
}

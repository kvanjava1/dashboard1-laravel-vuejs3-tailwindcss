<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Authenticate user and generate token
     */
    public function authenticate(array $credentials): array
    {
        // Attempt to authenticate the user
        if (!Auth::attempt($credentials)) {
            Log::warning('Failed login attempt', ['email' => $credentials['email']]);
            throw new \Exception('Invalid email or password', 401);
        }

        $user = Auth::user();
        
        // Generate Sanctum token
        $token = $user->createToken(
            request()->userAgent() ?? 'Device'
        )->plainTextToken;

        Log::info('User login successful', ['user_id' => $user->id, 'email' => $user->email]);

        return [
            'token' => $token,
            'user' => $this->formatUserData($user),
        ];
    }

    /**
     * Format user data with role and permissions
     */
    private function formatUserData(User $user): array
    {
        // Load roles and permissions eagerly
        $user->load('roles');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()->name ?? null,
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ];
    }

    /**
     * Logout user and revoke token
     */
    public function logout(User $user): bool
    {
        $user->currentAccessToken()->delete();
        Log::info('User logout', ['user_id' => $user->id]);
        return true;
    }

    /**
     * Get current user with role and permissions
     */
    public function getCurrentUser(User $user): array
    {
        return $this->formatUserData($user);
    }
}

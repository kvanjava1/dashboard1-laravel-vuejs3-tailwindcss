<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\UserService;

class AuthService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
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

        // Check if user account is active
        if (!$user->is_active) {
            Log::warning('Login attempt by inactive user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $meta['ip'] ?? null,
            ]);
            throw new \Illuminate\Auth\AuthenticationException('Your account is not active. Please contact administrator.');
        }

        // Check if user is banned
        if ($user->is_banned) {
            Log::warning('Login attempt by banned user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $meta['ip'] ?? null,
            ]);
            throw new \Illuminate\Auth\AuthenticationException('Your account has been banned. Please contact administrator.');
        }

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
            'user' => $this->userService->formatUserData($user),
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
        return $this->userService->formatUserData($user);
    }
}

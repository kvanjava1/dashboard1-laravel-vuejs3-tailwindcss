<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * User login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->authenticate(
                $request->validated(),
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => $request->user()?->id ?? null,
                ]
            );

            return response()->json([
                'message' => 'Login successful',
                'token' => $result['token'],
                'user' => $result['user'],
            ], 200);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'message' => $e->getMessage() ?? 'Authentication failed',
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage() ?? 'Authentication failed',
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * User logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout(
                $request->user(),
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => $request->user()?->id ?? null,
                ]
            );

            return response()->json([
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
            ], 500);
        }
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser($request->user());

            return response()->json([
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user',
            ], 500);
        }
    }
}

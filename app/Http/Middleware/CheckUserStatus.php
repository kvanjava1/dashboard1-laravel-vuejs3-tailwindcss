<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user account is active
            if (!$user->is_active) {
                // Delete current access token if available
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                return response()->json([
                    'message' => 'Your account has been deactivated. Please contact administrator.',
                ], 401);
            }

            // Check if user is banned
            if ($user->is_banned) {
                // Delete current access token if available
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                return response()->json([
                    'message' => 'Your account has been banned. Please contact administrator.',
                ], 401);
            }
        }

        return $next($request);
    }
}

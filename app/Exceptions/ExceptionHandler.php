<?php

namespace App\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class ExceptionHandler
{
    /**
     * Configure exception handling for the application.
     */
    public static function configure(Exceptions $exceptions): void
    {
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Method not allowed',
                    'error' => 'The ' . $request->method() . ' method is not supported for this route.',
                ], 405);
            }
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                // Use the exception message when it's a controlled/auth-related message
                $clientMessage = $e->getMessage() ?: 'Unauthenticated';

                return response()->json([
                    'message' => $clientMessage,
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Forbidden',
                    'error' => 'You do not have permission to access this resource.',
                ], 403);
            }
        });

        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found',
                    'error' => 'The requested resource could not be found.',
                ], 404);
            }
        });

        // Handle HTTP 429 - Throttling / Too Many Requests
        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if ($request->is('api/*')) {
                $retryAfter = null;
                if (method_exists($e, 'getHeaders')) {
                    $headers = $e->getHeaders();
                    $retryAfter = $headers['Retry-After'] ?? $headers['retry-after'] ?? null;
                }

                $payload = [
                    'message' => 'Too many requests',
                    'error' => 'Rate limit exceeded. Please try again later.',
                ];

                if ($retryAfter !== null) {
                    $payload['retry_after'] = is_numeric($retryAfter) ? (int) $retryAfter : $retryAfter;
                }

                $headers = $retryAfter !== null ? ['Retry-After' => (string) $retryAfter] : [];

                return response()->json($payload, 429, $headers);
            }
        });

        // Also handle Symfony's TooManyRequestsHttpException if thrown directly
        $exceptions->render(function (TooManyRequestsHttpException $e, $request) {
            if ($request->is('api/*')) {
                $retryAfter = null;
                if (method_exists($e, 'getHeaders')) {
                    $headers = $e->getHeaders();
                    $retryAfter = $headers['Retry-After'] ?? $headers['retry-after'] ?? null;
                }

                $payload = [
                    'message' => 'Too many requests',
                    'error' => 'Rate limit exceeded. Please try again later.',
                ];

                if ($retryAfter !== null) {
                    $payload['retry_after'] = is_numeric($retryAfter) ? (int) $retryAfter : $retryAfter;
                }

                $headers = $retryAfter !== null ? ['Retry-After' => (string) $retryAfter] : [];

                return response()->json($payload, 429, $headers);
            }
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $statusCode = $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500;

                // Centralized logging for uncaught exceptions (include request context)
                Log::error('Unhandled exception', [
                    'exception' => $e,
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->id ?? null,
                    'request_id' => $request->header('X-Request-Id') ?? null,
                ]);

                return response()->json([
                    'message' => 'An error occurred'
                ], $statusCode);
            }
        });
    }
}
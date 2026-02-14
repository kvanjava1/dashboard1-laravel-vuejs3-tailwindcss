<?php

namespace App\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;

class ExceptionHandler
{
    /**
     * Configure exception handling for the application.
     */
    public static function configure(Exceptions $exceptions): void
    {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Method not allowed',
                    'error' => 'The ' . $request->method() . ' method is not supported for this route.',
                ], 405);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'Authentication is required to access this resource.',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Forbidden',
                    'error' => 'You do not have permission to access this resource.',
                ], 403);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found',
                    'error' => 'The requested resource could not be found.',
                ], 404);
            }
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $statusCode = $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500;

                return response()->json([
                    'message' => 'An error occurred',
                    'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
                ], $statusCode);
            }
        });
    }
}
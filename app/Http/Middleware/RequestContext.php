<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RequestContext
{
    /**
     * Add request context to logger and response (X-Request-Id).
     */
    public function handle(Request $request, Closure $next)
    {
        $requestId = $request->header('X-Request-Id') ?: (string) Str::uuid();

        // Attach structured context to all subsequent Log calls
        Log::withContext([
            'request_id' => $requestId,
            'user_id' => $request->user()?->id ?? null,
            'ip' => $request->ip(),
            'method' => $request->method(),
            'path' => $request->path(),
            'route' => optional($request->route())->getName() ?? null,
            'env' => app()->environment(),
        ]);

        $response = $next($request);

        // Echo request id back to the client for easier traceability
        if (method_exists($response, 'header')) {
            $response->header('X-Request-Id', $requestId);
        }

        return $response;
    }
}

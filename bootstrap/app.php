<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register Spatie Permission Middleware
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.user.status' => \App\Http\Middleware\CheckUserStatus::class,
            // Request context middleware (adds request_id/user_id/ip to Log context)
            'request.context' => \App\Http\Middleware\RequestContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        \App\Exceptions\ExceptionHandler::configure($exceptions);
    })->create();

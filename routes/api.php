<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

Route::prefix('v1')->group(function () {
    // Public auth routes with rate limiting
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,15')
        ->name('login');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        // User management routes
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->where('user', '[0-9]+');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->where('user', '[0-9]+');
        Route::patch('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->where('user', '[0-9]+');

        // Role management routes
        Route::get('roles/options', [RoleController::class, 'options'])->name('roles.options');
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show')->where('role', '[0-9]+');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->where('role', '[0-9]+');
        Route::patch('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->where('role', '[0-9]+');

        // Permission routes
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::get('permissions/grouped', [PermissionController::class, 'grouped']);
    });
});

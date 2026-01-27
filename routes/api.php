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
        Route::get('users', [UserController::class, 'index'])->middleware(['permission:user_management.view'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->middleware(['permission:user_management.add'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->middleware(['permission:user_management.view'])->name('users.show')->where('user', '[0-9]+');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware(['permission:user_management.edit'])->name('users.update')->where('user', '[0-9]+');
        Route::patch('users/{user}', action: [UserController::class, 'update'])->middleware(['permission:user_management.edit']);
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware(['permission:user_management.delete'])->name('users.destroy')->where('user', '[0-9]+');

        // Role management routes
        Route::get('roles/options', [RoleController::class, 'options'])->middleware(['permission:role_management.view'])->name('roles.options');
        Route::get('roles', [RoleController::class, 'index'])->middleware(['permission:role_management.view'])->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->middleware(['permission:role_management.add'])->name('roles.store');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware(['permission:role_management.view'])->name('roles.show')->where('role', '[0-9]+');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware(['permission:role_management.edit'])->name('roles.update')->where('role', '[0-9]+');
        Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware(['permission:role_management.edit']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware(['permission:role_management.delete'])->name('roles.destroy')->where('role', '[0-9]+');

        // Permission routes
        Route::get('permissions', [PermissionController::class, 'index'])->middleware(['permission:permission.view']);
        Route::get('permissions/grouped', [PermissionController::class, 'grouped'])->middleware(['permission:permission.view']);

        // Example management routes
        Route::get('examples', [\App\Http\Controllers\Api\ExampleManagementController::class, 'index'])->middleware(['permission:example_management.view'])->name('examples.index');
        Route::post('examples', [\App\Http\Controllers\Api\ExampleManagementController::class, 'store'])->middleware(['permission:example_management.add'])->name('examples.store');
        Route::get('examples/{exampleManagement}', [\App\Http\Controllers\Api\ExampleManagementController::class, 'show'])->middleware(['permission:example_management.view'])->name('examples.show')->where('exampleManagement', '[0-9]+');
        Route::put('examples/{exampleManagement}', [\App\Http\Controllers\Api\ExampleManagementController::class, 'update'])->middleware(['permission:example_management.edit'])->name('examples.update')->where('exampleManagement', '[0-9]+');
        Route::patch('examples/{exampleManagement}', [\App\Http\Controllers\Api\ExampleManagementController::class, 'update'])->middleware(['permission:example_management.edit']);
        Route::delete('examples/{exampleManagement}', [\App\Http\Controllers\Api\ExampleManagementController::class, 'destroy'])->middleware(['permission:example_management.delete'])->name('examples.destroy')->where('exampleManagement', '[0-9]+');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Managements\UserController;
use App\Http\Controllers\Api\Managements\RoleController;
use App\Http\Controllers\Api\Managements\PermissionController;
use App\Http\Controllers\Api\Managements\CategoryController;

Route::prefix('v1')->group(function () {
    // Public auth routes with rate limiting
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,15')
        ->name('login');

    // Protected routes
    Route::middleware(['auth:sanctum', 'check.user.status'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        // User management routes
        Route::get('users', [UserController::class, 'index'])->middleware(['permission:user_management.view'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->middleware(['permission:user_management.add'])->name('users.store');
        Route::post('users/clear-cache', [UserController::class, 'clearCache'])->middleware(['permission:user_management.edit'])->name('users.clear-cache');
        Route::get('users/{user}', [UserController::class, 'show'])->middleware(['permission:user_management.view'])->name('users.show')->where('user', '[0-9]+');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware(['permission:user_management.edit'])->name('users.update')->where('user', '[0-9]+');
        Route::patch('users/{user}', action: [UserController::class, 'update'])->middleware(['permission:user_management.edit']);
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware(['permission:user_management.delete'])->name('users.destroy')->where('user', '[0-9]+');

        // User ban management routes
        Route::post('users/{user}/ban', [UserController::class, 'ban'])->middleware(['permission:user_management.ban'])->name('users.ban')->where('user', '[0-9]+');
        Route::post('users/{user}/unban', [UserController::class, 'unban'])->middleware(['permission:user_management.unban'])->name('users.unban')->where('user', '[0-9]+');
        Route::get('users/{user}/ban-history', [UserController::class, 'banHistory'])->middleware(['permission:user_management.view'])->name('users.ban-history')->where('user', '[0-9]+');

        // Role management routes
        Route::get('roles/options', [RoleController::class, 'options'])->middleware(['permission:role_management.view'])->name('roles.options');
        Route::get('roles', [RoleController::class, 'index'])->middleware(['permission:role_management.view'])->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->middleware(['permission:role_management.add'])->name('roles.store');
        Route::post('roles/clear-cache', [RoleController::class, 'clearCache'])->middleware(['permission:role_management.edit'])->name('roles.clear-cache');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware(['permission:role_management.view'])->name('roles.show')->where('role', '[0-9]+');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware(['permission:role_management.edit'])->name('roles.update')->where('role', '[0-9]+');
        Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware(['permission:role_management.edit']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware(['permission:role_management.delete'])->name('roles.destroy')->where('role', '[0-9]+');

        // Permission routes
        Route::get('permissions', [PermissionController::class, 'index'])->middleware(['permission:role_management.view']);
        Route::get('permissions/grouped', [PermissionController::class, 'grouped'])->middleware(['permission:role_management.view']);

        // Category management routes
        Route::get('categories/options', [CategoryController::class, 'options'])->middleware('permission:category_management.view')->name('categories.options');
        Route::get('categories', [CategoryController::class, 'index'])->middleware('permission:category_management.view')->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->middleware('permission:category_management.add')->name('categories.store');
        Route::post('categories/clear-cache', [CategoryController::class, 'clearCache'])->middleware('permission:category_management.edit')->name('categories.clear-cache');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->middleware('permission:category_management.view')->name('categories.show')->where('category', '[0-9]+');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('permission:category_management.edit')->name('categories.update')->where('category', '[0-9]+');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:category_management.delete')->name('categories.destroy')->where('category', '[0-9]+');

        // Gallery management routes
        Route::get('galleries', [\App\Http\Controllers\Api\Managements\GalleryController::class, 'index'])->middleware('permission:gallery_management.view')->name('galleries.index');
        Route::post('galleries', [\App\Http\Controllers\Api\Managements\GalleryController::class, 'store'])->middleware(['permission:gallery_management.add'])->name('galleries.store');
        Route::get('galleries/{gallery}', [\App\Http\Controllers\Api\Managements\GalleryController::class, 'show'])->middleware('permission:gallery_management.view')->name('galleries.show')->where('gallery', '[0-9]+');
        Route::put('galleries/{gallery}', [\App\Http\Controllers\Api\Managements\GalleryController::class, 'update'])->middleware('permission:gallery_management.edit')->name('galleries.update')->where('gallery', '[0-9]+');
        Route::delete('galleries/{gallery}', [\App\Http\Controllers\Api\Managements\GalleryController::class, 'destroy'])->middleware('permission:gallery_management.delete')->name('galleries.destroy')->where('gallery', '[0-9]+');

        // Tag options for autocomplete (authenticated users)
        Route::get('tags/options', [\App\Http\Controllers\Api\Managements\TagController::class, 'options'])->name('tags.options');

    });
});

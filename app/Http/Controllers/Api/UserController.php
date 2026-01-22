<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            // Assign role using Spatie Permission
            $user->assignRole($validated['role']);

            // Log the creation
            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $validated['role'],
                'created_by' => $request->user()->id ?? 'system'
            ]);

            // Return success response
            return response()->json([
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $validated['role'],
                    'status' => $validated['status'],
                    'created_at' => $user->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
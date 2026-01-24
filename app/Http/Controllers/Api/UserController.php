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
                'name' => $request->input('name'),
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                'password' => bcrypt($validated['password']),
                'bio' => $validated['bio'] ?? null,
            ]);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('avatars', 'public');
                $user->update(['profile_image' => $imagePath]);
            }

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
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $user->email,
                    'phone' => $validated['phone'] ?? null,
                    'profile_image' => $user->profile_image_url,
                    'bio' => $validated['bio'] ?? null,
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
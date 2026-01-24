<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);
            
            $filters = [
                'search' => $request->input('search'),
                'role' => $request->input('role'),
                'status' => $request->input('status'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            $result = $this->userService->getFilteredPaginatedUsers($perPage, $page, $filters);

            return response()->json([
                'message' => 'Users retrieved successfully',
                'data' => $result['users'],
                'meta' => [
                    'total' => $result['total'],
                    'total_pages' => $result['total_pages'],
                    'current_page' => $result['current_page'],
                    'per_page' => $result['per_page'],
                    'from' => $result['from'],
                    'to' => $result['to'],
                ],
                'filters' => [
                    'available_roles' => $result['available_roles'],
                    'status_options' => $result['status_options'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve users', [
                'error' => $e->getMessage(),
                'filters' => $request->all(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $userData = $this->userService->getUserById($id);

            return response()->json([
                'message' => 'User retrieved successfully',
                'user' => $userData,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user', [
                'error' => $e->getMessage(),
                'user_id' => $id,
            ]);

            return response()->json([
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            // Add who is updating
            $validated['updated_by'] = $request->user()->id;

            $userData = $this->userService->updateUser($id, $validated);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $userData,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'updated_by' => $request->user()->id ?? null
            ]);

            $statusCode = $e->getMessage() === 'Super admin users cannot be edited' ? 403 : 500;

            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $deletedBy = $request->user()->id;

            $this->userService->deleteUser($id, $deletedBy);

            return response()->json([
                'message' => 'User deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'deleted_by' => $request->user()->id ?? null
            ]);

            $statusCode = 500;
            if ($e->getMessage() === 'Cannot delete super admin users') {
                $statusCode = 403;
            } elseif ($e->getMessage() === 'Cannot delete your own account') {
                $statusCode = 403;
            }

            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
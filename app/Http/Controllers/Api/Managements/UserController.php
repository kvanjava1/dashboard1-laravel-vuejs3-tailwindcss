<?php

namespace App\Http\Controllers\Api\Managements;

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
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'username' => $request->input('username'),
                'location' => $request->input('location'),
                'role' => $request->input('role'),
                'status' => $request->input('status'),
                'is_banned' => $request->input('is_banned'),
                'date_of_birth_from' => $request->input('date_of_birth_from'),
                'date_of_birth_to' => $request->input('date_of_birth_to'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'timezone' => $request->input('timezone'),
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
                'exception' => $e,
                'filters' => $request->all(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json(['message' => 'Failed to retrieve users'], 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Add created_by information
            $validated['created_by'] = $request->user()->id ?? 'system';

            // Include the uploaded file in the data if present
            if ($request->hasFile('profile_image')) {
                $validated['profile_image'] = $request->file('profile_image');
            }

            $userData = $this->userService->createUser($validated);

            // Determine the appropriate message based on whether user was restored or created
            $message = isset($userData['restored']) && $userData['restored']
                ? 'User account restored and updated successfully'
                : 'User created successfully';

            // Return success response
            return response()->json([
                'message' => $message,
                'user' => $userData,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'exception' => $e,
                'email' => $request->input('email'),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json(['message' => 'Failed to create user'], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            $userData = $this->userService->formatUserData($user);

            return response()->json([
                'message' => 'User retrieved successfully',
                'user' => $userData,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user', [
                'exception' => $e,
                'user_id' => $id,
                'requested_by' => request()->user()->id ?? null
            ]);

            return response()->json(['message' => 'Failed to retrieve user'], 500);
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
                'exception' => $e,
                'user_id' => $id,
                'updated_by' => $request->user()->id ?? null
            ]);

            $statusCode = $e->getCode() === 403 ? 403 : 500;

            return response()->json(['message' => 'Failed to update user'], $statusCode);
        }
    }

    /**
     * Ban a user.
     */
    public function ban(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:1000',
                'is_forever' => 'boolean',
                'banned_until' => 'nullable|date|after:now',
            ]);

            $performedBy = $request->user()->id;

            $user = $this->userService->banUser($id, $validated, $performedBy);

            return response()->json([
                'message' => 'User banned successfully',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to ban user', [
                'exception' => $e,
                'user_id' => $id,
                'performed_by' => $request->user()->id ?? null
            ]);

            $statusCode = $e->getCode() === 403 ? 403 : 500;

            return response()->json(['message' => 'Failed to ban user'], $statusCode);
        }
    }

    /**
     * Unban a user.
     */
    public function unban(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'nullable|string|max:1000',
            ]);

            $performedBy = $request->user()->id;
            $reason = $validated['reason'] ?? null;

            $user = $this->userService->unbanUser($id, $reason, $performedBy);

            return response()->json([
                'message' => 'User unbanned successfully',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to unban user', [
                'exception' => $e,
                'user_id' => $id,
                'performed_by' => $request->user()->id ?? null
            ]);

            return response()->json(['message' => 'Failed to unban user'], 500);
        }
    }

    /**
     * Get user's ban history.
     */
    public function banHistory(int $id): JsonResponse
    {
        try {
            $history = $this->userService->getUserBanHistory($id);

            return response()->json([
                'message' => 'Ban history retrieved successfully',
                'data' => $history,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user ban history', [
                'exception' => $e,
                'user_id' => $id,
                'requested_by' => request()->user()->id ?? null
            ]);

            return response()->json(['message' => 'Failed to get user ban history'], 500);
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
                'exception' => $e,
                'user_id' => $id,
                'deleted_by' => $request->user()->id ?? null
            ]);

            $statusCode = $e->getCode() === 403 ? 403 : 500;

            return response()->json(['message' => 'Failed to delete user'], $statusCode);
        }
    }

    /**
     * Clear user cache manually.
     */
    public function clearCache(): JsonResponse
    {
        $this->userService->clearCache();
        return response()->json(['message' => 'User cache cleared successfully']);
    }
}
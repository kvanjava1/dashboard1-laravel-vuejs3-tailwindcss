<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of roles.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $roles = $this->roleService->getAllRoles();

            return response()->json([
                'message' => 'Roles retrieved successfully',
                'roles' => $roles,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'permissions' => 'sometimes|array',
                'permissions.*' => 'string|exists:permissions,name',
                'guard_name' => 'sometimes|string|max:255',
            ]);

            $role = $this->roleService->createRole($validated);

            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified role.
     */
    public function show(int $roleId): JsonResponse
    {
        try {
            $role = $this->roleService->getRoleById($roleId);

            return response()->json([
                'message' => 'Role retrieved successfully',
                'role' => $role,
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;

            return response()->json([
                'message' => 'Failed to retrieve role',
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, int $roleId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255|unique:roles,name,' . $roleId,
                'permissions' => 'sometimes|array',
                'permissions.*' => 'string|exists:permissions,name',
                'guard_name' => 'sometimes|string|max:255',
            ]);

            $role = $this->roleService->updateRole($roleId, $validated);

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $role,
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;

            return response()->json([
                'message' => 'Failed to update role',
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(int $roleId): JsonResponse
    {
        try {
            $this->roleService->deleteRole($roleId);

            return response()->json([
                'message' => 'Role deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? 403 : ($e->getCode() === 404 ? 404 : 500);

            return response()->json([
                'message' => 'Failed to delete role',
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
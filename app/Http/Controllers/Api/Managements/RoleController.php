<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use Spatie\Permission\Models\Role;

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
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            // Validate pagination parameters
            $perPage = max(1, min((int)$perPage, 100)); // Max 100 per page
            $page = max(1, (int)$page);

            // Get filter parameters
            $filters = [
                'search' => $request->get('search'),
                'permissions' => $request->get('permissions', default: []),
            ];

            $result = $this->roleService->getFilteredPaginatedRoles($perPage, $page, $filters);

            return response()->json([
                'message' => 'Roles retrieved successfully',
                ...$result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get role options for dropdowns (lightweight - id, name, display_name only)
     */
    public function options(): JsonResponse
    {
        try {
            $roles = $this->roleService->getRoleOptions();
            
            return response()->json([
                'message' => 'Role options retrieved successfully',
                'roles' => $roles,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve role options',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->createRole($request->validated());

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
    public function update(UpdateRoleRequest $request, int $roleId): JsonResponse
    {
        try {
            $role = $this->roleService->updateRole($roleId, $request->validated());

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
            // Prevent deletion of super admin role
            $role = $this->roleService->findRole($roleId);
            if ($role && $role->name === 'super_admin') {
                return response()->json([
                    'message' => 'Cannot delete super admin role',
                ], 403);
            }

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
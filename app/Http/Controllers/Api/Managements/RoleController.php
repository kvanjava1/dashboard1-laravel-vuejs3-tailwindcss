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
    }

    /**
     * Get role options for dropdowns (lightweight - id, name, display_name only)
     */
    public function options(): JsonResponse
    {
        $roles = $this->roleService->getRoleOptions();
        
        return response()->json([
            'message' => 'Role options retrieved successfully',
            'roles' => $roles,
        ], 200);
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->createRole($request->validated());

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role,
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(int $roleId): JsonResponse
    {
        $role = $this->roleService->getRoleById($roleId);

        return response()->json([
            'message' => 'Role retrieved successfully',
            'role' => $role,
        ], 200);
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, int $roleId): JsonResponse
    {
        $role = $this->roleService->updateRole($roleId, $request->validated());

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role,
        ], 200);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(int $roleId): JsonResponse
    {
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
    }

    /**
     * Clear role cache manually.
     */
    public function clearCache(): JsonResponse
    {
        $this->roleService->clearCache();
        return response()->json(['message' => 'Role cache cleared successfully']);
    }
}
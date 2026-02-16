<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display a listing of permissions.
     */
    public function index(): JsonResponse
    {
        try {
            $permissions = $this->permissionService->getAllPermissions();

            return response()->json([
                'message' => 'Permissions retrieved successfully',
                'permissions' => $permissions,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve permissions'], 500);
        }
    }

    /**
     * Display permissions grouped by category.
     */
    public function grouped(): JsonResponse
    {
        try {
            $groupedPermissions = $this->permissionService->getPermissionsGrouped();

            return response()->json([
                'message' => 'Grouped permissions retrieved successfully',
                'permissions' => $groupedPermissions,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve grouped permissions'], 500);
        }
    }
}
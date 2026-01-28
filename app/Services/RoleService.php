<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use App\Services\ProtectionService;
use App\Services\PermissionService;

class RoleService
{
    protected PermissionService $permissionService;
    protected ProtectionService $protectionService;

    public function __construct()
    {
        $this->permissionService = app(PermissionService::class);
        $this->protectionService = app(ProtectionService::class);
    }

    /**
     * Get all roles with their permissions and user counts
     */
    public function getAllRoles(): array
    {
        try {
            // Use a single query with left join to count users efficiently (avoiding N+1)
            $roles = Role::with('permissions')
                ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
                ->select('roles.*')
                ->selectRaw('COUNT(DISTINCT users.id) as users_count')
                ->groupBy('roles.id')
                ->get();

            $formattedRoles = $roles->map(function ($role) {
                return $this->formatRoleData($role);
            });

            Log::info('Roles retrieved successfully', ['count' => $roles->count()]);

            return $formattedRoles->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve roles', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get filtered and paginated roles with their permissions and user counts
     */
    public function getFilteredPaginatedRoles(int $perPage = 15, int $page = 1, array $filters = []): array
    {
        try {
            // Start building the query
            $query = Role::with('permissions')
                ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
                ->select('roles.*')
                ->selectRaw('COUNT(DISTINCT users.id) as users_count')
                ->groupBy('roles.id');

            // Apply search filter (role name)
            if (!empty($filters['search'])) {
                $query->where('roles.name', 'LIKE', '%' . $filters['search'] . '%');
            }

            // Apply permissions filter (roles must have ALL selected permissions)
            if (!empty($filters['permissions']) && is_array($filters['permissions'])) {
                foreach ($filters['permissions'] as $permission) {
                    $query->whereHas('permissions', function ($q) use ($permission) {
                        $q->where('name', $permission);
                    });
                }
            }

            $roles = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedRoles = $roles->getCollection()->map(function ($role) {
                return $this->formatRoleData($role);
            });

            // Get all available permissions grouped for the filter dropdown
            $availablePermissions = $this->permissionService->getPermissionsGrouped();

            Log::info('Filtered paginated roles retrieved successfully', [
                'page' => $page,
                'per_page' => $perPage,
                'filters' => $filters,
                'total' => $roles->total(),
                'count' => $formattedRoles->count()
            ]);

            return [
                'roles' => $formattedRoles->toArray(),
                'total' => $roles->total(),
                'total_pages' => $roles->lastPage(),
                'current_page' => $roles->currentPage(),
                'per_page' => $roles->perPage(),
                'from' => $roles->firstItem(),
                'to' => $roles->lastItem(),
                'available_permissions' => $availablePermissions
            ];
        } catch (\Exception $e) {
            Log::error('Failed to retrieve filtered paginated roles', [
                'page' => $page,
                'per_page' => $perPage,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get a specific role by ID
     */
    public function getRoleById(int $roleId): array
    {
        try {
            $role = Role::with('permissions')
                ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
                ->select('roles.*')
                ->selectRaw('COUNT(DISTINCT users.id) as users_count')
                ->where('roles.id', $roleId)
                ->groupBy('roles.id')
                ->firstOrFail();

            $roleData = $this->formatRoleData($role);

            Log::info('Role retrieved successfully', ['role_id' => $roleId, 'role_name' => $role->name]);

            return $roleData;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve role', [
                'role_id' => $roleId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a new role with permissions
     */
    public function createRole(array $data): array
    {
        try {
            // Create the role
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web'
            ]);

            // Assign permissions if provided
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            // Reload with permissions
            $role->load('permissions');

            Log::info('Role created successfully', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions_count' => count($data['permissions'] ?? [])
            ]);

            return $this->formatRoleData($role);
        } catch (\Exception $e) {
            Log::error('Failed to create role', [
                'role_name' => $data['name'] ?? null,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing role
     */
    public function updateRole(int $roleId, array $data): array
    {
        try {
            $role = Role::findOrFail($roleId);

            // Check if role is protected from modification
            if ($this->protectionService->isRoleProtectedFromModification($role)) {
                $reason = $this->protectionService->getRoleProtectionReason($role);
                $this->protectionService->throwProtectionException(
                    'Cannot modify protected role',
                    $reason
                );
            }

            // Update basic info
            $role->update([
                'name' => $data['name'] ?? $role->name,
                'guard_name' => $data['guard_name'] ?? $role->guard_name
            ]);

            // Update permissions if provided
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            // Reload with permissions
            $role->load('permissions');

            Log::info('Role updated successfully', [
                'role_id' => $roleId,
                'role_name' => $role->name
            ]);

            return $this->formatRoleData($role);
        } catch (\Exception $e) {
            Log::error('Failed to update role', [
                'role_id' => $roleId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a role
     */
    public function deleteRole(int $roleId): bool
    {
        try {
            $role = Role::findOrFail($roleId);

            // Check if role is protected from deletion
            if ($this->protectionService->isRoleProtectedFromDeletion($role)) {
                $reason = $this->protectionService->getRoleProtectionReason($role);
                $this->protectionService->throwProtectionException(
                    'Cannot delete protected role',
                    $reason
                );
            }

            // Check if role has assigned users
            $userCount = $role->users()->count();
            if ($userCount > 0) {
                throw new \Exception("Cannot delete role. It has {$userCount} assigned users.", 409);
            }

            $roleName = $role->name;
            $role->delete();

            Log::info('Role deleted successfully', [
                'role_id' => $roleId,
                'role_name' => $roleName
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete role', [
                'role_id' => $roleId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get role options for dropdown (lightweight - id, name, display_name only)
     */
    public function getRoleOptions(): array
    {
        try {
            $roles = Role::select(['id', 'name'])->orderBy('name')->get();
            
            $formattedRoles = $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $this->getDisplayName($role->name),
                ];
            });
            
            Log::info('Role options retrieved successfully', ['count' => $roles->count()]);
            
            return $formattedRoles->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve role options', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Format role data for API response
     */
    private function formatRoleData(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'display_name' => $this->getDisplayName($role->name),
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
            'users_count' => $role->users_count ?? 0,
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ];
    }

    /**
     * Get human-readable display name for role
     */
    private function getDisplayName(string $roleName): string
    {
        $displayNames = [
            'super_admin' => 'Super Administrator',
            'administrator' => 'Administrator',
            'editor' => 'Editor',
            'viewer' => 'Viewer',
            'moderator' => 'Moderator',
        ];

        return $displayNames[$roleName] ?? ucwords(str_replace('_', ' ', $roleName));
    }

}
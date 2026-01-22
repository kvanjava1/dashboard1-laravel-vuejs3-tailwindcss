<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class RoleService
{
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

            // Prevent deletion of super_admin role
            if ($role->name === 'super_admin') {
                throw new \Exception('Cannot delete super_admin role', 403);
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
<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    /**
     * Get all permissions with their details
     */
    public function getAllPermissions(): array
    {
        try {
            $permissions = Permission::all();

            $formattedPermissions = $permissions->map(function ($permission) {
                return $this->formatPermissionData($permission);
            });

            Log::info('Permissions retrieved successfully', ['count' => $permissions->count()]);

            return $formattedPermissions->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve permissions', [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Get permissions grouped by feature/category
     */
    public function getPermissionsGrouped(): array
    {
        try {
            $permissions = Permission::all();

            $grouped = [];

            foreach ($permissions as $permission) {
                $category = $this->categorizePermission($permission->name);

                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }

                $grouped[$category][] = $this->formatPermissionData($permission);
            }

            // Sort categories alphabetically for consistent ordering
            ksort($grouped);

            Log::info('Permissions retrieved and grouped successfully', [
                'categories' => array_keys($grouped),
                'total_permissions' => $permissions->count()
            ]);

            return $grouped;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve grouped permissions', [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Categorize a permission based on its name
     * Uses the first part of the dot-separated permission name
     */
    private function categorizePermission(string $permissionName): string
    {
        $parts = explode('.', $permissionName);

        // Return the first part as category, or 'other' if no dot found
        return $parts[0] ?? 'other';
    }

    /**
     * Format permission data for API response
     */
    private function formatPermissionData(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'label' => $this->getPermissionLabel($permission->name),
            'category' => $this->categorizePermission($permission->name),
            'guard_name' => $permission->guard_name,
            'created_at' => $permission->created_at,
            'updated_at' => $permission->updated_at,
        ];
    }

    /**
     * Get human-readable label for a permission
     */
    private function getPermissionLabel(string $permissionName): string
    {
        return ucwords(str_replace(['_', '.'], [' ', ' '], $permissionName));
    }

}
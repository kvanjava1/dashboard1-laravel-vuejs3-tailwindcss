<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions grouped by feature
        $permissions = [
            'dashboard' => [
                'dashboard.view',
            ],
            'user_management' => [
                'user_management.view',
                'user_management.view_detail',
                'user_management.add',
                'user_management.edit',
                'user_management.delete',
                'user_management.search',
                'user_management.ban',
                'user_management.unban',
            ],
            'role_management' => [
                'role_management.view',
                'role_management.view_detail',
                'role_management.add',
                'role_management.edit',
                'role_management.delete',
                'role_management.search',
            ],
            'profile_management' => [
                'profile.view',
                'profile.edit',
                'profile.update_password',
            ],
            'settings' => [
                'settings.view',
                'settings.edit',
            ]
        ];

        // Create all permissions (flattened)
        $allPermissions = [];
        foreach ($permissions as $feature => $permissionList) {
            foreach ($permissionList as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
                $allPermissions[] = $permission;
            }
        }

        // Create super_admin role with ALL permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        // Super admin gets ALL permissions (even future ones)
        $superAdminRole->syncPermissions(Permission::all());

        // Create or update the default super admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'super@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('12345678'),
                'is_banned' => false,
                'is_active' => true,
            ]
        );

        // Assign super_admin role to the user
        $superAdminUser->assignRole('super_admin');
    }
}

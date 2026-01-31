<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GuestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the active status
        $activeStatus = \App\Models\UserAccountStatus::where('name', 'active')->first();

        // Ensure the 'guest' role exists
        $guestRole = Role::firstOrCreate([
            'name' => 'guest',
            'guard_name' => 'web',
        ]);

        // Create 10 users with 'guest' role
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Guest User $i",
                'email' => "guest$i@example.com",
                'password' => Hash::make('password')
            ]);
            $user->assignRole($guestRole);
        }
    }
}

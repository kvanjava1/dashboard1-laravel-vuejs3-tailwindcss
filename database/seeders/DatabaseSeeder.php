<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Register RolePermissionSeeder
        $this->call([
            UserAccountStatusSeeder::class, // Must run first to create status records
            RolePermissionSeeder::class,
            GuestUserSeeder::class,
        ]);
    }
}

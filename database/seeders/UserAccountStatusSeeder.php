<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserAccountStatus;

class UserAccountStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'not_activated',
                'display_name' => 'Not Activated',
                'color' => 'gray',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'active',
                'display_name' => 'Active',
                'color' => 'green',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'banned',
                'display_name' => 'Banned',
                'color' => 'red',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($statuses as $status) {
            UserAccountStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}

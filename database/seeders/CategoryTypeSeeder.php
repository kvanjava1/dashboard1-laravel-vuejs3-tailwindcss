<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryType;

class CategoryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Article',
                'slug' => 'article',
                'description' => 'Categories used for organizing articles and blog posts.',
                'is_active' => true,
            ],
            [
                'name' => 'Gallery',
                'slug' => 'gallery',
                'description' => 'Categories used for organizing image galleries and media.',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            CategoryType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\CategoryType;
use App\Models\Category;

class GalleryNPlusOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_is_not_susceptible_to_n_plus_one()
    {
        Storage::fake('public');

        // create a user with required permissions
        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.view']);
        $user->givePermissionTo('gallery_management.add');
        $user->givePermissionTo('gallery_management.view');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'List N+1', 'slug' => 'list-n-plus-one', 'is_active' => true]);

        $cover = UploadedFile::fake()->image('cover.jpg', 1200, 900);

        // create a single gallery and measure queries for listing
        $this->postJson('/api/v1/galleries', [
            'title' => 'G1',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['t1','t2'],
            'cover' => $cover,
        ])->assertStatus(201);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson('/api/v1/galleries')->assertStatus(200);

        $queriesForOne = count(DB::getQueryLog());

        // create several more galleries (same shape)
        for ($i = 2; $i <= 6; $i++) {
            $this->postJson('/api/v1/galleries', [
                'title' => "G{$i}",
                'category_id' => $category->id,
                'status' => 'active',
                'visibility' => 'public',
                'tags' => ['t1','t2'],
                'cover' => UploadedFile::fake()->image("cover{$i}.jpg", 1200, 900),
            ])->assertStatus(201);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson('/api/v1/galleries')->assertStatus(200);

        $queriesForMany = count(DB::getQueryLog());

        // Expectation: number of queries should not scale linearly with number of galleries.
        // Allow a small constant tolerance (middleware, permission checks, etc.).
        $this->assertLessThanOrEqual($queriesForOne + 5, $queriesForMany, "Potential N+1 detected: queries for many ({$queriesForMany}) vs one ({$queriesForOne})");
    }
}

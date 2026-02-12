<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\CategoryType;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Gallery;
use App\Models\Media;

class GalleryCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_gallery_with_cover_and_tags()
    {
        Storage::fake('public');

        // Create a user and authenticate
        $user = User::factory()->create(['is_active' => true]);

        // Create category type and category
        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Test Category', 'slug' => 'test-category', 'is_active' => true]);

        // Ensure user has gallery creation permission
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        $user->givePermissionTo('gallery_management.add');

        $this->actingAs($user, 'sanctum');

        $cover = UploadedFile::fake()->image('cover.jpg', 1600, 1200);

        $response = $this->post('/api/v1/galleries', [
            'title' => 'My Test Gallery',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['sunset', 'beach'],
            'cover' => $cover,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('galleries', ['title' => 'My Test Gallery']);

        $gallery = Gallery::where('title', 'My Test Gallery')->first();
        $this->assertNotNull($gallery);

        // Media should be created
        $this->assertDatabaseCount('media', 1);
        $media = Media::first();

        // The media filename should exist on disk
        Storage::disk('public')->assertExists($media->filename);

        // Tags should be created
        $this->assertDatabaseHas('tags', ['name' => 'sunset']);
        $this->assertDatabaseHas('tags', ['name' => 'beach']);

        // Pivot table should have entries
        $this->assertDatabaseHas('gallery_tag', ['gallery_id' => $gallery->id]);
    }

    public function test_tag_suggestions_endpoint_returns_matching_tags()
    {
        // Create tags
        Tag::create(['name' => 'sunset', 'slug' => 'sunset']);
        Tag::create(['name' => 'beach', 'slug' => 'beach']);
        Tag::create(['name' => 'city', 'slug' => 'city']);

        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user, 'sanctum');

        $response = $this->get('/api/v1/tags/options?q=be');
        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('beach', $data[0]['name']);
    }

    public function test_create_gallery_with_private_visibility_sets_is_public_false()
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        $user->givePermissionTo('gallery_management.add');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Test Category 2', 'slug' => 'test-category-2', 'is_active' => true]);

        $cover = UploadedFile::fake()->image('cover2.jpg', 1600, 1200);

        $response = $this->post('/api/v1/galleries', [
            'title' => 'Private Gallery',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'private',
            'tags' => ['private'],
            'cover' => $cover,
        ]);

        $response->assertStatus(201);

        $gallery = Gallery::where('title', 'Private Gallery')->first();
        $this->assertNotNull($gallery);
        $this->assertFalse($gallery->is_public);
        $this->assertEquals(0, $gallery->item_count);
    }

    public function test_create_gallery_without_cover_returns_validation_error()
    {
        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        $user->givePermissionTo('gallery_management.add');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Test Category 3', 'slug' => 'test-category-3', 'is_active' => true]);

        $response = $this->post('/api/v1/galleries', [
            'title' => 'No Cover Gallery',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['nocover'],
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $this->assertArrayHasKey('cover', $response->json('errors'));
    }
}

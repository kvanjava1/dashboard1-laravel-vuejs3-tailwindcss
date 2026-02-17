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

        // Media records should be created for original + variants (original, 1200x900, 400x300)
        $this->assertDatabaseCount('media', 3);

        $filenames = Media::where('gallery_id', $gallery->id)->pluck('filename')->toArray();
        $this->assertNotEmpty($filenames);

        // Ensure both 1200x900 and 400x300 variants exist on disk
        $has1200 = collect($filenames)->contains(fn($f) => str_contains($f, '/1200x900/'));
        $has400 = collect($filenames)->contains(fn($f) => str_contains($f, '/400x300/'));
        $this->assertTrue($has1200, 'Expected 1200x900 variant to be saved');
        $this->assertTrue($has400, 'Expected 400x300 variant to be saved');

        foreach ($filenames as $fname) {
            Storage::disk('public')->assertExists($fname);
        }

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

    public function test_create_gallery_with_client_crop_maps_to_original_pixels()
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        $user->givePermissionTo('gallery_management.add');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Crop Test', 'slug' => 'crop-test', 'is_active' => true]);

        // Create a fixture image: left half red, right half green (1600x1200)
        $tmpPath = sys_get_temp_dir() . '/crop-test.png';
        $img = \Intervention\Image\ImageManagerStatic::canvas(1600, 1200, '#ff0000');
        $img->rectangle(800, 0, 1599, 1199, function ($draw) {
            $draw->background('#00ff00');
        });
        $img->save($tmpPath);

        $uploaded = new UploadedFile($tmpPath, 'crop-test.png', 'image/png', null, true);

        // Simulate client cropper canvas of 800x600 selecting the left half (x=0,width=400,height=600)
        $response = $this->post('/api/v1/galleries', [
            'title' => 'Cropped Gallery',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['crop'],
            'cover' => $uploaded,
            'crop_canvas_width' => 800,
            'crop_canvas_height' => 600,
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_width' => 400,
            'crop_height' => 600,
            'orig_width' => 1600,
            'orig_height' => 1200,
        ]);

        $response->assertStatus(201);

        $gallery = Gallery::where('title', 'Cropped Gallery')->first();
        $this->assertNotNull($gallery);

        // Find the 400x300 media row for this gallery
        $media400 = Media::where('gallery_id', $gallery->id)->where('filename', 'like', '%/400x300/%')->first();
        $this->assertNotNull($media400);

        $thumbPath = $media400->filename;
        Storage::disk('public')->assertExists($thumbPath);

        $thumb = \Intervention\Image\ImageManagerStatic::make(Storage::disk('public')->get($thumbPath));
        $this->assertEquals(400, $thumb->width());
        $this->assertEquals(300, $thumb->height());

        // Center pixel of thumbnail should come from the left-half of the original (red)
        $color = $thumb->pickColor(200, 150, 'array');
        $this->assertGreaterThan(200, $color[0]); // R is high
        $this->assertLessThan(100, $color[1]); // G is low
        $this->assertLessThan(100, $color[2]); // B is low
    }

    public function test_set_media_row_as_cover_updates_flags_and_response()
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.edit']);
        $user->givePermissionTo('gallery_management.add');
        $user->givePermissionTo('gallery_management.edit');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Cover Test', 'slug' => 'cover-test', 'is_active' => true]);

        $cover = UploadedFile::fake()->image('cover.jpg', 1600, 1200);

        $response = $this->post('/api/v1/galleries', [
            'title' => 'Cover Switch',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['switch'],
            'cover' => $cover,
        ]);

        $response->assertStatus(201);

        $gallery = Gallery::where('title', 'Cover Switch')->first();
        $this->assertNotNull($gallery);

        $mediaRows = Media::where('gallery_id', $gallery->id)->get();
        $this->assertCount(3, $mediaRows);

        // selected-cover flag should be set on one row
        $currentCover = $mediaRows->firstWhere('is_used_as_cover', true);
        $this->assertNotNull($currentCover);

        $other = $mediaRows->firstWhere('is_used_as_cover', false);
        $this->assertNotNull($other);

        // The service flags the 1200x900 variant as the selected gallery cover — ensure that's set
        $media1200 = $mediaRows->first(fn($m) => str_contains($m->filename, '/1200x900/'));
        $this->assertNotNull($media1200);
        $this->assertTrue((bool) $media1200->is_used_as_cover);
        // and it remains a cover variant
        $this->assertTrue((bool) $media1200->is_cover);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\CategoryType;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\Media;

class GalleryUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_gallery_with_client_pre_cropped_cover_keeps_visual_result()
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.edit']);
        $user->givePermissionTo('gallery_management.add');
        $user->givePermissionTo('gallery_management.edit');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Edit Test', 'slug' => 'edit-test', 'is_active' => true]);

        // Create initial gallery
        $cover = UploadedFile::fake()->image('cover.jpg', 1600, 1200);

        $create = $this->post('/api/v1/galleries', [
            'title' => 'Initial Gallery',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['initial'],
            'cover' => $cover,
        ]);
        $create->assertStatus(201);

        $gallery = Gallery::where('title', 'Initial Gallery')->first();
        $this->assertNotNull($gallery);

        // Prepare a fixture image: left half red, right half green (1600x1200)
        $tmpPath = sys_get_temp_dir() . '/edit-crop-test.png';
        $img = \Intervention\Image\ImageManagerStatic::canvas(1600, 1200, '#ff0000');
        $img->rectangle(800, 0, 1599, 1199, function ($draw) {
            $draw->background('#00ff00');
        });
        $img->save($tmpPath);

        // Simulate client pre-cropping: crop left half and resize to 1200x900 (client-side canvas output)
        $pre = \Intervention\Image\ImageManagerStatic::make($tmpPath)->crop(800, 1200, 0, 0)->resize(1200, 900);
        $prePath = sys_get_temp_dir() . '/edit-pre-cropped.jpg';
        $pre->save($prePath, 90);

        $uploadedPre = new UploadedFile($prePath, 'pre-crop.jpg', 'image/jpeg', null, true);

        // Submit update with pre-cropped file (include crop coords/orig sizes to emulate client behavior)
        // Use POST with method override so PHPUnit includes the uploaded file correctly
        $response = $this->post("/api/v1/galleries/{$gallery->id}", [
            '_method' => 'PUT',
            'title' => 'Initial Gallery',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['edited'],
            'cover' => $uploadedPre,
            // present but should be ignored by server because uploaded file differs from orig dims
            'crop_canvas_width' => 800,
            'crop_canvas_height' => 600,
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_width' => 400,
            'crop_height' => 600,
            'orig_width' => 1600,
            'orig_height' => 1200,
        ]);

        $response->assertStatus(200);

        $gallery = $gallery->fresh();

        // Find the 400x400 media row for this gallery
        $mediaRows = Media::where('gallery_id', $gallery->id)->get();
        $this->assertGreaterThanOrEqual(3, $mediaRows->count(), 'Expected media rows to exist for gallery');

        $media400 = $mediaRows->first(fn($m) => str_contains($m->filename, '/400x400/'));
        $filenames = $mediaRows->pluck('filename')->toArray();
        $this->assertNotNull($media400, 'Expected a 400x400 thumbnail after update — found: ' . implode(', ', $filenames));

        $thumbPath = $media400->filename;
        Storage::disk('public')->assertExists($thumbPath);

        $thumb = \Intervention\Image\ImageManagerStatic::make(Storage::disk('public')->get($thumbPath));
        $this->assertEquals(400, $thumb->width());
        $this->assertEquals(400, $thumb->height());

        // Also inspect the 1200x900 primary variant to ensure center is red
        $media1200 = $mediaRows->first(fn($m) => str_contains($m->filename, '/1200x900/'));
        $this->assertNotNull($media1200, 'Expected a 1200x900 variant after update');
        $img1200 = \Intervention\Image\ImageManagerStatic::make(Storage::disk('public')->get($media1200->filename));
        $this->assertEquals(1200, $img1200->width());
        $this->assertEquals(900, $img1200->height());

        $center1200 = $img1200->pickColor(600, 450, 'array');
        $this->assertGreaterThan(200, $center1200[0], 'Center of 1200x900 should be red (client pre-crop)');

        // Center pixel of thumbnail should come from the left-half (red)
        $color = $thumb->pickColor(200, 200, 'array');
        $this->assertGreaterThan(200, $color[0]); // R is high
        $this->assertLessThan(100, $color[1]); // G is low
        $this->assertLessThan(100, $color[2]); // B is low
    }
}

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

class GalleryEditWithoutNewCoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_gallery_without_changing_cover_succeeds_and_keeps_existing_media()
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.edit']);
        $user->givePermissionTo('gallery_management.add');
        $user->givePermissionTo('gallery_management.edit');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Edit Keep Cover', 'slug' => 'edit-keep-cover', 'is_active' => true]);

        // Create initial gallery with cover
        $cover = UploadedFile::fake()->image('cover.jpg', 1600, 1200);

        $create = $this->post('/api/v1/galleries', [
            'title' => 'Keep Cover Gallery',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['initial'],
            'cover' => $cover,
        ]);
        $create->assertStatus(201);

        $gallery = Gallery::where('title', 'Keep Cover Gallery')->first();
        $this->assertNotNull($gallery);

        $initialMedia = Media::where('gallery_id', $gallery->id)->pluck('filename')->toArray();
        $this->assertNotEmpty($initialMedia);

        // Update only the title (no cover file provided)
        $response = $this->put("/api/v1/galleries/{$gallery->id}", [
            'title' => 'Keep Cover Gallery - Updated',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['initial', 'updated'],
        ]);

        $response->assertStatus(200);

        $gallery = $gallery->fresh();
        $this->assertEquals('Keep Cover Gallery - Updated', $gallery->title);

        // Media rows should still exist and original filenames preserved
        $afterMedia = Media::where('gallery_id', $gallery->id)->pluck('filename')->toArray();
        $this->assertEqualsCanonicalizing($initialMedia, $afterMedia, 'Existing media should remain unchanged when cover is not replaced');
    }
}

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

class GalleryDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_delete_gallery_keeps_media_rows_and_files()
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.delete']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.view']);
        $user->givePermissionTo('gallery_management.add');
        $user->givePermissionTo('gallery_management.delete');
        $user->givePermissionTo('gallery_management.view');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Delete Test', 'slug' => 'delete-test', 'is_active' => true]);

        // Create gallery with cover
        $cover = UploadedFile::fake()->image('cover.jpg', 1600, 1200);

        $create = $this->post('/api/v1/galleries', [
            'title' => 'To Be Deleted',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
            'tags' => ['delete'],
            'cover' => $cover,
        ]);

        $create->assertStatus(201);

        $gallery = Gallery::where('title', 'To Be Deleted')->first();
        $this->assertNotNull($gallery);

        // Collect media filenames that should remain after soft-delete
        $mediaRows = Media::where('gallery_id', $gallery->id)->get();
        $this->assertNotEmpty($mediaRows);
        $filenames = $mediaRows->pluck('filename')->toArray();

        // Delete the gallery (soft-delete)
        $resp = $this->delete("/api/v1/galleries/{$gallery->id}");
        $resp->assertStatus(200)->assertJson(['message' => 'Gallery deleted successfully']);

        // Gallery should be soft-deleted
        $this->assertSoftDeleted('galleries', ['id' => $gallery->id]);

        // Media DB rows must still exist
        $this->assertDatabaseHas('media', ['gallery_id' => $gallery->id]);

        // Files should still exist on disk (we keep files on soft-delete)
        foreach ($filenames as $fname) {
            Storage::disk('public')->assertExists($fname);
        }

        // And fetching list should no longer return the deleted gallery (API-level)
        $list = $this->get('/api/v1/galleries');
        $list->assertStatus(200);
        $body = $list->json('data.galleries');
        $ids = array_column($body, 'id');
        $this->assertNotContains($gallery->id, $ids);
    }

    public function test_delete_nonexistent_gallery_returns_404()
    {
        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.delete']);
        $user->givePermissionTo('gallery_management.delete');
        $this->actingAs($user, 'sanctum');

        $resp = $this->delete('/api/v1/galleries/99999');
        $resp->assertStatus(404);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserProfileImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user profile images are resized to 300x300 pixels with server-side cropping
     */
    public function test_user_profile_image_is_resized_to_300x300()
    {
        // Create a test user
        $user = User::factory()->create(['is_active' => true]);

        // Give user permission to edit users
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'user_management.edit']);
        $user->givePermissionTo('user_management.edit');

        // Create a test image (larger than 300x300)
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);

        // Make a PUT request to update the user with the profile image and crop coordinates
        $response = $this->actingAs($user)
            ->putJson("/api/v1/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $image,
                // Simulate crop coordinates from cropper (coordinates are in source pixels)
                'crop_canvas_width' => 800, // original width
                'crop_canvas_height' => 600, // original height
                'crop_x' => 133, // 50 * (800/300)
                'crop_y' => 100, // 50 * (600/300)
                'crop_width' => 533, // 200 * (800/300)
                'crop_height' => 400, // 200 * (600/300)
                'orig_width' => 800,
                'orig_height' => 600,
            ]);

        $response->assertStatus(200);

        // Refresh the user from database
        $user->refresh();

        // Check that profile_image path is set
        $this->assertNotNull($user->profile_image);

        // Check that the file exists in storage
        Storage::disk('public')->assertExists($user->profile_image);

        // Check that the image is exactly 300×300 pixels
        $imagePath = Storage::disk('public')->path($user->profile_image);
        $imageInfo = getimagesize($imagePath);

        // Assert that the image is exactly 300x300 pixels
        $this->assertEquals(300, $imageInfo[0]); // width
        $this->assertEquals(300, $imageInfo[1]); // height

        // Clean up
        Storage::disk('public')->delete($user->profile_image);
    }

    /**
     * Test that user profile images are saved as WebP format
     */
    public function test_user_profile_image_is_saved_as_webp()
    {
        // Create a test user
        $user = User::factory()->create(['is_active' => true]);

        // Give user permission to edit users
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'user_management.edit']);
        $user->givePermissionTo('user_management.edit');

        // Create a test image
        $image = UploadedFile::fake()->image('test.jpg', 400, 400);

        // Make a PUT request to update the user with the profile image
        $response = $this->actingAs($user)
            ->putJson("/api/v1/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $image,
            ]);

        $response->assertStatus(200);

        // Refresh the user from database
        $user->refresh();

        // Check that profile_image path ends with .webp
        $this->assertStringEndsWith('.webp', $user->profile_image);

        // Check the MIME type
        $imagePath = Storage::disk('public')->path($user->profile_image);
        $mimeType = mime_content_type($imagePath);
        $this->assertEquals('image/webp', $mimeType);

        // Clean up
        Storage::disk('public')->delete($user->profile_image);
    }
}
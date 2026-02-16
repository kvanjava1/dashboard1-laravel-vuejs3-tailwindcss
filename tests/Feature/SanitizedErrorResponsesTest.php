<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\CategoryType;
use App\Models\Category;

class SanitizedErrorResponsesTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_store_does_not_leak_exception_message()
    {
        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gallery_management.add']);
        $user->givePermissionTo('gallery_management.add');
        $this->actingAs($user, 'sanctum');

        $type = CategoryType::create(['name' => 'Gallery', 'slug' => 'gallery']);
        $category = Category::create(['category_type_id' => $type->id, 'name' => 'Test Category', 'slug' => 'test-category', 'is_active' => true]);

        // Force the service to throw an exception with sensitive message
        $this->mock(\App\Services\GalleryService::class, function ($mock) {
            $mock->shouldReceive('createGallery')->andThrow(new \Exception('sensitive internal detail'));
        });

        $response = $this->postJson('/api/v1/galleries', [
            'title' => 'Leak Test',
            'category_id' => $category->id,
            'status' => 'active',
            'visibility' => 'public',
        ]);

        $response->assertStatus(500);
        $json = $response->json();
        $this->assertArrayNotHasKey('error', $json);
        $this->assertStringNotContainsString('sensitive internal detail', json_encode($json));
        $this->assertEquals('Failed to create gallery', $json['message']);
    }

    public function test_user_delete_does_not_leak_exception_message_and_respects_status_code()
    {
        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'user_management.delete']);
        $user->givePermissionTo('user_management.delete');
        $this->actingAs($user, 'sanctum');

        $target = User::factory()->create(['is_active' => true]);

        $this->mock(\App\Services\UserService::class, function ($mock) use ($target) {
            $mock->shouldReceive('deleteUser')->with($target->id)->andThrow(new \Exception('Cannot delete super admin users', 403));
        });

        $response = $this->deleteJson("/api/v1/users/{$target->id}");
        $response->assertStatus(403);
        $json = $response->json();
        $this->assertArrayNotHasKey('error', $json);
        $this->assertStringNotContainsString('Cannot delete super admin users', json_encode($json));
        $this->assertEquals('Failed to delete user', $json['message']);
    }

    public function test_role_delete_does_not_expose_internal_message_on_conflict()
    {
        $user = User::factory()->create(['is_active' => true]);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'role_management.delete']);
        $user->givePermissionTo('role_management.delete');
        $this->actingAs($user, 'sanctum');

        $role = \Spatie\Permission\Models\Role::create(['name' => 'test-role']);

        $this->mock(\App\Services\RoleService::class, function ($mock) use ($role) {
            $mock->shouldReceive('deleteRole')->with($role->id)->andThrow(new \Exception('Role has assigned users', 409));
        });

        $response = $this->deleteJson("/api/v1/roles/{$role->id}");
        $response->assertStatus(409);
        $json = $response->json();
        $this->assertArrayNotHasKey('error', $json);
        $this->assertStringNotContainsString('Role has assigned users', json_encode($json));
        $this->assertEquals('Failed to delete role', $json['message']);
    }
}

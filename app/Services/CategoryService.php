<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    /**
     * Get all categories with filtering.
     * Returns a flat list that the frontend turns into a tree.
     */
    public function getAllCategories(array $filters = []): array
    {
        try {
            $query = Category::with('type');

            // Filter by search term
            if (!empty($filters['search'])) {
                $term = $filters['search'];
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('slug', 'like', "%{$term}%");
                });
            }

            // Filter by type (slug)
            if (!empty($filters['type'])) {
                $typeSlug = $filters['type'];
                $query->whereHas('type', function ($q) use ($typeSlug) {
                    $q->where('slug', $typeSlug);
                });
            }

            // Filter by status (active/inactive)
            if (isset($filters['status']) && $filters['status'] !== '') {
                $isActive = $filters['status'] === 'active';
                $query->where('is_active', $isActive);
            }

            // Filter by exact slug
            if (!empty($filters['slug'])) {
                $query->where('slug', $filters['slug']);
            }

            // Default sort
            $query->orderBy('created_at', 'desc');

            $categories = $query->get();

            return $categories->map(function ($category) {
                return $this->formatCategory($category);
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve categories', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data): array
    {
        try {
            // Find type ID by slug
            $type = CategoryType::where('slug', $data['type'])->firstOrFail();

            // Generate slug if not provided, or use provided
            $slug = $data['slug'] ?? Str::slug($data['name']);

            // Ensure unique slug (simple check, validation should handle this too)
            if (Category::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . time();
            }

            $category = Category::create([
                'category_type_id' => $type->id,
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $this->formatCategory($category);

        } catch (\Exception $e) {
            Log::error('Failed to create category', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(int $id, array $data): array
    {
        try {
            $category = Category::findOrFail($id);

            // Update type if provided
            if (isset($data['type'])) {
                $type = CategoryType::where('slug', $data['type'])->firstOrFail();
                $category->category_type_id = $type->id;
            }

            if (isset($data['name'])) {
                $category->name = $data['name'];
            }
            
            if (isset($data['slug'])) {
                $category->slug = $data['slug'];
            }

            if (isset($data['description'])) {
                $category->description = $data['description'];
            }

            // Handle parent_id update (prevent circular reference check suggested for robust impl)
            if (array_key_exists('parent_id', $data)) {
                if ($data['parent_id'] == $id) {
                    throw new \Exception('Category cannot be its own parent.');
                }
                $category->parent_id = $data['parent_id'];
            }

            if (isset($data['is_active'])) {
                $category->is_active = $data['is_active'];
            }

            $category->save();

            return $this->formatCategory($category);

        } catch (\Exception $e) {
            Log::error('Failed to update category', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(int $id): bool
    {
        try {
            $category = Category::findOrFail($id);
            // Children's parent_id will be set to NULL by DB constraint (ON DELETE SET NULL)
            // But we can explicitly handle it if business logic requires logic like reparenting.
            // For now, rely on DB or basic logic.
            
            $category->delete();

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete category', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Format a category model for API response.
     */
    private function formatCategory(Category $category): array
    {
        // Load type if not already loaded
        if (!$category->relationLoaded('type')) {
            $category->load('type');
        }

        return [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'is_active' => (bool) $category->is_active,
            'type' => $category->type ? $category->type->slug : null, // article or gallery
            'created_at' => $category->created_at->toIso8601String(),
            'updated_at' => $category->updated_at->toIso8601String(),
        ];
    }

    public function getCategoryById(int $id): array
    {
        try {
            $category = Category::with('type')->findOrFail($id);
            return $this->formatCategory($category);
        } catch (\Exception $e) {
            Log::error('Failed to get category', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}

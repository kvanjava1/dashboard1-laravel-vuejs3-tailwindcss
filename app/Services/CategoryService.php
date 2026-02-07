<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryType;
use App\Traits\CanVersionCache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    use CanVersionCache;

    private const CACHE_SCOPE = 'categories';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all categories with filtering.
     * Returns a flat list that the frontend turns into a tree.
     */
    public function getAllCategories(array $filters = []): array
    {
        try {
            // 1. Check cache first (using linear style)
            $cacheKey = $this->getVersionedKey(self::CACHE_SCOPE, $filters);
            $cachedData = Cache::get($cacheKey);

            if ($cachedData !== null) {
                return $cachedData;
            }

            // 2. No cache, process the data
            $query = Category::with('type');

            if (!empty($filters['search'])) {
                $term = $filters['search'];
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('slug', 'like', "%{$term}%");
                });
            }

            if (!empty($filters['type'])) {
                $typeSlug = $filters['type'];
                $query->whereHas('type', function ($q) use ($typeSlug) {
                    $q->where('slug', $typeSlug);
                });
            }

            if (isset($filters['status']) && $filters['status'] !== '') {
                $isActive = $filters['status'] === 'active';
                $query->where('is_active', $isActive);
            }

            if (!empty($filters['slug'])) {
                $query->where('slug', $filters['slug']);
            }

            $query->orderBy('created_at', 'desc');
            $categories = $query->get();

            $result = $categories->map(function ($category) {
                return $this->formatCategory($category);
            })->toArray();

            // 3. Save cache
            Cache::put($cacheKey, $result, self::CACHE_TTL);

            // 4. Return result
            return $result;

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
            $type = CategoryType::where('slug', $data['type'])->firstOrFail();
            $slug = $data['slug'] ?? Str::slug($data['name']);

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

            // Clear cache by bumping version
            $this->clearVersionedCache(self::CACHE_SCOPE);

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

            // Clear cache by bumping version
            $this->clearVersionedCache(self::CACHE_SCOPE);

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
            $category->delete();

            // Clear cache by bumping version
            $this->clearVersionedCache(self::CACHE_SCOPE);

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
            'type' => $category->type ? $category->type->slug : null,
            'created_at' => $category->created_at->toIso8601String(),
            'updated_at' => $category->updated_at->toIso8601String(),
        ];
    }

    public function getCategoryById(int $id): array
    {
        try {
            $cacheKey = $this->getVersionedKey(self::CACHE_SCOPE, ['id' => $id]);
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }

            $category = Category::with('type')->findOrFail($id);
            $result = $this->formatCategory($category);

            Cache::put($cacheKey, $result, self::CACHE_TTL);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to get category', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Manually clear the category cache by bumping the version.
     */
    public function clearCache(): void
    {
        $this->clearVersionedCache(self::CACHE_SCOPE);
    }
}

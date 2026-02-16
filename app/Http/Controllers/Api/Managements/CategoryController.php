<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $categories = $this->categoryService->getAllCategories($request->all());

            return response()->json([
                'data' => $categories,
                'meta' => [
                    'total' => count($categories) // Since no pagination
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve categories', [
                'exception' => $e,
                'filters' => $request->all(),
                'user_id' => $request->user()?->id ?? null,
            ]);

            return response()->json(['message' => 'Failed to retrieve categories'], 500);
        }
    }

    /**
     * Get categories for dropdown options.
     */
    public function options(Request $request): JsonResponse
    {
        try {
            $options = $this->categoryService->getCategoryOptions($request->all());
            return response()->json(['data' => $options]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve category options', [
                'exception' => $e,
                'filters' => $request->all(),
                'user_id' => $request->user()?->id ?? null,
            ]);
            return response()->json(['message' => 'Failed to retrieve category options'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->createCategory($request->validated());
            return response()->json(['data' => $category], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create category', [
                'exception' => $e,
                'data' => $request->validated(),
                'user_id' => $request->user()?->id ?? null,
            ]);
            return response()->json(['message' => 'Failed to create category'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            // Using ID manually to ensure service handles generic lookup if needed
            $category = $this->categoryService->getCategoryById((int) $id);
            return response()->json(['data' => $category]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve category', [
                'exception' => $e,
                'category_id' => $id,
                'requested_by' => request()->user()?->id ?? null,
            ]);
            $status = $e->getCode() === 404 ? 404 : 500;
            return response()->json(['message' => 'Failed to retrieve category'], $status);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $category = $this->categoryService->updateCategory((int) $id, $request->validated());
            return response()->json(['data' => $category]);
        } catch (\Exception $e) {
            Log::error('Failed to update category', [
                'exception' => $e,
                'category_id' => $id,
                'updated_by' => $request->user()?->id ?? null,
            ]);
            $status = $e->getCode() === 404 ? 404 : 500;
            return response()->json(['message' => 'Failed to update category'], $status);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory((int) $id);
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete category', [
                'exception' => $e,
                'category_id' => $id,
                'deleted_by' => request()->user()?->id ?? null,
            ]);
            $status = $e->getCode() === 403 ? 403 : ($e->getCode() === 404 ? 404 : 500);
            return response()->json(['message' => 'Failed to delete category'], $status);
        }
    }

    /**
     * Clear category cache manually.
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            $this->categoryService->clearCache();
            return response()->json(['message' => 'Category cache cleared successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to clear category cache', ['exception' => $e, 'user_id' => $request->user()?->id ?? null]);
            return response()->json(['message' => 'Failed to clear category cache'], 500);
        }
    }
}

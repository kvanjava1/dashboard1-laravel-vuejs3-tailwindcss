<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\StoreGalleryRequest;
use App\Services\GalleryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GalleryController extends Controller
{
    protected GalleryService $galleryService;

    public function __construct(GalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
    }

    /**
     * Store a newly created gallery
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = intval($request->input('per_page', 15));
        $page = intval($request->input('page', 1));

        $query = \App\Models\Gallery::with(['tags:id,name,slug','cover']);

        if ($search = $request->input('search')) {
            $query->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
        }

        if ($category = $request->input('category_id')) {
            $query->where('category_id', $category);
        }

        $paginated = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        $result = [
            'galleries' => $paginated->getCollection()->map(function ($g) {
                return [
                    'id' => $g->id,
                    'title' => $g->title,
                    'slug' => $g->slug,
                    'description' => $g->description,
                    'category_id' => $g->category_id,
                    'is_active' => $g->is_active,
                    'is_public' => $g->is_public,
                    'item_count' => $g->item_count,
                    'cover' => $g->cover ? ['id' => $g->cover->id, 'url' => $g->cover->url] : null,
                    'tags' => $g->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toArray(),
                    'created_at' => $g->created_at,
                ];
            })->toArray(),
            'total' => $paginated->total(),
            'per_page' => $paginated->perPage(),
            'current_page' => $paginated->currentPage(),
            'total_pages' => $paginated->lastPage(),
        ];

        return response()->json([
            'message' => 'Galleries retrieved successfully',
            'data' => $result,
        ]);
    }

    public function store(StoreGalleryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Prepare tags - when sent as JSON string, ensure array
            $tags = [];
            if ($request->has('tags')) {
                $tags = $request->input('tags');
                if (is_string($tags)) {
                    $json = json_decode($tags, true);
                    if (is_array($json)) $tags = $json;
                }
            }

            $data = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'],
                'status' => $validated['status'],
                'visibility' => $validated['visibility'],
                'tags' => $tags,
            ];

            $cover = $request->file('cover');

            $gallery = $this->galleryService->createGallery($data, $cover);

            return response()->json([
                'message' => 'Gallery created successfully',
                'gallery' => $gallery,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create gallery', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to create gallery',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $gallery = \App\Models\Gallery::with(['tags:id,name,slug','cover'])->findOrFail($id);
            return response()->json(['message' => 'Gallery retrieved successfully', 'gallery' => $gallery]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve gallery', 'error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}

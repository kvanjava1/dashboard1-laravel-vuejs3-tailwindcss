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
                    // cover (single) kept for backward compatibility
                    'cover' => $g->cover ? ['id' => $g->cover->id, 'url' => $g->cover->url] : null,
                    // expose all media variants for this gallery
                    'media' => $g->media->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'filename' => $m->filename,
                            'url' => $m->url,
                            'extension' => $m->extension,
                            'mime_type' => $m->mime_type,
                            'size' => $m->size,
                            'is_cover' => (bool) ($m->is_cover ?? false),
                            'uploaded_at' => $m->uploaded_at,
                        ];
                    })->toArray(),
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

            // Include optional crop coordinates so service can crop from original
            if ($request->has('crop_x') && $request->has('crop_y') && $request->has('crop_width') && $request->has('crop_height')) {
                $data['crop'] = [
                    'canvas_width' => intval($request->input('crop_canvas_width', 0)),
                    'canvas_height' => intval($request->input('crop_canvas_height', 0)),
                    'x' => intval($request->input('crop_x')),
                    'y' => intval($request->input('crop_y')),
                    'width' => intval($request->input('crop_width')),
                    'height' => intval($request->input('crop_height')),
                    'orig_width' => intval($request->input('orig_width', 0)),
                    'orig_height' => intval($request->input('orig_height', 0)),
                ];
            }

            $cover = $request->file('cover');

            $gallery = $this->galleryService->createGallery($data, $cover);

            return response()->json([
                'message' => 'Gallery created successfully',
                'gallery' => $gallery,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create gallery'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $gallery = \App\Models\Gallery::with(['tags:id,name,slug','media'])->findOrFail($id);

            $payload = $gallery->toArray();
            // normalize media entries with public URLs
            $payload['media'] = $gallery->media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'filename' => $m->filename,
                    'url' => $m->url,
                    'extension' => $m->extension,
                    'mime_type' => $m->mime_type,
                    'size' => $m->size,
                    'is_cover' => (bool) ($m->is_cover ?? false),
                    'uploaded_at' => $m->uploaded_at,
                ];
            })->toArray();

            // keep `cover` for compatibility
            $payload['cover'] = $gallery->cover ? ['id' => $gallery->cover->id, 'url' => $gallery->cover->url] : null;

            return response()->json(['message' => 'Gallery retrieved successfully', 'gallery' => $payload]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve gallery', ['exception' => $e, 'gallery_id' => $id]);
            return response()->json(['message' => 'Failed to retrieve gallery'], 404);
        }
    }

    public function update(\App\Http\Requests\Gallery\UpdateGalleryRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Prepare tags - normalize if sent as JSON string
            $tags = [];
            if ($request->has('tags')) {
                $tags = $request->input('tags');
                if (is_string($tags)) {
                    $json = json_decode($tags, true);
                    if (is_array($json)) $tags = $json;
                }
            }

            $data = [];
            if (isset($validated['title'])) $data['title'] = $validated['title'];
            if (array_key_exists('description', $validated)) $data['description'] = $validated['description'] ?? null;
            if (isset($validated['category_id'])) $data['category_id'] = $validated['category_id'];
            if (isset($validated['status'])) $data['status'] = $validated['status'];
            if (isset($validated['visibility'])) $data['visibility'] = $validated['visibility'];
            if (!empty($tags)) $data['tags'] = $tags;

            // Include optional crop coordinates so service can crop from original
            if ($request->has('crop_x') && $request->has('crop_y') && $request->has('crop_width') && $request->has('crop_height')) {
                $data['crop'] = [
                    'canvas_width' => intval($request->input('crop_canvas_width', 0)),
                    'canvas_height' => intval($request->input('crop_canvas_height', 0)),
                    'x' => intval($request->input('crop_x')),
                    'y' => intval($request->input('crop_y')),
                    'width' => intval($request->input('crop_width')),
                    'height' => intval($request->input('crop_height')),
                    'orig_width' => intval($request->input('orig_width', 0)),
                    'orig_height' => intval($request->input('orig_height', 0)),
                ];
            }

            $cover = $request->file('cover');

            $gallery = $this->galleryService->updateGallery($id, $data, $cover);

            return response()->json([
                'message' => 'Gallery updated successfully',
                'gallery' => $gallery,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update gallery', ['exception' => $e, 'id' => $id]);
            return response()->json(['message' => 'Failed to update gallery'], 500);
        }
    }

    public function setCoverMedia(int $galleryId, int $mediaId): JsonResponse
    {
        try {
            $gallery = \App\Models\Gallery::with('media')->findOrFail($galleryId);
            $media = \App\Models\Media::where('gallery_id', $galleryId)->where('id', $mediaId)->firstOrFail();

            // unset other covers for this gallery
            \App\Models\Media::where('gallery_id', $galleryId)->update(['is_cover' => false]);

            // set chosen media as cover
            $media->is_cover = true;
            $media->save();

            // return updated media list
            $mediaList = $gallery->fresh('media')->media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'filename' => $m->filename,
                    'url' => $m->url,
                    'is_cover' => (bool) ($m->is_cover ?? false),
                ];
            })->toArray();

            return response()->json(['message' => 'Cover updated', 'media' => $mediaList]);
        } catch (\Exception $e) {
            Log::warning('Failed to set gallery cover', ['exception' => $e, 'gallery_id' => $galleryId ?? null]);
            return response()->json(['message' => 'Failed to set cover'], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->galleryService->deleteGallery($id);

            return response()->json(['message' => 'Gallery deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Gallery not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete gallery', ['exception' => $e, 'id' => $id]);
            return response()->json(['message' => 'Failed to delete gallery'], 500);
        }
    }
}

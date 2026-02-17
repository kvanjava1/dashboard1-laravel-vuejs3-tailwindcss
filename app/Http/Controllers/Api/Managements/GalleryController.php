<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\StoreGalleryRequest;
use App\Services\GalleryService;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GalleryController extends Controller
{
    protected GalleryService $galleryService;

    public function __construct(GalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
    }

    /**
     * Display a listing of galleries.
     *
     * This method is written as a step-by-step process so that new contributors
     * can follow the flow easily (read params → build query → run → map).
     */
    public function index(Request $request): JsonResponse
    {
        // Step 1 — read pagination parameters (sanitize defaults)
        [$perPage, $page] = $this->getPaginationParams($request);

        // Step 2 — build base query and apply filters
        $query = Gallery::with(['tags:id,name,slug', 'cover', 'media']);
        $query = $this->applyGalleryFilters($query, $request);

        // Step 3 — execute paginated query (sorted)
        $paginated = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Step 4 — transform database models into API payload (one gallery at a time)
        $galleries = $paginated->getCollection()
            ->map(function ($gallery) {
                return $this->mapGalleryToApi($gallery);
            })->toArray();

        // Step 5 — prepare response meta and return
        $result = [
            'galleries' => $galleries,
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

    /**
     * Helpers (kept in-controller for easier reading by newcomers)
     */
    private function getPaginationParams(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $page = max(1, (int) $request->input('page', 1));

        return [$perPage, $page];
    }

    private function applyGalleryFilters($query, Request $request)
    {
        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $category = $request->input('category_id');
        if (!empty($category)) {
            $query->where('category_id', $category);
        }

        return $query;
    }

    private function mapGalleryToApi(Gallery $g): array
    {
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
            'media' => $g->media->map(function ($m) {
                return $this->mapMediaToApi($m);
            })->toArray(),
            'tags' => $g->tags->map(function ($t) {
                return ['id' => $t->id, 'name' => $t->name];
            })->toArray(),
            'created_at' => $g->created_at,
        ];
    }

    private function mapMediaToApi($m): array
    {
        return [
            'id' => $m->id,
            'filename' => $m->filename,
            'url' => $m->url,
            'extension' => $m->extension,
            'mime_type' => $m->mime_type,
            'size' => $m->size,
            // backward-compatible API field `is_cover` now reflects the selected cover (is_used_as_cover)
            'is_cover' => (bool) ($m->is_used_as_cover ?? false),
            // expose variant flag separately so clients can know which rows are cover variants
            'is_cover_variant' => (bool) ($m->is_cover ?? false),
            'uploaded_at' => $m->uploaded_at,
        ];
    }

    public function store(StoreGalleryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Prepare tags - when sent as JSON string, ensure array
        $tags = [];
        if ($request->has("tags")) {
            $tags = $request->input("tags");
            if (is_string($tags)) {
                $json = json_decode($tags, true);
                if (is_array($json)) {
                    $tags = $json;
                }
            }
        }

        $data = [
            "title" => $validated["title"],
            "description" => $validated["description"] ?? null,
            "category_id" => $validated["category_id"],
            "status" => $validated["status"],
            "visibility" => $validated["visibility"],
            "tags" => $tags,
        ];

        // Include optional crop coordinates so service can crop from original
        if (
            $request->has("crop_x") &&
            $request->has("crop_y") &&
            $request->has("crop_width") &&
            $request->has("crop_height")
        ) {
            $data["crop"] = [
                "canvas_width" => intval(
                    $request->input("crop_canvas_width", 0),
                ),
                "canvas_height" => intval(
                    $request->input("crop_canvas_height", 0),
                ),
                "x" => intval($request->input("crop_x")),
                "y" => intval($request->input("crop_y")),
                "width" => intval($request->input("crop_width")),
                "height" => intval($request->input("crop_height")),
                "orig_width" => intval($request->input("orig_width", 0)),
                "orig_height" => intval($request->input("orig_height", 0)),
            ];
        }

        $cover = $request->file("cover");

        $gallery = $this->galleryService->createGallery($data, $cover);

        return response()->json(
            [
                "message" => "Gallery created successfully",
                "gallery" => $gallery,
            ],
            201,
        );
    }

    public function show(int $id): JsonResponse
    {
        $gallery = \App\Models\Gallery::with([
            "tags:id,name,slug",
            "media",
        ])->findOrFail($id);

        $payload = $gallery->toArray();
        // normalize media entries with public URLs
        $payload["media"] = $gallery->media
            ->map(function ($m) {
                return [
                    "id" => $m->id,
                    "filename" => $m->filename,
                    "url" => $m->url,
                    "extension" => $m->extension,
                    "mime_type" => $m->mime_type,
                    "size" => $m->size,
                    "is_cover" => (bool) ($m->is_cover ?? false),
                    "uploaded_at" => $m->uploaded_at,
                ];
            })
            ->toArray();

        // keep `cover` for compatibility
        $payload["cover"] = $gallery->cover
            ? ["id" => $gallery->cover->id, "url" => $gallery->cover->url]
            : null;

        return response()->json([
            "message" => "Gallery retrieved successfully",
            "gallery" => $payload,
        ]);
    }

    public function update(
        \App\Http\Requests\Gallery\UpdateGalleryRequest $request,
        int $id,
    ): JsonResponse {
        $validated = $request->validated();

        // Prepare tags - normalize if sent as JSON string
        $tags = [];
        if ($request->has("tags")) {
            $tags = $request->input("tags");
            if (is_string($tags)) {
                $json = json_decode($tags, true);
                if (is_array($json)) {
                    $tags = $json;
                }
            }
        }

        $data = [];
        if (isset($validated["title"])) {
            $data["title"] = $validated["title"];
        }
        if (array_key_exists("description", $validated)) {
            $data["description"] = $validated["description"] ?? null;
        }
        if (isset($validated["category_id"])) {
            $data["category_id"] = $validated["category_id"];
        }
        if (isset($validated["status"])) {
            $data["status"] = $validated["status"];
        }
        if (isset($validated["visibility"])) {
            $data["visibility"] = $validated["visibility"];
        }
        if (!empty($tags)) {
            $data["tags"] = $tags;
        }

        // Include optional crop coordinates so service can crop from original
        if (
            $request->has("crop_x") &&
            $request->has("crop_y") &&
            $request->has("crop_width") &&
            $request->has("crop_height")
        ) {
            $data["crop"] = [
                "canvas_width" => intval(
                    $request->input("crop_canvas_width", 0),
                ),
                "canvas_height" => intval(
                    $request->input("crop_canvas_height", 0),
                ),
                "x" => intval($request->input("crop_x")),
                "y" => intval($request->input("crop_y")),
                "width" => intval($request->input("crop_width")),
                "height" => intval($request->input("crop_height")),
                "orig_width" => intval($request->input("orig_width", 0)),
                "orig_height" => intval($request->input("orig_height", 0)),
            ];
        }

        $cover = $request->file("cover");

        $gallery = $this->galleryService->updateGallery($id, $data, $cover);

        return response()->json([
            "message" => "Gallery updated successfully",
            "gallery" => $gallery,
        ]);
    }

    public function setCoverMedia(int $galleryId, int $mediaId): JsonResponse
    {
        try {
            $gallery = \App\Models\Gallery::with("media")->findOrFail(
                $galleryId,
            );
            $media = \App\Models\Media::where("gallery_id", $galleryId)
                ->where("id", $mediaId)
                ->firstOrFail();

            // unset selected-cover flags for this gallery (application-level single-cover guarantee)
            \App\Models\Media::where('gallery_id', $galleryId)->update([
                'is_used_as_cover' => false,
            ]);

            // set chosen media as the selected cover (keep variant flags unchanged)
            $media->is_used_as_cover = true;
            $media->save();

            // return updated media list
            $mediaList = $gallery
                ->fresh("media")
                ->media->map(function ($m) {
                    return [
                        "id" => $m->id,
                        "filename" => $m->filename,
                        "url" => $m->url,
                        "is_cover" => (bool) ($m->is_cover ?? false),
                    ];
                })
                ->toArray();

            return response()->json([
                "message" => "Cover updated",
                "media" => $mediaList,
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to set gallery cover", [
                "exception" => $e,
                "gallery_id" => $galleryId ?? null,
            ]);
            return response()->json(["message" => "Failed to set cover"], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->galleryService->deleteGallery($id);

            return response()->json([
                "message" => "Gallery deleted successfully",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Gallery not found"], 404);
        } catch (\Exception $e) {
            Log::error("Failed to delete gallery", [
                "exception" => $e,
                "id" => $id,
            ]);
            return response()->json(
                ["message" => "Failed to delete gallery"],
                500,
            );
        }
    }
}

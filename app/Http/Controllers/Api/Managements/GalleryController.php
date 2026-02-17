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
        // Delegate list + filtering + mapping to service
        $result = $this->galleryService->paginateGalleries($request->all());

        return response()->json([
            'message' => 'Galleries retrieved successfully',
            'data' => $result,
        ]);
    }



    public function store(StoreGalleryRequest $request): JsonResponse
    {
        $gallery = $this->galleryService->createGallery($request);

        return response()->json([
            'message' => 'Gallery created successfully',
            'gallery' => $gallery,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $payload = $this->galleryService->getGalleryById($id);

        return response()->json([
            'message' => 'Gallery retrieved successfully',
            'gallery' => $payload,
        ]);
    }

    public function update(\App\Http\Requests\Gallery\UpdateGalleryRequest $request, int $id): JsonResponse
    {
        $gallery = $this->galleryService->updateGallery($request, $id);

        return response()->json([
            'message' => 'Gallery updated successfully',
            'gallery' => $gallery,
        ]);
    }

    public function setCoverMedia(int $galleryId, int $mediaId): JsonResponse
    {
        try {
            $mediaList = $this->galleryService->setCoverMedia($galleryId, $mediaId);

            return response()->json([
                'message' => 'Cover updated',
                'media' => $mediaList,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to set gallery cover', [
                'exception' => $e,
                'gallery_id' => $galleryId ?? null,
            ]);
            return response()->json(['message' => 'Failed to set cover'], 400);
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

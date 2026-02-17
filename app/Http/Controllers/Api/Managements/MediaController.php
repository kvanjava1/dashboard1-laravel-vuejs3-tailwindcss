<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    /**
     * Display a listing of media (primary 1200x900 rows only).
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->mediaService->paginateMedia($request->all());

        return response()->json([
            'message' => 'Media retrieved successfully',
            'data' => $result,
        ]);
    }

    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = $this->mediaService->createMedia($request);

        return response()->json([
            'message' => 'Media created successfully',
            'media' => $media,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $payload = $this->mediaService->getMediaById($id);

        return response()->json([
            'message' => 'Media retrieved successfully',
            'media' => $payload,
        ]);
    }

    public function update(UpdateMediaRequest $request, int $id): JsonResponse
    {
        $media = $this->mediaService->updateMedia($request, $id);

        return response()->json([
            'message' => 'Media updated successfully',
            'media' => $media,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->mediaService->deleteMedia($id);

            return response()->json([
                'message' => 'Media deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Media not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete media', ['exception' => $e, 'id' => $id]);
            return response()->json(['message' => 'Failed to delete media'], 500);
        }
    }
}

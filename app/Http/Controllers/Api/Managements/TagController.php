<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\OptionsTagRequest;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    protected TagService $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Return tag suggestions for autocomplete.
     * Query param: q (string), limit: optional
     */
    public function options(OptionsTagRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $q = $validated['q'] ?? '';
            $limit = intval($validated['limit'] ?? 10);
            $limit = max(1, min(100, $limit));

            $tags = $this->tagService->getTagOptions($q, $limit);

            return response()->json(['message' => 'Tags retrieved', 'data' => $tags], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve tags'], 500);
        }
    }
}

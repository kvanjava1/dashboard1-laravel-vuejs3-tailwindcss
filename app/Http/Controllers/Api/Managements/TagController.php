<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Services\TagService;
use Illuminate\Http\Request;

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
    public function options(Request $request)
    {
        try {
            $q = $request->input('q', '');
            $limit = intval($request->input('limit', 10));

            $tags = $this->tagService->getTagOptions($q, $limit);

            return response()->json(['message' => 'Tags retrieved', 'data' => $tags], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve tags'], 500);
        }
    }
}

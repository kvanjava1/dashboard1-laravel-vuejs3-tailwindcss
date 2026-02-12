<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Return tag suggestions for autocomplete.
     * Query param: q (string), limit: optional
     */
    public function options(Request $request)
    {
        $q = $request->input('q', '');
        $limit = intval($request->input('limit', 10));

        $query = Tag::query();
        if ($q !== '') {
            $query->where('name', 'LIKE', "%{$q}%");
        }

        $tags = $query->orderBy('name')->limit($limit)->get(['id', 'name']);

        return response()->json(['message' => 'Tags retrieved', 'data' => $tags]);
    }
}

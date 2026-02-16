<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Facades\Log;

class TagService
{
    /**
     * Return simple tag options for autocomplete.
     *
     * @param string|null $q
     * @param int $limit
     * @return array
     */
    public function getTagOptions(?string $q = null, int $limit = 10): array
    {
        try {
            $query = Tag::query();

            if (!empty($q)) {
                $query->where('name', 'LIKE', "%{$q}%");
            }

            $tags = $query->orderBy('name')->limit($limit)->get(['id', 'name'])->toArray();

            return $tags;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve tag options', [
                'exception' => $e,
                'q' => $q,
                'limit' => $limit,
            ]);

            throw $e;
        }
    }
}

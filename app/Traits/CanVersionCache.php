<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait CanVersionCache
{
    /**
     * Generate a unique versioned cache key based on scope and params.
     * This handles the versioning logic automatically.
     *
     * @param string $baseKey The namespace/scope (e.g., 'categories', 'users')
     * @param array $params Filtering parameters that make the query unique
     * @return string
     */
    protected function getVersionedKey(string $baseKey, array $params): string
    {
        $versionKey = "{$baseKey}_version";
        
        // Get or init version
        $version = (int) Cache::rememberForever($versionKey, function () {
            return 1;
        });

        $paramHash = md5(json_encode($params));
        
        return "{$baseKey}_v{$version}_{$paramHash}";
    }

    /**
     * Increment the version for a specific cache scope, effectively invalidating all
     * previous entries instantly.
     *
     * @param string $baseKey The namespace/scope to invalidate
     */
    protected function clearVersionedCache(string $baseKey): void
    {
        $versionKey = "{$baseKey}_version";
        Cache::increment($versionKey);
        Log::info("Versioned cache version bumped for scope: {$baseKey}");
    }
}

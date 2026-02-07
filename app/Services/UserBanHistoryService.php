<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBanHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Traits\CanVersionCache;

class UserBanHistoryService
{
    use CanVersionCache;

    private const CACHE_SCOPE = 'users'; // Sharing user scope for synchronized invalidation
    private const CACHE_TTL = 3600;

    /**
     * Log a ban or unban action
     */
    public function logBanAction(int $userId, string $action, string $reason, $bannedUntil = null, int $performedBy = null, bool $isForever = false): void
    {
        try {
            UserBanHistory::create([
                'user_id' => $userId,
                'action' => $action,
                'reason' => $reason,
                'banned_until' => $bannedUntil,
                'is_forever' => $isForever,
                'performed_by' => $performedBy,
            ]);

            Log::info('Ban action logged successfully', [
                'user_id' => $userId,
                'action' => $action,
                'reason' => $reason,
                'banned_until' => $bannedUntil,
                'is_forever' => $isForever,
                'performed_by' => $performedBy
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log ban action', [
                'user_id' => $userId,
                'action' => $action,
                'reason' => $reason,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get user's ban history
     */
    public function getUserBanHistory(int $userId): array
    {
        try {
            // 1. Check cache first
            $cacheKey = $this->getVersionedKey(self::CACHE_SCOPE, ['type' => 'ban_history', 'user_id' => $userId]);
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }

            // 2. Fetch from database
            $history = UserBanHistory::where('user_id', $userId)
                ->with('performedBy:id,name,email')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'action' => $record->action,
                        'reason' => $record->reason,
                        'banned_until' => $record->banned_until,
                        'is_forever' => $record->is_forever,
                        'performed_by' => $record->performedBy ? [
                            'id' => $record->performedBy->id,
                            'name' => $record->performedBy->name,
                            'email' => $record->performedBy->email,
                        ] : null,
                        'created_at' => $record->created_at,
                    ];
                })
                ->toArray();

            // 3. Save to cache
            Cache::put($cacheKey, $history, self::CACHE_TTL);

            Log::info('User ban history retrieved successfully', [
                'user_id' => $userId,
                'history_count' => count($history),
                'cached' => false
            ]);

            return $history;
        } catch (\Exception $e) {
            Log::error('Failed to get user ban history', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
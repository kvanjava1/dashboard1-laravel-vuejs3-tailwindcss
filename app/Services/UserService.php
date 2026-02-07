<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Services\ProtectionService;
use App\Services\UserBanHistoryService;
use App\Traits\CanVersionCache;

class UserService
{
    use CanVersionCache;

    protected ProtectionService $protectionService;
    protected UserBanHistoryService $userBanHistoryService;

    private const CACHE_SCOPE = 'users';
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(ProtectionService $protectionService, UserBanHistoryService $userBanHistoryService)
    {
        $this->protectionService = $protectionService;
        $this->userBanHistoryService = $userBanHistoryService;
    }

    /**
     * Get filtered and paginated users with their roles
     */
    public function getFilteredPaginatedUsers(int $perPage = 15, int $page = 1, array $filters = []): array
    {
        try {
            // 1. Check cache first
            $cacheKey = $this->getVersionedKey(self::CACHE_SCOPE, array_merge(['perPage' => $perPage, 'page' => $page], $filters));
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }

            // 2. No cache, process the data
            $query = User::with([
                'roles' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.profile_image',
                    'users.is_banned',
                    'users.is_active',
                    'users.created_at',
                    'users.updated_at'
                ]);

            // Apply search filter (name or email)
            if (!empty($filters['search'])) {
                $searchTerm = $filters['search'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('users.name', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('users.email', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            // Apply individual field filters
            if (!empty($filters['name'])) {
                $query->where('users.name', 'LIKE', '%' . $filters['name'] . '%');
            }
            if (!empty($filters['email'])) {
                $query->where('users.email', 'LIKE', '%' . $filters['email'] . '%');
            }

            // Apply ban status filter
            if (isset($filters['is_banned']) && $filters['is_banned'] !== null && $filters['is_banned'] !== '') {
                $query->where('users.is_banned', $filters['is_banned']);
            }

            // Apply role filter
            if (!empty($filters['role'])) {
                $query->whereHas('roles', function ($q) use ($filters) {
                    $q->where('name', $filters['role']);
                });
            }

            // Apply status filter
            if (!empty($filters['status'])) {
                switch ($filters['status']) {
                    case 'banned':
                        $query->where('users.is_banned', true);
                        break;
                    case 'active':
                        $query->where('users.is_banned', false)->where('users.is_active', true);
                        break;
                    case 'inactive':
                        $query->where('users.is_banned', false)->where('users.is_active', false);
                        break;
                }
            }

            // Apply date range filter
            if (!empty($filters['date_from'])) {
                $query->whereDate('users.created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('users.created_at', '<=', $filters['date_to']);
            }

            // Apply sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';

            $sortFieldMap = [
                'name' => 'users.name',
                'email' => 'users.email',
                'created_at' => 'users.created_at',
                'updated_at' => 'users.updated_at',
            ];

            $actualSortField = $sortFieldMap[$sortBy] ?? 'users.created_at';
            $query->orderBy($actualSortField, $sortOrder);

            $users = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedUsers = $users->getCollection()->map(function ($user) {
                return $this->formatUserData($user);
            });

            // Get all available roles for filter dropdown
            $availableRoles = Role::select(['id', 'name'])->orderBy('name')->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $this->getRoleDisplayName($role->name),
                ];
            })->toArray();

            $availableStatuses = [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ];

            $result = [
                'users' => $formattedUsers->toArray(),
                'total' => $users->total(),
                'total_pages' => $users->lastPage(),
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
                'available_roles' => $availableRoles,
                'status_options' => $availableStatuses,
            ];

            // 3. Save cache
            Cache::put($cacheKey, $result, self::CACHE_TTL);

            // 4. Return result
            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to retrieve filtered paginated users', [
                'page' => $page,
                'per_page' => $perPage,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get a specific user by ID
     */
    public function getUserById(int $userId): array
    {
        try {
            $cacheKey = $this->getVersionedKey(self::CACHE_SCOPE, ['id' => $userId]);
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }

            $user = User::with('roles')->findOrFail($userId);
            $result = $this->formatUserData($user);

            Cache::put($cacheKey, $result, self::CACHE_TTL);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a new user
     */
    public function createUser(array $data, $request = null): array
    {
        try {
            $existingUser = User::withTrashed()->where('email', $data['email'])->first();

            if ($existingUser && $existingUser->trashed()) {
                $existingUser->restore();
                $existingUser->update([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'is_banned' => $data['is_banned'] ?? false,
                    'is_active' => $data['is_active'] ?? true,
                    'password' => bcrypt($data['password']),
                ]);
                $user = $existingUser;
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'is_banned' => $data['is_banned'] ?? false,
                    'is_active' => $data['is_active'] ?? true,
                    'password' => bcrypt($data['password']),
                ]);
            }

            if (isset($data['profile_image']) && $data['profile_image'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = $this->storeProfileImage($data['profile_image']);
                $user->update(['profile_image' => $imagePath]);
            }

            $user->syncRoles([$data['role']]);

            // Clear cache
            $this->clearVersionedCache(self::CACHE_SCOPE);
            // Also clear roles because users count might have changed
            $this->clearVersionedCache('roles');

            return $this->formatUserData($user);

        } catch (\Exception $e) {
            Log::error('Failed to create/restore user', [
                'error' => $e->getMessage(),
                'email' => $data['email']
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing user
     */
    public function updateUser(int $userId, array $data): array
    {
        try {
            $user = User::findOrFail($userId);

            if ($this->protectionService->isAccountProtectedFromRoleChange($user) && isset($data['role'])) {
                $reason = $this->protectionService->getAccountProtectionReason($user);
                $this->protectionService->throwProtectionException(
                    'Cannot modify role for protected account',
                    $reason
                );
            }

            $updateData = [];
            if (isset($data['first_name']) || isset($data['last_name'])) {
                $firstName = $data['first_name'] ?? '';
                $lastName = $data['last_name'] ?? '';
                $updateData['name'] = trim("{$firstName} {$lastName}");
            } elseif (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            if (isset($data['email']))
                $updateData['email'] = $data['email'];
            if (isset($data['phone']))
                $updateData['phone'] = $data['phone'];
            if (isset($data['is_active']))
                $updateData['is_active'] = $data['is_active'];
            if (!empty($data['password']))
                $updateData['password'] = bcrypt($data['password']);

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            if (isset($data['profile_image']) && $data['profile_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $imagePath = $this->storeProfileImage($data['profile_image']);
                $user->update(['profile_image' => $imagePath]);
            }

            $roleChanged = false;
            if (isset($data['role'])) {
                $currentRoles = $user->getRoleNames()->toArray();
                if (!in_array($data['role'], $currentRoles)) {
                    $user->syncRoles([$data['role']]);
                    $roleChanged = true;
                }
            }

            $user->load('roles');

            // Clear cache
            $this->clearVersionedCache(self::CACHE_SCOPE);
            if ($roleChanged) {
                $this->clearVersionedCache('roles');
            }

            return $this->formatUserData($user);
        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser(int $userId, int $deletedBy): bool
    {
        try {
            $user = User::findOrFail($userId);

            if ($this->protectionService->isAccountProtectedFromDeletion($user)) {
                $reason = $this->protectionService->getAccountProtectionReason($user);
                $this->protectionService->throwProtectionException(
                    'Cannot delete protected account',
                    $reason
                );
            }

            if ($user->id === $deletedBy) {
                throw new \Exception('Cannot delete your own account', 403);
            }

            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->delete();

            // Clear cache
            $this->clearVersionedCache(self::CACHE_SCOPE);
            $this->clearVersionedCache('roles');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Ban a user
     */
    public function banUser(int $userId, array $banData, int $performedBy = null): array
    {
        try {
            $user = User::findOrFail($userId);

            if ($this->protectionService->isAccountProtectedFromBan($user)) {
                $reason = $this->protectionService->getAccountProtectionReason($user);
                $this->protectionService->throwProtectionException(
                    'Cannot ban protected account',
                    $reason
                );
            }

            $banReason = $banData['reason'] ?? 'No reason provided';
            $isForever = $banData['is_forever'] ?? false;
            $bannedUntil = $banData['banned_until'] ?? null;

            if (!$isForever && !$bannedUntil) {
                $bannedUntil = now()->addDays(30);
            }

            $user->update(['is_banned' => true]);

            $this->userBanHistoryService->logBanAction($user->id, 'ban', $banReason, $bannedUntil, $performedBy, $isForever);

            $user->load('roles');

            // Clear cache
            $this->clearVersionedCache(self::CACHE_SCOPE);

            return $this->formatUserData($user);
        } catch (\Exception $e) {
            Log::error('Failed to ban user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Unban a user
     */
    public function unbanUser(int $userId, string $reason = null, int $performedBy = null): array
    {
        try {
            $user = User::findOrFail($userId);

            if (!$user->is_banned) {
                throw new \Exception('User is not currently banned');
            }

            $unbanReason = $reason ?? 'Manual unban';

            $user->update(['is_banned' => false]);

            $this->userBanHistoryService->logBanAction($user->id, 'unban', $unbanReason, null, $performedBy);

            $user->load('roles');

            // Clear cache
            $this->clearVersionedCache(self::CACHE_SCOPE);

            return $this->formatUserData($user);
        } catch (\Exception $e) {
            Log::error('Failed to unban user', [
                'user_id' => $userId,
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
            return $this->userBanHistoryService->getUserBanHistory($userId);
        } catch (\Exception $e) {
            Log::error('Failed to get user ban history', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Format user data for API response
     */
    public function formatUserData(User $user): array
    {
        $primaryRole = $user->roles->first();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->is_active ? 'active' : 'inactive',
            'profile_image' => $user->profile_image ? Storage::disk('public')->url($user->profile_image) : null,
            'role' => $primaryRole ? $primaryRole->name : null,
            'role_display_name' => $primaryRole ? $this->getRoleDisplayName($primaryRole->name) : null,
            'permissions' => $permissions,
            'is_banned' => $user->is_banned,
            'is_active' => $user->is_active,
            'protection' => [
                'can_delete' => !$this->protectionService->isAccountProtectedFromDeletion($user),
                'can_edit' => !$this->protectionService->isAccountProtectedFromProfileUpdate($user),
                'can_ban' => !$this->protectionService->isAccountProtectedFromBan($user),
                'can_change_role' => !$this->protectionService->isAccountProtectedFromRoleChange($user),
                'reason' => $this->protectionService->getAccountProtectionReason($user),
            ],
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'joined_date' => $user->created_at->format('M d, Y'),
        ];
    }

    /**
     * Get human-readable display name for role
     */
    private function getRoleDisplayName(string $roleName): string
    {
        $displayNames = [
            'super_admin' => 'Super Administrator',
            'administrator' => 'Administrator',
            'editor' => 'Editor',
            'viewer' => 'Viewer',
            'moderator' => 'Moderator',
        ];

        return $displayNames[$roleName] ?? ucwords(str_replace('_', ' ', $roleName));
    }

    /**
     * Store profile image with custom path pattern
     */
    private function storeProfileImage(\Illuminate\Http\UploadedFile $file): string
    {
        $uniqueName = uniqid() . '_' . time() . '.webp';
        $year = date('Y');
        $month = date('m');
        $day = date('d');

        $directory = "avatar/{$year}/{$month}/{$day}";
        $fullPath = "{$directory}/{$uniqueName}";

        Storage::disk('public')->makeDirectory($directory);
        $imageContent = $this->convertToWebp($file);
        Storage::disk('public')->put($fullPath, $imageContent);

        return $fullPath;
    }

    /**
     * Convert uploaded image to WebP format
     */
    private function convertToWebp(\Illuminate\Http\UploadedFile $file): string
    {
        $imageContent = file_get_contents($file->getRealPath());
        if (function_exists('imagewebp') && function_exists('imagecreatefromstring')) {
            $image = imagecreatefromstring($imageContent);
            if ($image !== false) {
                ob_start();
                imagewebp($image, null, 80);
                $webpContent = ob_get_clean();
                imagedestroy($image);
                return $webpContent;
            }
        }
        return $imageContent;
    }

    private function getBanStatusText(User $user): string
    {
        if (!$user->is_banned) {
            return 'Not Banned';
        }
        return 'Permanently Banned';
    }

    /**
     * Manually clear the user cache.
     */
    public function clearCache(): void
    {
        $this->clearVersionedCache(self::CACHE_SCOPE);
    }
}
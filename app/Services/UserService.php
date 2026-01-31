<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\ProtectionService;
use App\Services\UserBanHistoryService;
use Carbon\Carbon;

class UserService
{
    protected ProtectionService $protectionService;
    protected UserBanHistoryService $userBanHistoryService;

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
            // Start building the query
            $query = User::with('roles')
                ->select('users.*');

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
            if (!empty($filters['phone'])) {
                $query->where('users.phone', 'LIKE', '%' . $filters['phone'] . '%');
            }
            if (!empty($filters['username'])) {
                $query->where('users.username', 'LIKE', '%' . $filters['username'] . '%');
            }
            if (!empty($filters['location'])) {
                $query->where('users.location', 'LIKE', '%' . $filters['location'] . '%');
            }
            if (!empty($filters['bio'])) {
                $query->where('users.bio', 'LIKE', '%' . $filters['bio'] . '%');
            }
            if (!empty($filters['timezone'])) {
                $query->where('users.timezone', 'LIKE', '%' . $filters['timezone'] . '%');
            }

            // Apply ban status filter
            if ($filters['is_banned'] !== null && $filters['is_banned'] !== '') {
                $query->where('users.is_banned', $filters['is_banned']);
            }

            // Apply date of birth range filter
            if (!empty($filters['date_of_birth_from'])) {
                $query->whereDate('users.date_of_birth', '>=', $filters['date_of_birth_from']);
            }
            if (!empty($filters['date_of_birth_to'])) {
                $query->whereDate('users.date_of_birth', '<=', $filters['date_of_birth_to']);
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
            
            // Map sort fields to actual database columns
            $sortFieldMap = [
                'name' => 'users.name',
                'email' => 'users.email',
                'username' => 'users.username',
                'phone' => 'users.phone',
                'location' => 'users.location',
                'date_of_birth' => 'users.date_of_birth',
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

            Log::info('Filtered paginated users retrieved successfully', [
                'page' => $page,
                'per_page' => $perPage,
                'filters' => $filters,
                'total' => $users->total(),
                'count' => $formattedUsers->count()
            ]);

            // Get all available account statuses
            $availableStatuses = [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'banned', 'label' => 'Banned'],
            ];

            return [
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
            $user = User::with('roles')->findOrFail($userId);

            $userData = $this->formatUserData($user);

            Log::info('User retrieved successfully', ['user_id' => $userId, 'email' => $user->email]);

            return $userData;
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
            // Check if there's a soft deleted user with this email
            $existingUser = User::withTrashed()->where('email', $data['email'])->first();

            if ($existingUser && $existingUser->trashed()) {
                // Restore the soft deleted user
                $existingUser->restore();
                
                // Update the user with new data
                $existingUser->update([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'is_banned' => $data['is_banned'] ?? false,
                    'is_active' => $data['is_active'] ?? true,
                    'password' => bcrypt($data['password']),
                    'bio' => $data['bio'] ?? null,
                ]);

                $user = $existingUser;

                Log::info('Soft deleted user restored and updated', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'restored_by' => $data['created_by'] ?? 'system'
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'is_banned' => $data['is_banned'] ?? false,
                    'is_active' => $data['is_active'] ?? true,
                    'password' => bcrypt($data['password']),
                    'bio' => $data['bio'] ?? null,
                ]);

                Log::info('New user created', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'created_by' => $data['created_by'] ?? 'system'
                ]);
            }

            // Handle profile image upload (for both restored and new users)
            if (isset($data['profile_image']) && $data['profile_image'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = $data['profile_image']->store('avatars', 'public');
                $user->update(['profile_image' => $imagePath]);
            }

            // Assign role using Spatie Permission (re-assign for restored users, assign for new)
            $user->syncRoles([$data['role']]); // Use syncRoles to replace existing roles

            // Log the final action
            Log::info('User creation/restore completed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $data['role'],
                'action' => $existingUser && $existingUser->trashed() ? 'restored' : 'created',
                'created_by' => $data['created_by'] ?? 'system'
            ]);

            // Return formatted user data
            $nameParts = explode(' ', $data['name'], 2);
            return [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
                'email' => $user->email,
                'phone' => $data['phone'] ?? null,
                'profile_image' => $user->profile_image_url,
                'bio' => $data['bio'] ?? null,
                'role' => $data['role'],
                'is_banned' => $user->is_banned,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
                'restored' => $existingUser && $existingUser->trashed(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create/restore user', [
                'error' => $e->getMessage(),
                'email' => $data['email'],
                'created_by' => $data['created_by'] ?? 'system'
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

            // Check if account is protected from profile updates
            if ($this->protectionService->isAccountProtectedFromProfileUpdate($user)) {
                $updateData = [];
                if (!empty($data['password'])) {
                    $updateData['password'] = bcrypt($data['password']);
                }

                // Handle profile image upload
                if (isset($data['profile_image']) && $data['profile_image'] instanceof \Illuminate\Http\UploadedFile) {
                    // Delete old image if exists
                    if ($user->profile_image) {
                        Storage::disk('public')->delete($user->profile_image);
                    }
                    $imagePath = $data['profile_image']->store('avatars', 'public');
                    $updateData['profile_image'] = $imagePath;
                }

                if (!empty($updateData)) {
                    $user->update($updateData);
                }

                // Reload with roles
                $user->load('roles');
                Log::info('Protected account updated (limited fields only)', [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'updated_by' => $data['updated_by'] ?? 'system',
                    'reason' => $this->protectionService->getAccountProtectionReason($user)
                ]);
                return $this->formatUserData($user);
            }

            // Check if account is protected from role changes
            if ($this->protectionService->isAccountProtectedFromRoleChange($user) && isset($data['role'])) {
                $reason = $this->protectionService->getAccountProtectionReason($user);
                $this->protectionService->throwProtectionException(
                    'Cannot modify role for protected account',
                    $reason
                );
            }

            // Update basic info
            $updateData = [];
            // Handle name update from first_name and last_name
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
            if (isset($data['is_banned']))
                $updateData['is_banned'] = $data['is_banned'];
            if (isset($data['is_active']))
                $updateData['is_active'] = $data['is_active'];
            if (isset($data['bio']))
                $updateData['bio'] = $data['bio'];
            // Update password if provided
            if (!empty($data['password'])) {
                $updateData['password'] = bcrypt($data['password']);
            }
            if (!empty($updateData)) {
                $user->update($updateData);
            }
            // Handle profile image upload
            if (isset($data['profile_image']) && $data['profile_image'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $imagePath = $data['profile_image']->store('avatars', 'public');
                $user->update(['profile_image' => $imagePath]);
            }
            // Update role if provided
            if (isset($data['role'])) {
                $user->syncRoles([$data['role']]);
            }
            // Reload with roles
            $user->load('roles');
            Log::info('User updated successfully', [
                'user_id' => $userId,
                'email' => $user->email,
                'updated_by' => $data['updated_by'] ?? 'system'
            ]);
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

            // Check if account is protected from deletion
            if ($this->protectionService->isAccountProtectedFromDeletion($user)) {
                $reason = $this->protectionService->getAccountProtectionReason($user);
                $this->protectionService->throwProtectionException(
                    'Cannot delete protected account',
                    $reason
                );
            }

            // Prevent self-deletion
            if ($user->id === $deletedBy) {
                throw new \Exception('Cannot delete your own account', 403);
            }

            // Delete profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }


            $userEmail = $user->email;
            // Soft delete user
            $user->delete();

            Log::info('User deleted successfully', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'deleted_by' => $deletedBy
            ]);

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

            // Check if account is protected from banning
            if ($this->protectionService->isAccountProtectedFromBan($user)) {
                $reason = $this->protectionService->getAccountProtectionReason($user);
                $this->protectionService->throwProtectionException(
                    'Cannot ban protected account',
                    $reason
                );
            }

            $banReason = $banData['reason'] ?? 'No reason provided';

            // Get the banned status
            $bannedStatus = UserAccountStatus::where('name', 'banned')->first();
            if (!$bannedStatus) {
                throw new \Exception('Banned status not found in database');
            }

            // Update user account status to banned
            $user->update([
                'is_banned' => true,
            ]);

            // Log the ban action
            $this->userBanHistoryService->logBanAction(
                $user->id,
                'ban',
                $banReason,
                null, // No longer tracking banned_until
                $performedBy
            );

            // Reload user with relationships
            $user->load('roles');

            Log::info('User banned successfully', [
                'user_id' => $userId,
                'email' => $user->email,
                'reason' => $banReason,
                'performed_by' => $performedBy
            ]);

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

            // Check if user is actually banned
            if (!$user->is_banned) {
                throw new \Exception('User is not currently banned');
            }

            $unbanReason = $reason ?? 'Manual unban';

            // Update user account status to active
            $user->update([
                'is_banned' => false,
            ]);

            // Log the unban action
            $this->userBanHistoryService->logBanAction(
                $user->id,
                'unban',
                $unbanReason,
                null,
                $performedBy
            );

            // Reload user with relationships
            $user->load('roles');

            Log::info('User unbanned successfully', [
                'user_id' => $userId,
                'email' => $user->email,
                'reason' => $unbanReason,
                'performed_by' => $performedBy
            ]);

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
    private function formatUserData(User $user): array
    {
        $primaryRole = $user->roles->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'profile_image' => $user->profile_image,
            'profile_image_url' => $user->profile_image_url,
            'role' => $primaryRole ? $primaryRole->name : null,
            'role_display_name' => $primaryRole ? $this->getRoleDisplayName($primaryRole->name) : null,
            'is_banned' => $user->is_banned,
            'is_active' => $user->is_active,
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
     * Get human-readable ban status text
     */
    private function getBanStatusText(User $user): string
    {
        if (!$user->is_banned) {
            return 'Not Banned';
        }

        // All bans are now permanent (no banned_until tracking)
        return 'Permanently Banned';
    }
}
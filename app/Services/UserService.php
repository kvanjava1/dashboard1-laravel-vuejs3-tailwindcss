<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserService
{
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

            // Apply role filter
            if (!empty($filters['role'])) {
                $query->whereHas('roles', function ($q) use ($filters) {
                    $q->where('name', $filters['role']);
                });
            }

            // Apply status filter
            if (!empty($filters['status'])) {
                $query->where('users.status', $filters['status']);
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
            $query->orderBy($sortBy, $sortOrder);

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

            return [
                'users' => $formattedUsers->toArray(),
                'total' => $users->total(),
                'total_pages' => $users->lastPage(),
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
                'available_roles' => $availableRoles,
                'status_options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                    ['value' => 'pending', 'label' => 'Pending'],
                ]
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
    public function createUser(array $data): array
    {
        try {
            // Create the user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'],
                'password' => bcrypt($data['password']),
                'bio' => $data['bio'] ?? null,
            ]);

            // Handle profile image upload
            if (isset($data['profile_image']) && $data['profile_image'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = $data['profile_image']->store('avatars', 'public');
                $user->update(['profile_image' => $imagePath]);
            }

            // Assign role using Spatie Permission
            if (isset($data['role'])) {
                $user->assignRole($data['role']);
            }

            // Reload with roles
            $user->load('roles');

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $data['role'] ?? null,
            ]);

            return $this->formatUserData($user);
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage()
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

            // Restrict super_admin: only password can be changed
            if ($user->hasRole('super_admin') || $user->email === 'super@admin.com') {
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
                Log::info('Super admin password updated', [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'updated_by' => $data['updated_by'] ?? 'system'
                ]);
                return $this->formatUserData($user);
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
            if (isset($data['status']))
                $updateData['status'] = $data['status'];
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

            // Prevent deletion of super admin users by email or role
            if ($user->hasRole('super_admin') && $user->email === 'super@admin.com') {
                throw new \Exception('Cannot delete super admin users', 403);
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
     * Format user data for API response
     */
    private function formatUserData(User $user): array
    {
        $primaryRole = $user->roles->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone,
            'status' => $user->status,
            'bio' => $user->bio,
            'date_of_birth' => $user->date_of_birth,
            'location' => $user->location,
            'timezone' => $user->timezone,
            'last_activity' => $user->last_activity,
            'is_banned' => $user->is_banned,
            'ban_reason' => $user->ban_reason,
            'banned_until' => $user->banned_until,
            'profile_image' => $user->profile_image,
            'profile_image_url' => $user->profile_image_url,
            'role' => $primaryRole ? $primaryRole->name : null,
            'role_display_name' => $primaryRole ? $this->getRoleDisplayName($primaryRole->name) : null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'joined_date' => $user->created_at->format('M d, Y'),
            'last_activity_formatted' => $user->last_activity ? $user->last_activity->format('M d, Y H:i') : null,
            'banned_until_formatted' => $user->banned_until ? $user->banned_until->format('M d, Y H:i') : null,
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
}
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'is_banned',
        'is_active',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get the user's profile image URL
     */
    public function getProfileImageUrlAttribute(): string
    {
        return $this->profile_image
            ? asset('storage/' . $this->profile_image)
            : "";
    }

    /**
     * Check if user is a forum member
     */
    public function isForumMember(): bool
    {
        return $this->hasAnyRole(['forum_member', 'administrator', 'moderator', 'editor']);
    }

    /**
     * Check if user can moderate
     */
    public function canModerate(): bool
    {
        return $this->hasAnyRole(['administrator', 'moderator']);
    }

    /**
     * Get the status attribute (computed from boolean fields)
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_banned) {
            return 'banned';
        }
        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * Check if the user is currently banned
     */
    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    /**
     * Check if the user has a temporary ban that has expired
     */
    public function hasExpiredBan(): bool
    {
        // Since we no longer track banned_until, this is not applicable
        // All bans are now permanent until manually unbanned
        return false;
    }

    /**
     * Check if the user has a permanent ban
     */
    public function isPermanentlyBanned(): bool
    {
        return $this->is_banned;
    }

    /**
     * Check if the user has a temporary ban
     */
    public function isTemporarilyBanned(): bool
    {
        // All bans are permanent
        return false;
    }
}

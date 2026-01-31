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
        'phone',
        'user_account_status_id',
        'password',
        'profile_image',
        'username',
        'bio',
        'date_of_birth',
        'location',
        'timezone',
        'is_banned',
        'ban_reason',
        'banned_until',
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
     * Get the user's account status.
     */
    public function accountStatus()
    {
        return $this->belongsTo(UserAccountStatus::class, 'user_account_status_id');
    }

    /**
     * Get the status attribute (computed from relationship)
     */
    public function getStatusAttribute(): string
    {
        return $this->accountStatus ? $this->accountStatus->name : 'unknown';
    }

    /**
     * Check if the user is currently banned
     */
    public function isBanned(): bool
    {
        return $this->is_banned && ($this->banned_until === null || $this->banned_until->isFuture());
    }

    /**
     * Check if the user has a temporary ban that has expired
     */
    public function hasExpiredBan(): bool
    {
        return $this->is_banned && $this->banned_until !== null && $this->banned_until->isPast();
    }

    /**
     * Check if the user has a permanent ban
     */
    public function isPermanentlyBanned(): bool
    {
        return $this->is_banned && $this->banned_until === null;
    }

    /**
     * Check if the user has a temporary ban
     */
    public function isTemporarilyBanned(): bool
    {
        return $this->is_banned && $this->banned_until !== null && $this->banned_until->isFuture();
    }
}

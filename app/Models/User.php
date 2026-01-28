<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\SoftDeletes;

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
        'status',
        'password',
        'profile_image',
        'username',
        'bio',
        'date_of_birth',
        'location',
        'is_banned',
        'ban_reason',
        'banned_until',
        'timezone',
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
            'banned_until' => 'datetime',
            'is_banned' => 'boolean',
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
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        if (!$this->is_banned) {
            return false;
        }

        // Check if ban has expired
        if ($this->banned_until && $this->banned_until->isPast()) {
            $this->update(['is_banned' => false, 'ban_reason' => null, 'banned_until' => null]);
            return false;
        }

        return true;
    }
}

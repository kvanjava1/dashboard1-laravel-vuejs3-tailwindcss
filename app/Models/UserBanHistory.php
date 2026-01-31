<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBanHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'reason',
        'banned_until',
        'performed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'banned_until' => 'datetime',
        ];
    }

    /**
     * Get the user that was banned/unbanned.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who performed the ban/unban action.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope to get only ban actions
     */
    public function scopeBans($query)
    {
        return $query->where('action', 'ban');
    }

    /**
     * Scope to get only unban actions
     */
    public function scopeUnbans($query)
    {
        return $query->where('action', 'unban');
    }

    /**
     * Scope to get recent actions
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
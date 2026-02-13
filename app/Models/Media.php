<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
        'filename',
        'extension',
        'mime_type',
        'size',
        'alt_text',
        'sort_order',
        'uploaded_at',
        'is_cover',
    ];

    protected $casts = [
        'size' => 'integer',
        'sort_order' => 'integer',
        'uploaded_at' => 'datetime',
        'is_cover' => 'boolean',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    /**
     * Get full public URL for this media record (points to 1200x900 by default)
     */
    public function getUrlAttribute(): string
    {
        // If filename is already a public path (relative to storage/app/public), return full URL
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->filename);
    }
}

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
    ];

    protected $casts = [
        'size' => 'integer',
        'sort_order' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}

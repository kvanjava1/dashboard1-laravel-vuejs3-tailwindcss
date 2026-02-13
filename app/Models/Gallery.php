<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category_id',
        'is_active',
        'is_public',
        'item_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'item_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function cover(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        // The "cover" is the media row flagged as is_cover for this gallery
        return $this->hasOne(Media::class)->where('is_cover', true);
    }

    /**
     * Tags associated with the gallery (many-to-many)
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'gallery_tag');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Galleries that belong to this tag
     */
    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(Gallery::class, 'gallery_tag');
    }
}

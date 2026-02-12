<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\Media;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GalleryService
{
    // Storage base folder
    private const BASE_PATH = 'uploads/gallery';

    public function __construct()
    {
        // prefer Imagick when available
        Image::configure(['driver' => extension_loaded('imagick') ? 'imagick' : 'gd']);
    }

    /**
     * Create a gallery with processed cover image and tags.
     */
    public function createGallery(array $data, ?\Illuminate\Http\UploadedFile $coverFile = null): array
    {
        return DB::transaction(function () use ($data, $coverFile) {
            // Generate unique slug
            $slug = $this->generateUniqueSlug($data['title']);

            $gallery = Gallery::create([
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'is_active' => ($data['status'] ?? 'active') === 'active',
                'is_public' => ($data['visibility'] ?? 'public') === 'public',
                'item_count' => 0,
            ]);

            // Process cover if provided
            if ($coverFile) {
                $media = $this->processAndStoreCover($gallery, $coverFile);
                $gallery->cover_id = $media->id;
                $gallery->save();
            }

            // Handle tags (create if missing)
            if (!empty($data['tags']) && is_array($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    $name = trim($tagName);
                    if (!$name) continue;
                    $slugTag = Str::slug($name);
                    $tag = Tag::firstOrCreate(['slug' => $slugTag], ['name' => $name, 'slug' => $slugTag]);
                    $tagIds[] = $tag->id;
                }
                if (!empty($tagIds)) {
                    $gallery->tags()->sync($tagIds);
                }
            }

            $gallery->load(['tags:id,name,slug', 'cover']);

            return $gallery->toArray();
        });
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: Str::slug(substr($title, 0, 50));
        $slug = $base;
        $i = 2;
        while (Gallery::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }

    /**
     * Process the cover into multiple sizes and store, returns Media model
     */
    private function processAndStoreCover(Gallery $gallery, \Illuminate\Http\UploadedFile $file): Media
    {
        try {
            $datePath = now()->format('Y/m/d');
            $slug = $gallery->slug;

            // Prepare filename base
            $unique = uniqid();
            $filenameBase = "{$unique}.webp";

            // Prepare paths
            $path1200 = self::BASE_PATH . "/{$slug}/covers/1200x900/{$datePath}/{$filenameBase}";
            $path400 = self::BASE_PATH . "/{$slug}/covers/400x400/{$datePath}/{$filenameBase}";

            // Create directories
            Storage::disk('public')->makeDirectory(self::BASE_PATH . "/{$slug}/covers/1200x900/{$datePath}");
            Storage::disk('public')->makeDirectory(self::BASE_PATH . "/{$slug}/covers/400x400/{$datePath}");

            // Load image
            $img = Image::make($file->getRealPath());

            // 1200x900 (crop center)
            $img1200 = clone $img;
            $img1200->fit(1200, 900, function ($constraint) {
                $constraint->upsize();
            });
            $webp1200 = (string) $img1200->encode('webp', 80);
            Storage::disk('public')->put($path1200, $webp1200);

            // 400x400 (crop center)
            $img400 = clone $img;
            $img400->fit(400, 400, function ($constraint) {
                $constraint->upsize();
            });
            $webp400 = (string) $img400->encode('webp', 80);
            Storage::disk('public')->put($path400, $webp400);

            // Persist media record pointing to primary (1200) file
            $size = Storage::disk('public')->size($path1200);

            $media = Media::create([
                'gallery_id' => $gallery->id,
                'filename' => $path1200,
                'extension' => 'webp',
                'mime_type' => 'image/webp',
                'size' => $size,
                'alt_text' => $gallery->title,
                'sort_order' => 0,
            ]);

            return $media;
        } catch (\Exception $e) {
            Log::error('Failed to process cover image', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

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

            // Process cover if provided. Pass optional crop coordinates from $data['crop'] (server-side crop-from-original)
            if ($coverFile) {
                $crop = $data['crop'] ?? null;
                // process images and create media rows (original + variants). The primary variant is flagged is_cover.
                $this->processAndStoreCover($gallery, $coverFile, $crop);
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

    /**
     * Update an existing gallery. If a new cover is provided it will replace the current cover
     * (existing media cover flags are cleared). Tags are synced if present in the payload.
     */
    public function updateGallery(int $galleryId, array $data, ?\Illuminate\Http\UploadedFile $coverFile = null): array
    {
        return DB::transaction(function () use ($galleryId, $data, $coverFile) {
            $gallery = Gallery::findOrFail($galleryId);

            // Update mutable fields (do not change slug to preserve URLs)
            if (isset($data['title'])) $gallery->title = $data['title'];
            if (array_key_exists('description', $data)) $gallery->description = $data['description'] ?? null;
            if (isset($data['category_id'])) $gallery->category_id = $data['category_id'];
            if (isset($data['status'])) $gallery->is_active = ($data['status'] === 'active');
            if (isset($data['visibility'])) $gallery->is_public = ($data['visibility'] === 'public');

            $gallery->save();

            // Tags: if provided, sync; if omitted, leave unchanged
            if (array_key_exists('tags', $data) && is_array($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    $name = trim($tagName);
                    if (!$name) continue;
                    $slugTag = Str::slug($name);
                    $tag = Tag::firstOrCreate(['slug' => $slugTag], ['name' => $name, 'slug' => $slugTag]);
                    $tagIds[] = $tag->id;
                }
                $gallery->tags()->sync($tagIds);
            }

            // If a new cover file is provided, clear existing cover flags and store new variants
            if ($coverFile) {
                Media::where('gallery_id', $gallery->id)->update(['is_cover' => false]);
                $crop = $data['crop'] ?? null;
                $this->processAndStoreCover($gallery, $coverFile, $crop);
            }

            $gallery->load(['tags:id,name,slug', 'cover', 'media']);

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
    private function processAndStoreCover(Gallery $gallery, \Illuminate\Http\UploadedFile $file, ?array $crop = null): Media
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
            Storage::disk('public')->makeDirectory(self::BASE_PATH . "/{$slug}/originals/{$datePath}");

            // Persist the original upload so we can reprocess later (keeps original extension)
            try {
                $origExt = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)) ?: 'jpg';
                $origPath = self::BASE_PATH . "/{$slug}/originals/{$datePath}/{$unique}_original.{$origExt}";
                // move/store original file
                Storage::disk('public')->putFileAs(self::BASE_PATH . "/{$slug}/originals/{$datePath}", $file, "{$unique}_original.{$origExt}");
            } catch (\Exception $e) {
                // non-fatal: log and continue
                Log::warning('Failed to store original upload', ['error' => $e->getMessage()]);
            }

            // Load original image
            $img = Image::make($file->getRealPath());

            // If crop coordinates provided, map them to original pixels and crop first
            if (is_array($crop) && !empty($crop['width']) && !empty($crop['height'])) {
                $canvasW = intval($crop['canvas_width'] ?? 0);
                $canvasH = intval($crop['canvas_height'] ?? 0);
                $origW = intval($crop['orig_width'] ?? $img->width());
                $origH = intval($crop['orig_height'] ?? $img->height());

                // If the uploaded file DOES NOT match the provided original dimensions,
                // it likely means the client already performed the crop and uploaded
                // a pre-cropped/resized image. In that case we MUST NOT attempt to map
                // the client coordinates back onto the uploaded image (would double-crop).
                if ($img->width() !== $origW || $img->height() !== $origH) {
                    Log::info('Uploaded image dimensions differ from orig_width/orig_height; skipping server-side crop (assuming client pre-cropped).', [
                        'uploaded_w' => $img->width(),
                        'uploaded_h' => $img->height(),
                        'orig_w' => $origW,
                        'orig_h' => $origH,
                    ]);
                } else {
                    // Calculate scale factors (avoid division by zero)
                    $scaleX = $canvasW > 0 ? ($origW / $canvasW) : 1;
                    $scaleY = $canvasH > 0 ? ($origH / $canvasH) : 1;

                    $cx = max(0, intval(round(($crop['x'] ?? 0) * $scaleX)));
                    $cy = max(0, intval(round(($crop['y'] ?? 0) * $scaleY)));
                    $cw = max(1, intval(round(($crop['width'] ?? $canvasW) * $scaleX)));
                    $ch = max(1, intval(round(($crop['height'] ?? $canvasH) * $scaleY)));

                    // Clamp
                    $cx = min($cx, $img->width() - 1);
                    $cy = min($cy, $img->height() - 1);
                    $cw = min($cw, $img->width() - $cx);
                    $ch = min($ch, $img->height() - $cy);

                    try {
                        $img->crop($cw, $ch, $cx, $cy);
                    } catch (\Exception $e) {
                        Log::warning('Failed to crop original with provided coordinates', ['error' => $e->getMessage(), 'crop' => $crop]);
                    }
                }
            }

            // Produce high-quality variants from (possibly cropped) original
            $img1200 = clone $img;
            $img1200->fit(1200, 900);
            $webp1200 = (string) $img1200->encode('webp', 90);
            Storage::disk('public')->put($path1200, $webp1200);

            $img400 = clone $img;
            $img400->fit(400, 400);
            $webp400 = (string) $img400->encode('webp', 90);
            Storage::disk('public')->put($path400, $webp400);

            // Persist media record pointing to primary (1200) file
            $size = Storage::disk('public')->size($path1200);

            // Create media records for original + variants and mark the primary (1200x900) as the gallery cover
            $createdMedia = [];

            // Original (keep original extension)
            try {
                if (isset($origPath)) {
                    $origSize = Storage::disk('public')->exists($origPath) ? Storage::disk('public')->size($origPath) : 0;
                    $createdMedia[] = Media::create([
                        'gallery_id' => $gallery->id,
                        'filename' => $origPath ?? null,
                        'extension' => $origExt ?? 'jpg',
                        'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                        'size' => $origSize,
                        'alt_text' => $gallery->title,
                        'sort_order' => 0,
                        'is_cover' => false,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to create media record for original', ['error' => $e->getMessage()]);
            }

            // 1200x900 — primary cover
            $size1200 = Storage::disk('public')->size($path1200);
            $media1200 = Media::create([
                'gallery_id' => $gallery->id,
                'filename' => $path1200,
                'extension' => 'webp',
                'mime_type' => 'image/webp',
                'size' => $size1200,
                'alt_text' => $gallery->title,
                'sort_order' => 0,
                'is_cover' => true,
            ]);
            $createdMedia[] = $media1200;

            // 400x400 thumbnail - also marked as cover variant
            $size400 = Storage::disk('public')->size($path400);
            $media400 = Media::create([
                'gallery_id' => $gallery->id,
                'filename' => $path400,
                'extension' => 'webp',
                'mime_type' => 'image/webp',
                'size' => $size400,
                'alt_text' => $gallery->title,
                'sort_order' => 0,
                'is_cover' => true,
            ]);
            $createdMedia[] = $media400;

            // Return the primary cover media (1200x900)
            return $media1200;
        } catch (\Exception $e) {
            Log::error('Failed to process cover image', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

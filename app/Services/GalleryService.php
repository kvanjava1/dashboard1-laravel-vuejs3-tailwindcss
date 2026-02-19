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
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Gallery\StoreGalleryRequest;
use App\Http\Requests\Gallery\UpdateGalleryRequest;
use App\Traits\CanVersionCache;

class GalleryService
{
    use CanVersionCache;

    // Storage base folder for gallery cover images
    private const BASE_COVER_PATH = 'upload/images/cover';
    private const CACHE_SCOPE = 'galleries';
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct()
    {
        // prefer Imagick when available
        Image::configure(['driver' => extension_loaded('imagick') ? 'imagick' : 'gd']);
    }

    /**
     * Core: create a gallery from a plain data array (business logic).
     */
    public function processCreateGallery(array $data, ?UploadedFile $coverFile = null): array
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
                    if (!$name)
                        continue;
                    $slugTag = Str::slug($name);
                    $tag = Tag::firstOrCreate(['slug' => $slugTag], ['name' => $name, 'slug' => $slugTag]);
                    $tagIds[] = $tag->id;
                }
                if (!empty($tagIds)) {
                    $gallery->tags()->sync($tagIds);
                }
            }

            $gallery->load(['tags:id,name,slug', 'cover']);

            // Clear gallery cache after creation
            $this->clearVersionedCache(self::CACHE_SCOPE);

            return $gallery->toArray();
        });
    }

    /**
     * Accept a validated StoreGalleryRequest, normalize inputs and create the gallery.
     */
    /**
     * Request adapter: create a gallery from a StoreGalleryRequest.
     * Controller should call this (`createGallery`) when handling HTTP requests.
     */
    public function createGallery(StoreGalleryRequest $request): array
    {
        $validated = $request->validated();

        // Normalize tags (accept JSON string or array)
        $tags = [];
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            if (is_string($tags)) {
                $json = json_decode($tags, true);
                if (is_array($json)) {
                    $tags = $json;
                }
            }
        }

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
            'visibility' => $validated['visibility'],
            'tags' => $tags,
        ];

        // Optional crop parameters (map to internal `crop` structure)
        if (
            $request->has('crop_x') &&
            $request->has('crop_y') &&
            $request->has('crop_width') &&
            $request->has('crop_height')
        ) {
            $data['crop'] = [
                'canvas_width' => intval($request->input('crop_canvas_width', 0)),
                'canvas_height' => intval($request->input('crop_canvas_height', 0)),
                'x' => intval($request->input('crop_x')),
                'y' => intval($request->input('crop_y')),
                'width' => intval($request->input('crop_width')),
                'height' => intval($request->input('crop_height')),
                'orig_width' => intval($request->input('orig_width', 0)),
                'orig_height' => intval($request->input('orig_height', 0)),
            ];
        }

        $cover = $request->file('cover');

        return $this->processCreateGallery($data, $cover);
    }



    /**
     * HTTP adapter: update a gallery from an UpdateGalleryRequest.
     * Controller should call this (`updateGallery`) when handling HTTP requests.
     */
    public function updateGallery(UpdateGalleryRequest $request, int $galleryId): array
    {
        $validated = $request->validated();

        $data = [];
        if (isset($validated['title'])) {
            $data['title'] = $validated['title'];
        }
        if (array_key_exists('description', $validated)) {
            $data['description'] = $validated['description'] ?? null;
        }
        if (isset($validated['category_id'])) {
            $data['category_id'] = $validated['category_id'];
        }
        if (isset($validated['status'])) {
            $data['status'] = $validated['status'];
        }
        if (isset($validated['visibility'])) {
            $data['visibility'] = $validated['visibility'];
        }

        // Normalize tags if provided
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            if (is_string($tags)) {
                $json = json_decode($tags, true);
                if (is_array($json)) {
                    $tags = $json;
                }
            }
            if (is_array($tags)) {
                $data['tags'] = $tags;
            }
        }

        // Optional crop
        if (
            $request->has('crop_x') &&
            $request->has('crop_y') &&
            $request->has('crop_width') &&
            $request->has('crop_height')
        ) {
            $data['crop'] = [
                'canvas_width' => intval($request->input('crop_canvas_width', 0)),
                'canvas_height' => intval($request->input('crop_canvas_height', 0)),
                'x' => intval($request->input('crop_x')),
                'y' => intval($request->input('crop_y')),
                'width' => intval($request->input('crop_width')),
                'height' => intval($request->input('crop_height')),
                'orig_width' => intval($request->input('orig_width', 0)),
                'orig_height' => intval($request->input('orig_height', 0)),
            ];
        }

        $cover = $request->file('cover');

        return $this->processUpdateGallery($galleryId, $data, $cover);
    }
    /**
     * Update an existing gallery from an array of data (core business logic).
     * If a new cover is provided it will replace the current cover (existing media cover flags are cleared).
     */
    public function processUpdateGallery(int $galleryId, array $data, ?UploadedFile $coverFile = null): array
    {
        return DB::transaction(function () use ($galleryId, $data, $coverFile) {
            $gallery = Gallery::findOrFail($galleryId);

            // Update mutable fields (do not change slug to preserve URLs)
            if (isset($data['title']))
                $gallery->title = $data['title'];
            if (array_key_exists('description', $data))
                $gallery->description = $data['description'] ?? null;
            if (isset($data['category_id']))
                $gallery->category_id = $data['category_id'];
            if (isset($data['status']))
                $gallery->is_active = ($data['status'] === 'active');
            if (isset($data['visibility']))
                $gallery->is_public = ($data['visibility'] === 'public');

            $gallery->save();

            // Tags: if provided, sync; if omitted, leave unchanged
            if (array_key_exists('tags', $data) && is_array($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    $name = trim($tagName);
                    if (!$name)
                        continue;
                    $slugTag = Str::slug($name);
                    $tag = Tag::firstOrCreate(['slug' => $slugTag], ['name' => $name, 'slug' => $slugTag]);
                    $tagIds[] = $tag->id;
                }
                $gallery->tags()->sync($tagIds);
            }

            // If a new cover file is provided, clear existing cover flags and store new variants
            if ($coverFile) {
                // clear the currently selected cover and replace with the newly-uploaded cover
                Media::where('gallery_id', $gallery->id)->update(['is_used_as_cover' => false]);
                $crop = $data['crop'] ?? null;
                $this->processAndStoreCover($gallery, $coverFile, $crop);
            }

            $gallery->load(['tags:id,name,slug', 'cover', 'media']);

            // Clear gallery cache after update
            $this->clearVersionedCache(self::CACHE_SCOPE);

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
     * Soft-delete a gallery (keep media rows & files). Returns true on success.
     */
    public function deleteGallery(int $galleryId): bool
    {
        try {
            return DB::transaction(function () use ($galleryId) {
                $gallery = Gallery::with('media')->findOrFail($galleryId);

                // Soft delete the gallery (Gallery model uses SoftDeletes)
                $gallery->delete();

                // Clear versioned cache for galleries
                $this->clearVersionedCache(self::CACHE_SCOPE);

                Log::info('Gallery soft-deleted', ['gallery_id' => $galleryId, 'slug' => $gallery->slug]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete gallery', ['gallery_id' => $galleryId, 'exception' => $e]);
            throw $e;
        }
    }

    /**
     * Paginate and return galleries with applied filters and mapped API shape.
     * This centralizes list logic so controllers remain thin.
     */
    public function paginateGalleries(array $params = []): array
    {
        try {
            $perPage = isset($params['per_page']) ? max(1, min(100, (int) $params['per_page'])) : 15;
            $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;

            // Build cache key using versioned-key helper
            $cacheKey = $this->getVersionedKey(self::CACHE_SCOPE, array_merge(['perPage' => $perPage, 'page' => $page], $params));
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            $query = Gallery::with(['tags:id,name,slug', 'cover', 'media']);

            if (!empty($params['search'])) {
                $term = $params['search'];
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'LIKE', "%{$term}%")
                        ->orWhere('description', 'LIKE', "%{$term}%");
                });
            }

            if (!empty($params['category_id'])) {
                $query->where('category_id', $params['category_id']);
            }

            $paginated = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $galleries = $paginated->getCollection()
                ->map(function ($gallery) {
                    return $this->mapGalleryToApi($gallery);
                })->toArray();

            $result = [
                'galleries' => $galleries,
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'total_pages' => $paginated->lastPage(),
            ];

            // Store in cache
            Cache::put($cacheKey, $result, self::CACHE_TTL);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to paginate galleries', ['params' => $params, 'exception' => $e]);
            throw $e;
        }
    }

    /**
     * Return a single gallery payload normalized for API consumers.
     */
    public function getGalleryById(int $id): array
    {
        try {
            $cacheKey = $this->getVersionedKey(self::CACHE_SCOPE, ['id' => $id]);
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            $gallery = Gallery::with(['tags:id,name,slug', 'media'])->findOrFail($id);

            $payload = $gallery->toArray();

            // normalize media entries with public URLs and canonical fields
            $payload['media'] = $gallery->media
                ->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'filename' => $m->filename,
                        'url' => $m->url,
                        'extension' => $m->extension,
                        'mime_type' => $m->mime_type,
                        'size' => $m->size,
                        'is_cover' => (bool) ($m->is_cover ?? false),
                        'uploaded_at' => $m->uploaded_at,
                    ];
                })->toArray();

            $payload['cover'] = $gallery->cover ? ['id' => $gallery->cover->id, 'url' => $gallery->cover->url] : null;

            Cache::put($cacheKey, $payload, self::CACHE_TTL);

            return $payload;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve gallery by id', ['id' => $id, 'exception' => $e]);
            throw $e;
        }
    }

    /**
     * Set a specific media item as the gallery's selected cover and return updated media list.
     */
    public function setCoverMedia(int $galleryId, int $mediaId): array
    {
        $gallery = Gallery::with('media')->findOrFail($galleryId);

        $media = Media::where('gallery_id', $galleryId)->where('id', $mediaId)->firstOrFail();

        // unset selected-cover flags for this gallery
        Media::where('gallery_id', $galleryId)->update(['is_used_as_cover' => false]);

        // set chosen media as the selected cover (keep variant flags unchanged)
        $media->is_used_as_cover = true;
        $media->save();

        // Clear gallery cache since media/cover changed
        $this->clearVersionedCache(self::CACHE_SCOPE);

        // return updated media list
        $mediaList = $gallery->fresh('media')->media->map(function ($m) {
            return [
                'id' => $m->id,
                'filename' => $m->filename,
                'url' => $m->url,
                'is_cover' => (bool) ($m->is_cover ?? false),
            ];
        })->toArray();

        return $mediaList;
    }

    /**
     * Map an Eloquent Gallery model into API-friendly array (used by pagination helper).
     */
    private function mapGalleryToApi(Gallery $g): array
    {
        return [
            'id' => $g->id,
            'title' => $g->title,
            'slug' => $g->slug,
            'description' => $g->description,
            'category_id' => $g->category_id,
            'is_active' => $g->is_active,
            'is_public' => $g->is_public,
            'item_count' => $g->item_count,
            'cover' => $g->cover ? ['id' => $g->cover->id, 'url' => $g->cover->url] : null,
            'media' => $g->media->map(function ($m) {
                return $this->mapMediaToApi($m);
            })->toArray(),
            'tags' => $g->tags->map(function ($t) {
                return ['id' => $t->id, 'name' => $t->name];
            })->toArray(),
            'created_at' => $g->created_at,
            'updated_at' => $g->updated_at,
        ];
    }

    private function mapMediaToApi($m): array
    {
        return [
            'id' => $m->id,
            'filename' => $m->filename,
            'url' => $m->url,
            'extension' => $m->extension,
            'mime_type' => $m->mime_type,
            'size' => $m->size,
            // backward-compatible API field `is_cover` now reflects the selected cover (is_used_as_cover)
            'is_cover' => (bool) ($m->is_used_as_cover ?? false),
            // expose variant flag separately so clients can know which rows are cover variants
            'is_cover_variant' => (bool) ($m->is_cover ?? false),
            'uploaded_at' => $m->uploaded_at,
        ];
    }
    /**
     * Process the cover into multiple sizes and store, returns Media model.
     *
     * Storage structure:
     *   upload/images/cover/1200x900/{Y/m/d}/{uuid}.webp
     *   upload/images/cover/400x300/{Y/m/d}/{uuid}.webp
     *   upload/images/cover/originals/{Y/m/d}/{uuid}_original.{ext}
     *
     * All 3 media rows share the same variant_code (UUID).
     */
    private function processAndStoreCover(Gallery $gallery, UploadedFile $file, ?array $crop = null): Media
    {
        try {
            $datePath = now()->format('Y/m/d');

            // Generate UUID used for both filename and variant_code
            $variantCode = Str::uuid()->toString();
            $filenameBase = "{$variantCode}.webp";

            // Prepare paths — flat structure, no gallery slug in path
            $path1200 = self::BASE_COVER_PATH . "/1200x900/{$datePath}/{$filenameBase}";
            $path400 = self::BASE_COVER_PATH . "/400x300/{$datePath}/{$filenameBase}";

            // Create directories
            Storage::disk('public')->makeDirectory(self::BASE_COVER_PATH . "/1200x900/{$datePath}");
            Storage::disk('public')->makeDirectory(self::BASE_COVER_PATH . "/400x300/{$datePath}");
            Storage::disk('public')->makeDirectory(self::BASE_COVER_PATH . "/originals/{$datePath}");

            // Persist the original upload so we can reprocess later (keeps original extension)
            $origPath = null;
            $origExt = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)) ?: 'jpg';
            try {
                $origPath = self::BASE_COVER_PATH . "/originals/{$datePath}/{$variantCode}_original.{$origExt}";
                Storage::disk('public')->putFileAs(
                    self::BASE_COVER_PATH . "/originals/{$datePath}",
                    $file,
                    "{$variantCode}_original.{$origExt}"
                );
            } catch (\Exception $e) {
                Log::warning('Failed to store original upload', ['exception' => $e]);
            }

            // Load original image
            $img = Image::make($file->getRealPath());

            // If crop coordinates provided, map them to original pixels and crop first
            if (is_array($crop) && !empty($crop['width']) && !empty($crop['height'])) {
                $canvasW = intval($crop['canvas_width'] ?? 0);
                $canvasH = intval($crop['canvas_height'] ?? 0);
                $origW = intval($crop['orig_width'] ?? $img->width());
                $origH = intval($crop['orig_height'] ?? $img->height());

                if ($img->width() !== $origW || $img->height() !== $origH) {
                    Log::info('Uploaded image dimensions differ from orig_width/orig_height; skipping server-side crop (assuming client pre-cropped).', [
                        'uploaded_w' => $img->width(),
                        'uploaded_h' => $img->height(),
                        'orig_w' => $origW,
                        'orig_h' => $origH,
                    ]);
                } else {
                    $scaleX = $canvasW > 0 ? ($origW / $canvasW) : 1;
                    $scaleY = $canvasH > 0 ? ($origH / $canvasH) : 1;

                    $cx = max(0, intval(round(($crop['x'] ?? 0) * $scaleX)));
                    $cy = max(0, intval(round(($crop['y'] ?? 0) * $scaleY)));
                    $cw = max(1, intval(round(($crop['width'] ?? $canvasW) * $scaleX)));
                    $ch = max(1, intval(round(($crop['height'] ?? $canvasH) * $scaleY)));

                    $cx = min($cx, $img->width() - 1);
                    $cy = min($cy, $img->height() - 1);
                    $cw = min($cw, $img->width() - $cx);
                    $ch = min($ch, $img->height() - $cy);

                    try {
                        $img->crop($cw, $ch, $cx, $cy);
                    } catch (\Exception $e) {
                        Log::warning('Failed to crop original with provided coordinates', ['exception' => $e, 'crop' => $crop]);
                    }
                }
            }

            // Produce high-quality variants from (possibly cropped) original
            $img1200 = clone $img;
            $img1200->fit(1200, 900);
            Storage::disk('public')->put($path1200, (string) $img1200->encode('webp', 90));

            $img400 = clone $img;
            $img400->fit(400, 300);
            Storage::disk('public')->put($path400, (string) $img400->encode('webp', 90));

            // Create media records — all share the same variant_code
            $createdMedia = [];

            // Original (keep original extension)
            if ($origPath) {
                try {
                    $origSize = Storage::disk('public')->exists($origPath) ? Storage::disk('public')->size($origPath) : 0;
                    $createdMedia[] = Media::create([
                        'gallery_id' => $gallery->id,
                        'variant_code' => $variantCode,
                        'variant_size' => 'original',
                        'filename' => $origPath,
                        'extension' => $origExt,
                        'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                        'size' => $origSize,
                        'alt_text' => $gallery->title,
                        'sort_order' => 0,
                        'is_cover' => false,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create media record for original', ['exception' => $e]);
                }
            }

            // 1200x900 — primary cover
            $media1200 = Media::create([
                'gallery_id' => $gallery->id,
                'variant_code' => $variantCode,
                'variant_size' => '1200x900',
                'filename' => $path1200,
                'extension' => 'webp',
                'mime_type' => 'image/webp',
                'size' => Storage::disk('public')->size($path1200),
                'alt_text' => $gallery->title,
                'sort_order' => 0,
                'is_cover' => true,
                'is_used_as_cover' => true,
            ]);
            $createdMedia[] = $media1200;

            // 400x300 thumbnail — also a cover variant
            $media400 = Media::create([
                'gallery_id' => $gallery->id,
                'variant_code' => $variantCode,
                'variant_size' => '400x300',
                'filename' => $path400,
                'extension' => 'webp',
                'mime_type' => 'image/webp',
                'size' => Storage::disk('public')->size($path400),
                'alt_text' => $gallery->title,
                'sort_order' => 0,
                'is_cover' => true,
            ]);
            $createdMedia[] = $media400;

            // Return the primary cover media (1200x900)
            return $media1200;
        } catch (\Exception $e) {
            Log::error('Failed to process cover image', ['exception' => $e]);
            throw $e;
        }
    }
}

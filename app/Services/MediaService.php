<?php

namespace App\Services;

use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Models\Gallery;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class MediaService
{
    private const BASE_GALLERY_PATH = "uploads/gallery";
    private const BASE_IMAGES_PATH = "uploads/images";

    public function __construct()
    {
        Image::configure([
            "driver" => extension_loaded("imagick") ? "imagick" : "gd",
        ]);
    }

    public function paginateMedia(array $params = []): array
    {
        $perPage = isset($params["per_page"])
            ? max(1, min(100, (int) $params["per_page"]))
            : 15;
        $page = isset($params["page"]) ? max(1, (int) $params["page"]) : 1;

        $query = Media::query()
            ->with(["gallery:id,title,category_id", "gallery.category:id,name"])
            ->where("is_cover", false)
            ->where("filename", "like", "%/1200x900/%");

        if (!empty($params["search"])) {
            $term = $params["search"];
            $query->where(function ($q) use ($term) {
                $q->where("filename", "like", "%{$term}%")->orWhere(
                    "alt_text",
                    "like",
                    "%{$term}%",
                );
            });
        }

        if (!empty($params["gallery_id"])) {
            $query->where("gallery_id", $params["gallery_id"]);
        }

        if (!empty($params["category_id"])) {
            $catId = (int) $params["category_id"];
            $query->whereHas("gallery", function ($q) use ($catId) {
                $q->where("category_id", $catId);
            });
        }

        if (!empty($params["date_from"])) {
            $query->whereDate("uploaded_at", ">=", $params["date_from"]);
        }

        if (!empty($params["date_to"])) {
            $query->whereDate("uploaded_at", "<=", $params["date_to"]);
        }

        if (!empty($params["file_type"])) {
            $query->where("extension", $params["file_type"]);
        }

        if (!empty($params["size_range"])) {
            $query->where(function ($q) use ($params) {
                switch ($params["size_range"]) {
                    case "small":
                        $q->where("size", "<", 1 * 1024 * 1024);
                        break;
                    case "medium":
                        $q->whereBetween("size", [
                            1 * 1024 * 1024,
                            5 * 1024 * 1024,
                        ]);
                        break;
                    case "large":
                        $q->where("size", ">", 5 * 1024 * 1024);
                        break;
                }
            });
        }

        $paginated = $query
            ->orderBy("uploaded_at", "desc")
            ->paginate($perPage, ["*"], "page", $page);

        $media = $paginated
            ->getCollection()
            ->map(function (Media $m) {
                return $this->mapMediaToApi($m);
            })
            ->toArray();

        return [
            "media" => $media,
            "total" => $paginated->total(),
            "per_page" => $paginated->perPage(),
            "current_page" => $paginated->currentPage(),
            "total_pages" => $paginated->lastPage(),
        ];
    }

    public function createMedia(StoreMediaRequest $request): array
    {
        $validated = $request->validated();

        $gallery = null;
        if (!empty($validated["gallery_id"])) {
            $gallery = Gallery::findOrFail($validated["gallery_id"]);
        }

        $data = [
            "gallery_id" => $gallery?->id,
            "alt_text" => $validated["alt_text"] ?? null,
            "crop" => $this->extractCrop($request),
        ];

        $file = $request->file("file");

        return DB::transaction(function () use ($data, $file, $gallery) {
            $created = $this->processAndStoreMedia($data, $file, $gallery);

            if ($gallery) {
                $gallery->increment("item_count");
            }

            return $created;
        });
    }

    public function getMediaById(int $id): array
    {
        $media = Media::with([
            "gallery:id,title,category_id",
            "gallery.category:id,name",
        ])->findOrFail($id);

        $groupKey = $this->getGroupKey($media->filename);
        $group = $this->loadGroupMedia($groupKey);

        $primary = $this->mapMediaToApi($media);
        $variants = $this->mapVariants($group);

        return [...$primary, "variants" => $variants];
    }

    public function updateMedia(UpdateMediaRequest $request, int $id): array
    {
        $media = Media::findOrFail($id);
        $oldGalleryId = $media->gallery_id;

        $newGalleryId = $request->has("gallery_id")
            ? $request->input("gallery_id")
            : $oldGalleryId;
        if ($newGalleryId === "" || $newGalleryId === null) {
            $newGalleryId = null;
        }
        $newGallery = $newGalleryId ? Gallery::findOrFail($newGalleryId) : null;
        $oldGallery = $oldGalleryId ? Gallery::findOrFail($oldGalleryId) : null;

        $groupKey = $this->getGroupKey($media->filename);
        $group = $this->loadGroupMedia($groupKey);

        $hasNewFile = $request->hasFile("file");
        $altText = $request->has("alt_text")
            ? $request->input("alt_text")
            : $media->alt_text;
        $crop = $this->extractCrop($request);

        return DB::transaction(function () use (
            $group,
            $hasNewFile,
            $request,
            $newGallery,
            $oldGallery,
            $altText,
            $crop,
            $oldGalleryId,
            $newGalleryId,
        ) {
            if ($hasNewFile) {
                $this->deleteGroupFiles($group);
                Media::whereIn("id", $group->pluck("id")->all())->delete();

                if ($oldGalleryId && $oldGalleryId !== $newGalleryId) {
                    $oldGallery?->decrement("item_count");
                    $newGallery?->increment("item_count");
                }

                $data = [
                    "gallery_id" => $newGallery?->id,
                    "alt_text" => $altText,
                    "crop" => $crop,
                ];

                $file = $request->file("file");
                return $this->processAndStoreMedia($data, $file, $newGallery);
            }

            // Update alt text + gallery only
            if ($oldGalleryId !== $newGalleryId) {
                $this->moveGroupFiles($group, $oldGallery, $newGallery);

                if ($oldGalleryId) {
                    $oldGallery?->decrement("item_count");
                }
                if ($newGalleryId) {
                    $newGallery?->increment("item_count");
                }

                Media::whereIn("id", $group->pluck("id")->all())->update([
                    "gallery_id" => $newGallery?->id,
                ]);
            }

            if ($altText !== null) {
                Media::whereIn("id", $group->pluck("id")->all())->update([
                    "alt_text" => $altText,
                ]);
            }

            $primary =
                $group->first(function ($item) {
                    return str_contains($item->filename, "/1200x900/");
                }) ?? $group->first();
            $primary = $primary
                ? $primary->fresh([
                    "gallery:id,title,category_id",
                    "gallery.category:id,name",
                ])
                : null;

            return $primary ? $this->mapMediaToApi($primary) : [];
        });
    }

    public function deleteMedia(int $id): bool
    {
        $media = Media::findOrFail($id);
        $galleryId = $media->gallery_id;
        $gallery = $galleryId ? Gallery::find($galleryId) : null;

        $groupKey = $this->getGroupKey($media->filename);
        $group = $this->loadGroupMedia($groupKey);

        return DB::transaction(function () use ($group, $gallery) {
            $this->deleteGroupFiles($group);
            Media::whereIn("id", $group->pluck("id")->all())->delete();

            if ($gallery) {
                $gallery->decrement("item_count");
            }

            return true;
        });
    }

    private function processAndStoreMedia(
        array $data,
        UploadedFile $file,
        ?Gallery $gallery = null,
    ): array {
        $datePath = now()->format("Y/m/d");
        $unique = Str::uuid()->toString();

        $basePath = $this->getBasePath($gallery);
        $filenameBase = "{$unique}.webp";

        $path1200 = "{$basePath}/1200x900/{$datePath}/{$filenameBase}";
        $path400 = "{$basePath}/400x400/{$datePath}/{$filenameBase}";

        Storage::disk("public")->makeDirectory(
            "{$basePath}/1200x900/{$datePath}",
        );
        Storage::disk("public")->makeDirectory(
            "{$basePath}/400x400/{$datePath}",
        );
        Storage::disk("public")->makeDirectory(
            "{$basePath}/originals/{$datePath}",
        );

        $origPath = null;
        $origExt =
            strtolower(
                pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION),
            ) ?:
            "jpg";

        try {
            $origPath = "{$basePath}/originals/{$datePath}/{$unique}_original.{$origExt}";
            Storage::disk("public")->putFileAs(
                "{$basePath}/originals/{$datePath}",
                $file,
                "{$unique}_original.{$origExt}",
            );
        } catch (\Exception $e) {
            Log::warning("Failed to store original upload", [
                "exception" => $e,
            ]);
        }

        $img = Image::make($file->getRealPath());
        $this->applyCrop($img, $data["crop"] ?? null);

        $img1200 = clone $img;
        $img1200->fit(1200, 900);
        Storage::disk("public")->put(
            $path1200,
            (string) $img1200->encode("webp", 90),
        );

        $img400 = clone $img;
        $img400->fit(400, 400);
        Storage::disk("public")->put(
            $path400,
            (string) $img400->encode("webp", 90),
        );

        $records = [];

        if ($origPath) {
            $records[] = Media::create([
                "gallery_id" => $gallery?->id,
                "filename" => $origPath,
                "extension" => $origExt,
                "mime_type" =>
                    $file->getMimeType() ?? "application/octet-stream",
                "size" => Storage::disk("public")->exists($origPath)
                    ? Storage::disk("public")->size($origPath)
                    : 0,
                "alt_text" => $data["alt_text"] ?? null,
                "sort_order" => 0,
                "is_cover" => false,
                "is_used_as_cover" => false,
            ]);
        }

        $records[] = Media::create([
            "gallery_id" => $gallery?->id,
            "filename" => $path1200,
            "extension" => "webp",
            "mime_type" => "image/webp",
            "size" => Storage::disk("public")->size($path1200),
            "alt_text" => $data["alt_text"] ?? null,
            "sort_order" => 0,
            "is_cover" => false,
            "is_used_as_cover" => false,
        ]);

        $records[] = Media::create([
            "gallery_id" => $gallery?->id,
            "filename" => $path400,
            "extension" => "webp",
            "mime_type" => "image/webp",
            "size" => Storage::disk("public")->size($path400),
            "alt_text" => $data["alt_text"] ?? null,
            "sort_order" => 0,
            "is_cover" => false,
            "is_used_as_cover" => false,
        ]);

        $primary = collect($records)->first(function ($m) use ($path1200) {
            return $m->filename === $path1200;
        });

        return $primary
            ? $this->mapMediaToApi(
                $primary->fresh([
                    "gallery:id,title,category_id",
                    "gallery.category:id,name",
                ]),
            )
            : [];
    }

    private function extractCrop($request): ?array
    {
        if (
            $request->has("crop_x") &&
            $request->has("crop_y") &&
            $request->has("crop_width") &&
            $request->has("crop_height")
        ) {
            return [
                "canvas_width" => intval(
                    $request->input("crop_canvas_width", 0),
                ),
                "canvas_height" => intval(
                    $request->input("crop_canvas_height", 0),
                ),
                "x" => intval($request->input("crop_x")),
                "y" => intval($request->input("crop_y")),
                "width" => intval($request->input("crop_width")),
                "height" => intval($request->input("crop_height")),
                "orig_width" => intval($request->input("orig_width", 0)),
                "orig_height" => intval($request->input("orig_height", 0)),
            ];
        }

        return null;
    }

    private function applyCrop($img, ?array $crop): void
    {
        if (
            !is_array($crop) ||
            empty($crop["width"]) ||
            empty($crop["height"])
        ) {
            return;
        }

        $canvasW = intval($crop["canvas_width"] ?? 0);
        $canvasH = intval($crop["canvas_height"] ?? 0);
        $origW = intval($crop["orig_width"] ?? $img->width());
        $origH = intval($crop["orig_height"] ?? $img->height());

        if ($img->width() !== $origW || $img->height() !== $origH) {
            Log::info(
                "Uploaded image dimensions differ from orig_width/orig_height; skipping server-side crop (assuming client pre-cropped).",
                [
                    "uploaded_w" => $img->width(),
                    "uploaded_h" => $img->height(),
                    "orig_w" => $origW,
                    "orig_h" => $origH,
                ],
            );
            return;
        }

        $scaleX = $canvasW > 0 ? $origW / $canvasW : 1;
        $scaleY = $canvasH > 0 ? $origH / $canvasH : 1;

        $cx = max(0, intval(round(($crop["x"] ?? 0) * $scaleX)));
        $cy = max(0, intval(round(($crop["y"] ?? 0) * $scaleY)));
        $cw = max(1, intval(round(($crop["width"] ?? $canvasW) * $scaleX)));
        $ch = max(1, intval(round(($crop["height"] ?? $canvasH) * $scaleY)));

        $cx = min($cx, $img->width() - 1);
        $cy = min($cy, $img->height() - 1);
        $cw = min($cw, $img->width() - $cx);
        $ch = min($ch, $img->height() - $cy);

        try {
            $img->crop($cw, $ch, $cx, $cy);
        } catch (\Exception $e) {
            Log::warning("Failed to crop original with provided coordinates", [
                "exception" => $e,
                "crop" => $crop,
            ]);
        }
    }

    private function getBasePath(?Gallery $gallery): string
    {
        if ($gallery) {
            return self::BASE_GALLERY_PATH . "/" . $gallery->slug;
        }

        return self::BASE_IMAGES_PATH;
    }

    private function getGroupKey(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        return Str::replaceLast("_original", "", $base);
    }

    private function loadGroupMedia(string $groupKey)
    {
        return Media::query()
            ->where("filename", "like", "%/{$groupKey}.webp")
            ->orWhere("filename", "like", "%/{$groupKey}_original.%")
            ->get();
    }

    private function moveGroupFiles(
        $group,
        ?Gallery $oldGallery,
        ?Gallery $newGallery,
    ): void {
        $oldPrefix = $this->getBasePath($oldGallery);
        $newPrefix = $this->getBasePath($newGallery);

        foreach ($group as $media) {
            $oldPath = $media->filename;
            if (!Str::startsWith($oldPath, $oldPrefix)) {
                continue;
            }
            $newPath = Str::replaceFirst($oldPrefix, $newPrefix, $oldPath);

            if (Storage::disk("public")->exists($oldPath)) {
                Storage::disk("public")->makeDirectory(dirname($newPath));
                Storage::disk("public")->move($oldPath, $newPath);
            }

            $media->filename = $newPath;
            $media->gallery_id = $newGallery?->id;
            $media->save();
        }
    }

    private function deleteGroupFiles($group): void
    {
        foreach ($group as $media) {
            if (Storage::disk("public")->exists($media->filename)) {
                Storage::disk("public")->delete($media->filename);
            }
        }
    }

    private function mapMediaToApi(Media $m): array
    {
        $filename = basename($m->filename);

        return [
            "id" => $m->id,
            "name" => $filename,
            "filename" => $m->filename,
            "url" => $m->url,
            "extension" => $m->extension,
            "mime_type" => $m->mime_type,
            "size" => $m->size,
            "alt_text" => $m->alt_text,
            "gallery_id" => $m->gallery_id,
            "gallery" => $m->gallery
                ? ["id" => $m->gallery->id, "title" => $m->gallery->title]
                : null,
            "category" =>
                $m->gallery && $m->gallery->category
                    ? [
                        "id" => $m->gallery->category->id,
                        "name" => $m->gallery->category->name,
                    ]
                    : null,
            "uploaded_at" => $m->uploaded_at,
        ];
    }

    private function mapVariants($group): array
    {
        $variants = [
            "original" => null,
            "size_1200" => null,
            "size_400" => null,
        ];

        foreach ($group as $m) {
            if (str_contains($m->filename, "/originals/")) {
                $variants["original"] = [
                    "id" => $m->id,
                    "url" => $m->url,
                    "filename" => $m->filename,
                ];
            } elseif (str_contains($m->filename, "/1200x900/")) {
                $variants["size_1200"] = [
                    "id" => $m->id,
                    "url" => $m->url,
                    "filename" => $m->filename,
                ];
            } elseif (str_contains($m->filename, "/400x400/")) {
                $variants["size_400"] = [
                    "id" => $m->id,
                    "url" => $m->url,
                    "filename" => $m->filename,
                ];
            }
        }

        return $variants;
    }
}

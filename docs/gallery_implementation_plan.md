# Gallery Backend Implementation Plan

Add backend support for Gallery management, including CRUD operations and automated SEO image processing (WebP conversion and multi-size generation).

## Proposed Changes

### [Backend] Storage Architecture

Images will be stored in `storage/app/public` with two distinct paths:

1.  **Gallery Path**: For images that belong to a specific collection/album.
2.  **Images Path**: For general uploads (e.g., article images, single photos) not tied to a gallery.

```text
storage/app/public/uploads/
├── gallery/           (Thematic organization)
│   └── {gallery-slug}/
│       ├── covers/            (Dedicated folder for Gallery Cover)
│       │   ├── 1200x900/
│       │   └── 400x400/
│       ├── 1200x900/          (Regular Photos - Size First)
│       │   └── {year}/{month}/{day}/
│       │       └── {xxxx}.webp
│       └── 400x400/           (Regular Photos - Size First)
│           └── {year}/{month}/{day}/
│               └── {xxxx}.webp
└── images/            (General pool)
    ├── 1200x900/              (Size First)
    │   └── {year}/{month}/{day}/
    │       └── {xxxx}.webp
    └── 400x400/               (Size First)
        └── {year}/{month}/{day}/
            └── {xxxx}.webp
```

### [Backend] Scalability & High-Volume Logic

- **Standardized Folder Order**: We place the **Image Size first**, then the Date. This creates a predictable URL pattern: `/uploads/{type}/{context}/{size}/{date}/{file}.webp`.
- **Explicit Size Folders**: We use the actual dimensions (e.g., `1200x900`) as folder names. This makes it crystal clear what resolution is stored inside each path.
- **Date Subfolders within Gallery**: Since we expect **2,000+ photos per day**, we MUST use date-based subfolders everywhere. This prevents any single directory from hitting the performance-sapping file limit of modern filesystems.

### [Backend] Logic for Unassigned Photos

If a user uploads a photo without selecting a gallery:
- **Storage**: It moves to the `images/{year}/{month}/` path.
- **Database**: We will implement a `Media` or `Photo` model that can exist independently (nullable `gallery_id`).
- **SEO**: All resizing/WebP rules still apply to maintain high performance.

### [Backend] Moving Images (Assignment Logic)

When a previously "unassigned" image is edited to join a gallery:
1.  **Physical Move**: The backend will physically move the three WebP versions (Original, Medium, Thumbnail) from the `uploads/images/` path to the new `uploads/gallery/{gallery-slug}/` path.
2.  **Path Update**: The database `path` fields will be updated to reflect the new location.
3.  **Consistency**: This keeps the `gallery/` folder as a single source of truth for everything inside an album, making exports or bulk deletions easier.

### [Backend] Database Schema

We will use two main tables to support both structured galleries and independent photo uploads.

#### 1. `galleries` Table
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key |
| `title` | String | User-friendly title |
| `slug` | String | Unique URL identifier (indexed) |
| `description`| Text | Optional gallery description |
| `category_id` | Foreign Key| Link to `categories` table (nullable) |
| `is_active` | Boolean | `true` (Active), `false` (Inactive) |
| `is_public` | Boolean | `true` (Public), `false` (Private) |
| `cover_id` | Foreign Key| Link to `media` for the specific cover |
| `item_count` | Integer | Cached count for performance |
| `timestamps` | - | `created_at`, `updated_at`, `deleted_at` |

#### 2. `media` Table (General Asset Pool)
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key |
| `gallery_id` | Foreign Key| Link to `galleries` (nullable for standalone photos) |
| `filename` | String | Unique hash name (e.g., `abc-123`) |
| `extension` | String | Always `webp` in our plan |
| `mime_type` | String | `image/webp` |
| `size` | BigInt | Total file size in bytes |
| `alt_text` | String | SEO optimization field |
| `sort_order` | Integer | For custom ordering in galleries |
| `uploaded_at`| Timestamp | Used to reconstruct the `{Y}/{M}/{D}` path |
| `timestamps` | - | Standard Laravel timestamps |

---

### [Backend] Gallery Core

#### [NEW] [Gallery.php](file:///home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/app/Models/Gallery.php)
- Define model with fillable fields: `title`, `slug`, `description`, `is_active`, `is_public`, `category_id`, `cover_id`, `tags`, `item_count`.
- Add relationship to `Category` and `Media` (for cover).
- Implement `Sluggable` trait or manual slug generation.

#### [NEW] [Migration](file:///home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/database/migrations/2026_02_08_000000_create_galleries_table.php)
- Create `galleries` table with appropriate indexes and foreign keys.

### [Backend] Service Layer

#### [NEW] [GalleryService.php](file:///home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/app/Services/GalleryService.php)
- Handle CRUD logic.
- **Image Processing**:
    - Use `Intervention Image` to process uploads.
    - Convert all images to **WebP**.
    - Generate two versions:
        - `1200x900`: Featured (Google Discover).
        - `400x400`: Thumbnail / Grid.

### [Backend] API Layer

#### [NEW] [GalleryController.php](file:///home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/app/Http/Controllers/Api/Managements/GalleryController.php)
- Standard Resource Controller with `index`, `store`, `show`, `update`, `destroy`.
- Integrate permissions (`gallery_management.view`, `gallery_management.create`, etc.).

#### [MODIFY] [api.php](file:///home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/routes/api.php)
- Register `galleries` resource routes.

## Verification Plan

### Automated Tests
- Run `php artisan test` after creating `GalleryTest.php` to verify:
    - Resource creation with valid data.
    - Validation errors for missing fields.
    - Automatic WebP conversion and file existence.

### Manual Verification
1. Upload a high-resolution JPG in the Gallery Add form.
2. Verify in the filesystem/database that:
    - The file is converted to WebP.
    - Three different sizes are generated and stored.
    - The `category_id` is correctly saved from the new dynamic dropdown.

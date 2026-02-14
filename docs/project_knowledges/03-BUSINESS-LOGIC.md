# Business Logic Analysis

## Core Business Processes

### 1. User Lifecycle Management

#### Creation Workflow
**Evidence**: [app/Http/Controllers/Api/Managements/UserController.php](app/Http/Controllers/Api/Managements/UserController.php) store() + [app/Services/UserService.php](app/Services/UserService.php) createUser()

**Steps**:
1. Admin submits user creation form with:
   - name, email, password, phone (optional)
   - profile image file (optional)
   - roles to assign (optional)

2. `StoreUserRequest` validates:
   - email is unique
   - password meets requirements
   - file is valid image (if provided)

3. `UserService::createUser()` executes:
   ```
   a) Check if user exists in soft-deleted state (restore scenario)
   b) If exists + soft-deleted:
      - Restore record
      - Update with new data
      - Mark as restored in response
   c) If not exists:
      - Create new user record
      - Password automatically hashed via Model cast
   d) Process profile image if provided:
      - Resize via Intervention Image
      - Store in storage/profile_images/
      - Save filename in profile_image column
   e) Assign roles:
      - Spatie: user->syncRoles($roles)
   f) Clear cache
   g) Return formatted user data
   ```

**Evidence in UserService**:
- Line ~90: Check for soft-deleted user and restore
- Line ~110: Profile image processing
- Line ~125: Role assignment via Spatie's syncRoles()

#### Viewing/Filtering
**Evidence**: UserService::getFilteredPaginatedUsers()

**Filters Supported** (from [routes/api.php](routes/api.php)):
- `search` - matches name OR email (wildcard)
- `name` - exact name match (wildcard)
- `email` - exact email match (wildcard)
- `phone` - phone number
- `username` - username (if field exists)
- `role` - filter by Spatie role
- `status` - active/inactive/banned
- `is_banned` - boolean
- `date_from`, `date_to` - registration date range

**Query Logic**:
1. Build query with eager loading: `with('roles')`
2. Apply filters via WHERE and HAVING clauses
3. Join on `model_has_roles` table for role filtering
4. Paginate (default 15 per page)
5. Check versioned cache before executing

**Evidence**: Lines 47-120 in UserService.php

#### Update Workflow
**Endpoint**: `PUT /api/v1/users/{id}` or `PATCH /api/v1/users/{id}`

**Via UpdateUserRequest validation**:
- Can update: name, email, phone, profile_image, password
- Cannot update: other fields

**Service Logic** (UserService::updateUser()):
1. Load user by ID
2. Check if protected from profile updates (ProtectionService)
3. Process new profile image if provided
4. Update user fields
5. Update role assignments if provided
6. Clear cache
7. Return formatted data

#### Soft Delete vs Physical Delete
**Soft Delete** (DELETE endpoint):
- Sets `deleted_at` timestamp
- User remains in database but hidden from queries
- Can be restored

**Evidence**: User model uses `SoftDeletes` trait

**Hard Delete** (via `->forceDelete()`):
- Not directly available through API
- Can only be done via Tinker or direct DB access
- Prevents accidental permanent data loss

### 2. User Status Management

#### Ban System
**Data Model**:
- Users table: `is_banned` (boolean)
- `user_ban_histories` table: Audit trail of ban events

**Evidence**: [database/migrations/2026_01_31_041410_add_ban_fields_back_to_users_table.php](database/migrations/2026_01_31_041410_add_ban_fields_back_to_users_table.php), [database/migrations/2026_01_31_074611_create_user_ban_histories_table.php](database/migrations/2026_01_31_074611_create_user_ban_histories_table.php)

**Ban Endpoint**: `POST /api/v1/users/{id}/ban`
**Logic** (UserBanHistoryService::createBanHistory()):
1. Validate user exists
2. Check if user is protected from banning (ProtectionService)
3. Create entry in `user_ban_histories` table:
   - action: 'ban'
   - reason: provided text
   - banned_until: optional timestamp
   - is_forever: boolean (set by API caller)
   - performed_by: current user ID
4. Set user `is_banned = true`
5. Revoke all active Sanctum tokens (force re-login)

**Unban Endpoint**: `POST /api/v1/users/{id}/unban`
**Logic**:
1. Create another `user_ban_histories` entry with action='unban'
2. Set `is_banned = false`

**Ban History Access**:
- `GET /api/v1/users/{id}/ban-history`
- Returns all entries for user
- Shows who banned, when, reason, duration

#### Active/Inactive Status
**Field**: `is_active` (boolean)

**Purpose**: Distinguish from ban
- `is_banned = true`: User violated terms, penalized
- `is_active = false`: Account suspended, disabled, or awaiting approval

**Enforcement**:
- Login check: AuthService line 45 - `if (!$user->is_active) throw exception`
- Middleware check: CheckUserStatus.php - validates on every request

**Evidence**: [app/Http/Middleware/CheckUserStatus.php](app/Http/Middleware/CheckUserStatus.php)

### 3. Role & Permission System

**Subsystem**: Spatie Permission library (v6.24)

**Data Model** (Spatie-provided):
- `roles` - role definitions
- `permissions` - permission definitions
- `model_has_roles` - users → roles junction
- `role_has_permissions` - roles → permissions junction
- `model_has_permissions` - direct user → permission assignments (rarely used)

**Permissions Naming Convention**: `{entity}.{action}`
Examples:
- `user_management.view`
- `user_management.add`
- `user_management.edit`
- `user_management.delete`
- `user_management.ban`
- Similar for: `role_management`, `gallery_management`, `category_management`

**Evidence**: [routes/api.php](routes/api.php) middleware directives like `permission:user_management.view`

#### Permission Management Workflow
**Endpoints**:
- `GET /api/v1/permissions` - List all permissions
- `GET /api/v1/permissions/grouped` - List permissions grouped by category

**Service Logic** (PermissionService):
1. Fetch all Permission records
2. Format with human-readable labels
3. Categorize by first dot-separated segment
4. Return grouped or flat based on endpoint

**Evidence**: [app/Services/PermissionService.php](app/Services/PermissionService.php)

#### Role Management Workflow
**Endpoints**:
- `GET /api/v1/roles/options` - Simple list for dropdowns
- `GET /api/v1/roles` - Paginated list with user counts
- `POST /api/v1/roles` - Create new role
- `PUT /api/v1/roles/{id}` - Update role
- `DELETE /api/v1/roles/{id}` - Delete role

**Role Creation Logic** (RoleService::createRole()):
1. Validate role name is unique
2. Create Role record
3. Assign permissions:
   - Parse permissions array
   - Use Spatie's `syncPermissions()` to attach
4. Clear cache
5. Return role with permissions

**Constraints**:
- Protected roles cannot be deleted (ProtectionService)
  - Default: `super_admin` is protected
- Evidence: [config/protected_entities.php](config/protected_entities.php)

### 4. Gallery & Media Management

#### Gallery Creation Workflow
**Endpoint**: `POST /api/v1/galleries`

**Input** (from Vue form):
```json
{
  "title": "Summer 2026",
  "description": "Beach photos",
  "category_id": 5,
  "visibility": "public",
  "status": "active",
  "file": <UploadedFile>,
  "crop": {
    "x": 100,
    "y": 50,
    "width": 400,
    "height": 300
  },
  "tags": ["beach", "summer", "vacation"]
}
```

**Service Processing** (GalleryService::createGallery()):

1. **Database Transaction** - All changes atomic
   
2. **Slug Generation** (unique, SEO-friendly):
   - Generate base slug from title: `Str::slug(title)`
   - Check uniqueness
   - If exists, append counter: `slug-2`, `slug-3`
   
3. **Gallery Record Creation**:
   ```php
   Gallery::create([
       'title' => data['title'],
       'slug' => generated_slug,
       'description' => data['description'] ?? null,
       'category_id' => data['category_id'] ?? null,
       'is_active' => (data['status'] === 'active'),
       'is_public' => (data['visibility'] === 'public'),
       'item_count' => 0
   ])
   ```

4. **Cover Image Processing** (processAndStoreCover()):
   
   **If cover file provided**:
   
   a. Load image via Intervention Image:
      ```
      Image::make($file)
      ```
   
   b. Apply server-side crop if provided:
      ```
      ->crop(width, height, x, y)
      ```
   
   c. Generate two variants:
      - **1200x900 variant**:
        - Resize to fit 1200x900
        - Convert to WebP (quality 95)
        - Store: `storage/app/public/uploads/gallery/{slug}/covers/1200x900/{Y}/{m}/{d}/{unique}.webp`
      
      - **400x400 variant**:
        - Resize to fit 400x400
        - Convert to WebP (quality 90)
        - Store: `storage/app/public/uploads/gallery/{slug}/covers/400x400/{Y}/{m}/{d}/{unique}.webp`
   
   d. Create Media records:
      ```
      Media::create([
          'gallery_id' => gallery.id,
          'filename' => stored_path,
          'extension' => 'webp',
          'mime_type' => 'image/webp',
          'size' => bytes,
          'is_cover' => true,
          'uploaded_at' => now()
      ])
      ```

5. **Tag Processing**:
   - For each tag: `Tag::firstOrCreate(['slug' => slug], ['name' => name])`
   - Sync to gallery: `gallery->tags()->sync($tagIds)`

6. **Cache Invalidation**:
   - Clear any cached gallery lists

7. **Return**: Gallery object with tags and cover media

**Evidence**: [app/Services/GalleryService.php](app/Services/GalleryService.php) lines 23-68

#### Gallery Update Workflow
**Endpoint**: `PUT /api/v1/galleries/{id}`

**Updates Allowed**:
- title, description, category_id, visibility, status
- Cover image can be replaced
- Tags can be updated

**Logic**:
1. Load gallery
2. Update mutable fields
3. If new cover uploaded: process like creation workflow
4. If tags changed: resync junction table
5. Clear cache
6. Return updated gallery

#### Gallery Deletion
**Endpoint**: `DELETE /api/v1/galleries/{id}`

**Behavior**:
- Soft delete (sets `deleted_at`)
- All associated Media records remain (foreign key allows null via onDelete('set null') for media)
- Gallery can be restored via backend only (no API endpoint)

**Evidence**: [database/migrations/2026_02_08_135549_create_galleries_table.php](database/migrations/2026_02_08_135549_create_galleries_table.php)

#### Media/Image Storage Architecture
**Path Structure**:
```
storage/app/public/uploads/
├── gallery/
│   └── {gallery-slug}/
│       └── covers/
│           ├── 1200x900/
│           │   └── {year}/{month}/{day}/{unique}.webp
│           └── 400x400/
│               └── {year}/{month}/{day}/{unique}.webp
```

**Rationale** (from gallery_implementation_plan.md):
- **Size-first ordering**: Easier to identify resolution
- **Date-based subfolders**: Prevents filesystem performance degradation (ext4 limit ~15k files per directory)
- **Gallery-slug grouping**: Logical organization, simplifies backups/exports

**WebP Conversion Purpose**:
- Smaller file size (30-40% vs JPEG)
- Better quality at lower sizes
- Modern browser support (95%+)
- SEO benefit (Google favors WebP)

### 5. Category Management

#### Hierarchical Structure
**Data Model**:
```
CategoryType
    ↓
Category (belongs to type)
    ├─ parent_id (self-referencing)
    └─ children (self-referencing)
```

**Example**:
```
CategoryType: "Gallery"
├─ Photography (id=1, parent_id=null)
│  ├─ Landscape (id=2, parent_id=1)
│  ├─ Portrait (id=3, parent_id=1)
└─ Art (id=4, parent_id=null)
```

**Evidence**: [app/Models/Category.php](app/Models/Category.php) lines 21-31 show parent/children relationships

#### Category Workflow
**Creation Endpoint**: `POST /api/v1/categories`

**Input**:
```json
{
  "type": "gallery",
  "name": "Landscape",
  "description": "...",
  "parent_id": 1,
  "is_active": true
}
```

**Service Logic** (CategoryService::createCategory()):
1. Look up CategoryType by slug
2. Generate slug from name (with collision prevention)
3. Create Category record:
   ```php
   Category::create([
       'category_type_id' => type.id,
       'parent_id' => data['parent_id'] ?? null,
       'name' => data['name'],
       'slug' => generated_slug,
       'description' => data['description'],
       'is_active' => data['is_active'] ?? true
   ])
   ```
4. Clear cache
5. Return formatted category

**Evidence**: [app/Services/CategoryService.php](app/Services/CategoryService.py) lines 74-95

#### Category Querying
**Endpoints**:
- `GET /api/v1/categories/options` - Simple id/name for dropdowns
- `GET /api/v1/categories` - Full list with nesting

**Filters**:
- `search` - by name or slug
- `type` - by CategoryType slug
- `status` - active/inactive
- `slug` - specific slug

**Frontend**: Receives flat list, converts to tree structure client-side

**Caching**: 1-hour TTL via versioned cache keys

### 6. Tag System

#### Simple Non-Hierarchical Structure
**Data Model**:
- Tags table: id, name (unique), slug (unique)
- gallery_tag junction: gallery_id, tag_id (unique composite)

**Auto-Creation on Gallery Save**:
- When creating gallery with tags, non-existent tags created automatically
- Evidence: GalleryService line 53-60
  ```php
  foreach ($data['tags'] as $tagName) {
      $tag = Tag::firstOrCreate(['slug' => slug], ['name' => name, 'slug' => slug]);
      $tagIds[] = $tag->id;
  }
  gallery->tags()->sync($tagIds);
  ```

**Tag Endpoint**: `GET /api/v1/tags/options`
- Returns all tags for autocomplete
- Likely used in gallery create/edit form

### 7. Account Protection System

**Purpose**: Prevent accidental or malicious modification of critical accounts/roles

**Configuration File**: [config/protected_entities.php](config/protected_entities.php)

**Protected Accounts** (configurable):
```php
'protected_accounts' => [
    'super@admin.com' => [
        'protect_deletion' => true,
        'protect_role_change' => true,
        'protect_profile_update' => true,
        'protect_ban' => true
    ]
]
```

**Protected Roles** (configurable):
```php
'protected_roles' => [
    'super_admin' => [
        'protect_deletion' => true,
        'protect_modification' => true
    ]
]
```

**Enforcement Points** (ProtectionService):
1. **Before User Delete**:
   - Check: isAccountProtectedFromDeletion()
   - If protected: throw exception
   
2. **Before Role Change**:
   - Check: isAccountProtectedFromRoleChange()
   - If protected: throw exception (can't assign/remove roles)
   
3. **Before Profile Update**:
   - Check: isAccountProtectedFromProfileUpdate()
   - If protected: allow password/image only, block name/email changes
   
4. **Before Ban**:
   - Check: isAccountProtectedFromBan()
   - If protected: throw exception

**Evidence**: [app/Services/ProtectionService.php](app/Services/ProtectionService.php) lines 11-90

---

## Calculation Logic & Algorithms

### 1. Slug Generation Algorithm
**Purpose**: Create SEO-friendly, unique identifiers

**Algorithm** (CategoryService + GalleryService):
```
1. InputString → slug = Str::slug(InputString)
2. Query: SELECT COUNT(*) FROM table WHERE slug = slug
3. If count = 0: return slug
4. Else:
   i = 2
   while EXISTS(slug-i):
       i++
   return slug-i
```

**Examples**:
- "Summer Photos 2026" → "summer-photos-2026"
- "Summer Photos 2026" (duplicate) → "summer-photos-2026-2"
- "Summer Photos 2026" (another) → "summer-photos-2026-3"

### 2. Image Resize Algorithm (Intervention)
**Goal**: Generate standardized thumbnail variants

**Process**:
1. Load source image
2. For each target size:
   a. Calculate aspect ratio preservation
   b. Resize to fit within bounds (maintains ratio)
   c. Convert to WebP format
   d. Apply quality setting (95 for 1200x900, 90 for 400x400)
   e. Store to path

**Evidence**: GalleryService::processAndStoreCover()

### 3. Versioned Cache Key Generation
**Trait**: [app/Traits/CanVersionCache.php](app/Traits/CanVersionCache.php)

**Purpose**: Automatic cache invalidation when data changes

**Pattern**:
```
versionedKey = scope + version + filters
Example: "users_v1_search=john&page=1"
```

**On Data Change**:
- Service increments version for scope
- Old cache keys become obsolete
- No manual cache busting needed

**Evidence**: Services use `$this->getVersionedKey(self::CACHE_SCOPE, $filters)`

---

## Validation Rules & Business Constraints

### User Validation (StoreUserRequest)
Evidence: [app/Http/Requests/User/StoreUserRequest.php](app/Http/Requests/User/StoreUserRequest.php)

**Required Fields**:
- name: string, required
- email: email format, required, unique in users table

**Optional Fields**:
- password: required on creation; format constraints (regex for complexity)
- phone: optional string
- profile_image: optional file (image only)
- roles: optional array of role IDs

### Gallery Validation
Evidence: [app/Http/Requests/Gallery/StoreGalleryRequest.php](app/Http/Requests/Gallery/StoreGalleryRequest.php)

**Required Fields**:
- title: string, required

**Optional Fields**:
- description: text
- category_id: exists in categories table
- visibility: in ['public', 'private']
- status: in ['active', 'inactive']
- file: image file (jpg, png, gif, webp)
- crop: JSON object with {x, y, width, height}
- tags: array of tag name strings

### Category Validation
**Required**:
- type: must exist in category_types table (by slug)
- name: string, required

**Optional**:
- parent_id: null or exists in categories table (for hierarchy)
- description: text
- is_active: boolean

---

## Business Rules & Constraints

### 1. User Account Rules
- Only one user per email (database unique constraint)
- Currently active user cannot be deleted (soft delete only)
- Password is automatically hashed (Model cast)
- Profile image stored in `storage/profile_images/`

### 2. Gallery Rules
- Gallery slug must be globally unique
- Gallery must have a title
- Cover image is optional
- Gallery can exist without category
- Gallery can be public or private (visibility flag)
- Active/inactive status allows soft-disabling

### 3. Category Rules
- Category must belong to a CategoryType
- Category can have optional parent (self-referential)
- Multiple levels of nesting supported
- Category slug must be unique (within type)

### 4. Permission Rules
- Permissions named with dot notation: `{entity}.{action}`
- Roles are collections of permissions
- Users have roles (preferred) or direct permissions (rare)
- Permission changes take effect immediately (not cached in Spatie)

### 5. Ban Rules
- Ban is timestamped (can be temporary or permanent)
- Ban reason is required
- Performed_by field tracks which user enacted ban
- Ban audit trail is immutable (append-only)

### 6. Protected Account Rules
- Protected accounts cannot be deleted
- Protected accounts cannot be banned
- Protected accounts cannot have roles changed
- Protected accounts can update password/profile image only
- Configured per email address (not role-based for user)

---

## Data Integrity Safeguards

### Foreign Key Constraints
- `gallery.category_id` → `categories.id` (onDelete: set null - orphaned galleries allowed)
- `media.gallery_id` → `galleries.id` (onDelete: cascade - delete media when gallery deleted)
- `user_ban_history.user_id` → `users.id` (onDelete: cascade)
- `user_ban_history.performed_by` → `users.id` (onDelete: set null - can delete performer)
- `gallery_tag.gallery_id` → `galleries.id` (onDelete: cascade)
- `gallery_tag.tag_id` → `tags.id` (onDelete: cascade)

Evidence: Migration files in [database/migrations/](database/migrations/)

### Unique Constraints
- Users: email (global)
- Galleries: slug (global)
- Categories: slug (global)
- Tags: name, slug (global)
- gallery_tag: (gallery_id, tag_id) composite unique

### Timestamps
- All models track `created_at`, `updated_at`
- Soft-deleted models track `deleted_at`
- Ban history tracks `created_at` (action timestamp)
- Media tracks `uploaded_at` (used for path reconstruction)

---

## UNKNOWN / NOT FOUND IN CODE
- Multi-tenancy (seems to be single-tenant)
- Scheduled jobs/background tasks (queue config exists but no job classes visible)
- Email notification triggers (mail config present but no mailable classes)
- Bulk operations (bulk user import/export)
- Audit logging (beyond ban history)
- Data retention policies (soft deletes are permanent otherwise)
- Image optimization service workers or CDN integration

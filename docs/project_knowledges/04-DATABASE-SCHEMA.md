# Database Schema & Data Model

## Database Configuration

**Default Connection**: SQLite  
**Alternative**: MySQL  
**Evidence**: [config/database.php](config/database.php) - default is `sqlite`

```php
'default' => env('DB_CONNECTION', 'sqlite'),
'database' => env('DB_DATABASE', database_path('database.sqlite')),
```

**Development Default Path**: `database/database.sqlite`

---

## Complete Schema Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         CORE ENTITIES                                    │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  users                galaxies              categories                   │
│  ┌──────────────┐   ┌──────────────┐      ┌──────────────┐              │
│  │ id (PK)      │   │ id (PK)      │      │ id (PK)      │              │
│  │ name         │   │ title        │      │ name         │              │
│  │ email (UQ)   │   │ slug (UQ)    │◄─────│ slug (UQ)    │              │
│  │ password     │   │ description  │      │ description  │              │
│  │ phone        │   │ category_id  │-┐    │ is_active    │              │
│  │ profile_img  │   │ is_active    │ │    │ ct_id (FK)   │              │
│  │ is_banned    │   │ is_public    │ │    │ parent_id    │◄─┐           │
│  │ is_active    │   │ cover_id     │ │    └──────────────┘  │           │
│  │ created_at   │   │ item_count   │ │                      │           │
│  │ updated_at   │   │ created_at   │ │    ┌──────────────┐  │           │
│  │ deleted_at   │   │ updated_at   │ │    │ category_    │  │           │
│  └──────────────┘   │ deleted_at   │ │    │ types        │  │           │
│       │             └──────────────┘ │    │ ┌──────────┐ │  │           │
│       │                   │          │    │ │ id (PK)  │ │  │           │
│       ↓                   ├──────────┘    │ │ name     │ │  │           │
│                           │               │ │ slug     │ │  │           │
│   roles                   ↓               │ │ c_at     │ │  │           │
│ ┌──────────────┐   media              │ │ u_at     │ │  │           │
│ │ id (PK)      │  ┌──────────────┐   │ │ └──────────┘ │  │           │
│ │ name         │  │ id (PK)      │   │ └──────────────┘  │           │
│ │ guard_name   │  │ gallery_id   │───│────────────────────┘           │
│ │ updated_at   │  │ filename     │                                      │
│ └──────────────┘  │ extension    │   tags                              │
│       │           │ mime_type    │  ┌──────────────┐                   │
│       ↓           │ size         │  │ id (PK)      │                   │
│                   │ alt_text     │  │ name (UQ)    │                   │
│ permissions       │ sort_order   │  │ slug (UQ)    │                   │
│ ┌──────────────┐  │ uploaded_at  │  │ created_at   │                   │
│ │ id (PK)      │  │ is_cover     │  │ updated_at   │                   │
│ │ name         │  │ created_at   │  └──────────────┘                   │
│ │ guard_name   │  │ updated_at   │        │                            │
│ │ c_at         │  └──────────────┘        ↓                            │
│ │ u_at         │                                                        │
│ └──────────────┘   user_ban_histories    gallery_tag (Junction)        │
│                   ┌──────────────┐      ┌──────────────┐               │
│                   │ id (PK)      │      │ id (PK)      │               │
│                   │ user_id (FK) │      │ gallery_id   │               │
│                   │ action       │      │ tag_id       │               │
│                   │ reason       │      │ created_at   │               │
│                   │ banned_until │      │ updated_at   │               │
│                   │ is_forever   │      │ UQ: (g,t)    │               │
│                   │ performed_by │      └──────────────┘               │
│                   │ c_at         │                                     │
│                   │ u_at         │                                     │
│                   └──────────────┘                                     │
│                                                                        │
│  model_has_roles (Junction)     role_has_permissions (Junction)       │
│  ┌──────────────┐               ┌──────────────────┐                  │
│  │ role_id (FK) │               │ role_id (FK)     │                  │
│  │ model_id (FK)│               │ permission_id(FK)│                  │
│  │ model_type   │               │ created_at       │                  │
│  └──────────────┘               └──────────────────┘                  │
│                                                                        │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Complete Table Definitions

### users

**Purpose**: System user accounts with authentication and profile data

| Column | Type | Nullable | Unique | Indexed | Default | Notes |
|--------|------|----------|--------|---------|---------|-------|
| id | bigint | NO | YES (PK) | YES | auto | Primary key |
| name | varchar(255) | NO | NO | NO | - | User display name |
| email | varchar(255) | NO | YES | YES | - | Unique email for login |
| email_verified_at | timestamp | YES | NO | NO | NULL | Laravel standard |
| password | varchar(255) | NO | NO | NO | - | Hashed password (Model cast) |
| remember_token | varchar(100) | YES | NO | NO | NULL | Laravel session token |
| phone | varchar(255) | YES | NO | NO | NULL | Contact phone number |
| profile_image | varchar(255) | YES | NO | NO | NULL | Path to profile image file |
| is_banned | boolean | NO | NO | NO | false | User is banned |
| is_active | boolean | NO | NO | NO | false | Account is active |
| created_at | timestamp | NO | NO | YES | now() | Creation timestamp |
| updated_at | timestamp | NO | NO | NO | now() | Last update timestamp |
| deleted_at | timestamp | YES | NO | YES | NULL | Soft delete timestamp |

**Foreign Keys**: None (auth pivot is in model_has_roles)

**Constraints**:
- email: UNIQUE
- Soft deletes via deleted_at

**Evidence**: [database/migrations/0001_01_01_000000_create_users_table.php](database/migrations/0001_01_01_000000_create_users_table.php), [database/migrations/2026_02_01_133832_add_phone_field_to_users_table.php](database/migrations/2026_02_01_133832_add_phone_field_to_users_table.php)

---

### galleries

**Purpose**: Collections/albums of images with metadata

| Column | Type | Nullable | Unique | Indexed | Default | Notes |
|--------|------|----------|--------|---------|---------|-------|
| id | bigint | NO | YES (PK) | YES | auto | Primary key |
| title | varchar(255) | NO | NO | NO | - | Gallery name |
| slug | varchar(255) | NO | YES | YES | - | URL-friendly identifier |
| description | text | YES | NO | NO | NULL | Gallery description |
| category_id | bigint | YES | NO | YES | NULL | FK to categories |
| is_active | boolean | NO | NO | NO | true | Gallery visibility (active) |
| is_public | boolean | NO | NO | NO | true | Public/private flag |
| cover_id | bigint | YES | NO | NO | NULL | FK to media (cover image) |
| item_count | integer | NO | NO | NO | 0 | Cached count of media items |
| created_at | timestamp | NO | NO | YES | now() | Creation timestamp |
| updated_at | timestamp | NO | NO | NO | now() | Last update timestamp |
| deleted_at | timestamp | YES | NO | YES | NULL | Soft delete timestamp |

**Foreign Keys**:
- category_id → categories(id) [onDelete: set null]

**Indexes**:
- slug (unique)
- category_id
- created_at (for sorting)

**Constraints**:
- slug: UNIQUE
- Soft deletes via deleted_at

**Evidence**: [database/migrations/2026_02_08_135549_create_galleries_table.php](database/migrations/2026_02_08_135549_create_galleries_table.php)

---

### media

**Purpose**: Individual image files belonging to galleries with multiple size variants

| Column | Type | Nullable | Unique | Indexed | Default | Notes |
|--------|------|----------|--------|---------|---------|-------|
| id | bigint | NO | YES (PK) | YES | auto | Primary key |
| gallery_id | bigint | YES | NO | YES | NULL | FK to galleries (onDelete: cascade) |
| filename | varchar(255) | NO | NO | NO | - | File path/name (relative to storage) |
| extension | varchar(10) | NO | NO | NO | webp | File extension (always webp) |
| mime_type | varchar(255) | NO | NO | NO | image/webp | MIME type (always image/webp) |
| size | bigint | NO | NO | NO | - | File size in bytes |
| alt_text | varchar(255) | YES | NO | NO | NULL | SEO alt text |
| sort_order | integer | NO | NO | NO | 0 | Display order in gallery |
| uploaded_at | timestamp | NO | NO | YES | now() | Upload timestamp (used for path) |
| is_cover | boolean | NO | NO | NO | false | Is this the gallery cover? |
| created_at | timestamp | NO | NO | YES | now() | Record creation timestamp |
| updated_at | timestamp | NO | NO | NO | now() | Last update timestamp |

**Foreign Keys**:
- gallery_id → galleries(id) [onDelete: cascade]

**Indexes**:
- gallery_id
- uploaded_at (for timestamped paths)

**Evidence**: [database/migrations/2026_02_08_135550_create_media_table.php](database/migrations/2026_02_08_135550_create_media_table.php)

**Related Migration**: [database/migrations/2026_02_12_000000_move_gallery_cover_to_media.php](database/migrations/2026_02_12_000000_move_gallery_cover_to_media.php) - moved cover reference from galleries to media

---

### categories

**Purpose**: Hierarchical organization of content (e.g., Gallery types)

| Column | Type | Nullable | Unique | Indexed | Default | Notes |
|--------|------|----------|--------|---------|---------|-------|
| id | bigint | NO | YES (PK) | YES | auto | Primary key |
| category_type_id | bigint | NO | NO | YES | - | FK to category_types (onDelete: cascade) |
| parent_id | bigint | YES | NO | YES | NULL | FK to categories (self-ref, onDelete: set null) |
| name | varchar(255) | NO | NO | NO | - | Category name |
| slug | varchar(255) | NO | YES | YES | - | URL identifier (globally unique) |
| description | text | YES | NO | NO | NULL | Category description |
| is_active | boolean | NO | NO | NO | true | Category is active |
| created_at | timestamp | NO | NO | YES | now() | Creation timestamp |
| updated_at | timestamp | NO | NO | NO | now() | Last update timestamp |

**Foreign Keys**:
- category_type_id → category_types(id) [onDelete: cascade]
- parent_id → categories(id) [onDelete: set null] (self-referential for hierarchy)

**Indexes**:
- slug (unique)
- (category_type_id, slug) - composite index
- parent_id (for hierarchy traversal)
- is_active (for filtering)

**Constraints**:
- slug: UNIQUE globally
- Supports unlimited nesting via parent_id

**Evidence**: [database/migrations/2026_02_07_055222_create_categories_table.php](database/migrations/2026_02_07_055222_create_categories_table.php)

---

### category_types

**Purpose**: Classifiers for categories (e.g., Gallery, Article, Product)

| Column | Type | Nullable | Unique | Indexed | Default | Notes |
|--------|------|----------|--------|---------|---------|-------|
| id | bigint | NO | YES (PK) | YES | auto | Primary key |
| name | varchar(255) | NO | NO | NO | - | Type name (e.g., "Gallery") |
| slug | varchar(255) | NO | YES | YES | - | URL identifier |
| created_at | timestamp | NO | NO | NO | now() | Creation timestamp |
| updated_at | timestamp | NO | NO | NO | now() | Last update timestamp |

**Constraints**:
- slug: UNIQUE

**Evidence**: [database/migrations/2026_02_07_055218_create_category_types_table.php](database/migrations/2026_02_07_055218_create_category_types_table.php)

---

### tags

**Purpose**: Simple labels for gallery organization

| Column | Type | Nullable | Unique | Indexed | Default | Notes |
|--------|------|----------|--------|---------|---------|-------|
| id | bigint | NO | YES (PK) | YES | auto | Primary key |
| name | varchar(255) | NO | YES | NO | - | Tag name (unique) |
| slug | varchar(255) | NO | YES | YES | - | URL identifier (unique) |
| created_at | timestamp | NO | NO | NO | now() | Creation timestamp |
| updated_at | timestamp | NO | NO | NO | now() | Last update timestamp |

**Constraints**:
- name: UNIQUE
- slug: UNIQUE

**Indexes**:
- name
- slug

**Evidence**: [database/migrations/2026_02_12_000001_create_tags_table.php](database/migrations/2026_02_12_000001_create_tags_table.php)

---

### gallery_tag (Junction Table)

**Purpose**: Many-to-many relationship between galleries and tags

| Column | Type | Nullable | Unique | Indexed | Notes |
|--------|------|----------|--------|---------|-------|
| id | bigint | NO | YES (PK) | YES | Primary key |
| gallery_id | bigint | NO | NO | YES | FK to galleries (onDelete: cascade) |
| tag_id | bigint | NO | NO | YES | FK to tags (onDelete: cascade) |
| created_at | timestamp | NO | NO | NO | Creation timestamp |
| updated_at | timestamp | NO | NO | NO | Last update timestamp |

**Foreign Keys**:
- gallery_id → galleries(id) [onDelete: cascade]
- tag_id → tags(id) [onDelete: cascade]

**Constraints**:
- (gallery_id, tag_id): UNIQUE - prevent duplicate tags on same gallery

**Indexes**:
- gallery_id
- tag_id

**Evidence**: [database/migrations/2026_02_12_000002_create_gallery_tag_table.php](database/migrations/2026_02_12_000002_create_gallery_tag_table.php)

---

### user_ban_histories

**Purpose**: Audit trail of user ban/unban actions with reasons and durations

| Column | Type | Nullable | Unique | Indexed | Default | Notes |
|--------|------|----------|--------|---------|---------|-------|
| id | bigint | NO | YES (PK) | YES | auto | Primary key |
| user_id | bigint | NO | NO | YES | - | FK to users (onDelete: cascade) |
| action | enum | NO | NO | YES | - | 'ban' or 'unban' |
| reason | text | NO | NO | NO | - | Reason for action |
| banned_until | timestamp | YES | NO | NO | NULL | Expiry timestamp (if temporary) |
| is_forever | boolean | NO | NO | NO | false | Is ban permanent? |
| performed_by | bigint | YES | NO | NO | NULL | FK to users (admin who performed, onDelete: set null) |
| created_at | timestamp | NO | NO | YES | now() | Action timestamp |
| updated_at | timestamp | NO | NO | NO | now() | Last update timestamp |

**Foreign Keys**:
- user_id → users(id) [onDelete: cascade]
- performed_by → users(id) [onDelete: set null]

**Indexes**:
- (user_id, created_at) - for history queries
- action - for filtering bans vs unbans
- performed_by

**Usage**: Query to show all bans for a specific user in chronological order

**Evidence**: [database/migrations/2026_01_31_074611_create_user_ban_histories_table.php](database/migrations/2026_01_31_074611_create_user_ban_histories_table.php)

---

### roles (Spatie Permission)

**Purpose**: Authorization roles for grouping permissions

| Column | Type | Nullable | Unique | Indexed | Notes |
|--------|------|----------|--------|---------|-------|
| id | bigint | NO | YES (PK) | YES | Primary key |
| name | varchar(255) | NO | YES | YES | Role name (e.g., "admin", "moderator") |
| guard_name | varchar(255) | NO | NO | NO | Guard context (default "web" or "api") |
| created_at | timestamp | NO | NO | NO | Creation timestamp |
| updated_at | timestamp | NO | NO | NO | Last update timestamp |

**Constraints**:
- name: UNIQUE per guard_name

**Evidence**: Standard Spatie Permission table (created via migration)

---

### permissions (Spatie Permission)

**Purpose**: Individual permissions with dot-notation naming

| Column | Type | Nullable | Unique | Indexed | Notes |
|--------|------|----------|--------|---------|-------|
| id | bigint | NO | YES (PK) | YES | Primary key |
| name | varchar(255) | NO | YES | YES | Permission name (e.g., "user_management.view") |
| guard_name | varchar(255) | NO | NO | NO | Guard context |
| created_at | timestamp | NO | NO | NO | Creation timestamp |
| updated_at | timestamp | NO | NO | NO | Last update timestamp |

**Naming Convention**: `{entity}.{action}`
- Examples:
  - user_management.view
  - user_management.add
  - user_management.edit
  - user_management.delete
  - user_management.ban
  - gallery_management.view
  - role_management.view
  - category_management.add

**Evidence**: [app/Services/PermissionService.php](app/Services/PermissionService.php) - references dot notation

---

### model_has_roles (Spatie Permission Junction)

**Purpose**: Associates users (or other models) to roles

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| role_id | bigint | NO | FK to roles |
| model_id | bigint | NO | FK to users (or other model) |
| model_type | varchar(255) | NO | Model class (usually "App\Models\User") |

**Purpose**: Many-to-many users-roles relationship
**Example**: User ID 5 is assigned to Role ID 2 (admin)

**Evidence**: Spatie Permission standard table

---

### role_has_permissions (Spatie Permission Junction)

**Purpose**: Associates roles to permissions

| Column | Type | Notes |
|--------|------|-------|
| role_id | bigint | FK to roles |
| permission_id | bigint | FK to permissions |

**Purpose**: Many-to-many roles-permissions relationship
**Example**: Role "admin" has Permission "user_management.delete"

---

## Data Relationships Summary

```
┌─ User
│  ├─ hasMany: roles (via model_has_roles)
│  ├─ hasMany: permissions (via model_has_permissions, rarely used)
│  ├─ hasMany: user_ban_histories (user_id)
│  ├─ hasMany: user_ban_histories as performed (performed_by)
│  └─ Softdeletes: deleted_at
│
├─ Gallery
│  ├─ belongsTo: category (category_id)
│  ├─ hasMany: media (gallery_id)
│  ├─ hasOne: cover (media where is_cover=true)
│  ├─ belongsToMany: tags (via gallery_tag)
│  ├─ scopeActive: where is_active=true
│  ├─ scopePublic: where is_public=true
│  └─ Softdeletes: deleted_at
│
├─ Media
│  └─ belongsTo: gallery (gallery_id)
│
├─ Category
│  ├─ belongsTo: type (category_type_id)
│  ├─ belongsTo: parent (parent_id, self-ref)
│  ├─ hasMany: children (parent_id, self-ref)
│  └─ scopeActive: where is_active=true
│
├─ CategoryType
│  └─ hasMany: categories (category_type_id)
│
├─ Tag
│  └─ belongsToMany: galleries (via gallery_tag)
│
├─ Role (Spatie)
│  ├─ belongsToMany: permissions (via role_has_permissions)
│  ├─ morphedByMany: users (via model_has_roles)
│  └─ hasMany: model_has_roles
│
├─ Permission (Spatie)
│  ├─ belongsToMany: roles (via role_has_permissions)
│  ├─ morphedByMany: users (via model_has_permissions, rarely used)
│  └─ hasMany: role_has_permissions
│
└─ UserBanHistory
   ├─ belongsTo: user (user_id)
   └─ belongsTo: performedBy as performer (performed_by)
```

---

## Indexes Strategy

**Indexed Columns for Query Performance**:

| Table | Column(s) | Type | Purpose |
|-------|-----------|------|---------|
| users | email | UNIQUE indexed | Login lookups (authenticate) |
| users | created_at | indexed | Sort by date |
| users | deleted_at | indexed | Filter soft-deleted |
| galleries | slug | UNIQUE indexed | Direct gallery lookup |
| galleries | category_id | indexed | Filter by category |
| galleries | created_at | indexed | Sort by date |
| galleries | deleted_at | indexed | Filter soft-deleted |
| media | gallery_id | indexed | Get media for gallery |
| media | uploaded_at | indexed | Reconstruct path dates |
| categories | slug | UNIQUE indexed | Direct lookup |
| categories | (category_type_id, slug) | composite | Type+slug lookups |
| categories | parent_id | indexed | Tree traversal |
| categories | is_active | indexed | Active category filtering |
| tags | slug | UNIQUE indexed | Tag lookup |
| gallery_tag | gallery_id | indexed | Tags for gallery |
| gallery_tag | tag_id | indexed | Galleries for tag |
| gallery_tag | (gallery_id, tag_id) | UNIQUE | Prevent duplicates |
| user_ban_histories | (user_id, created_at) | composite | Ban history timeline |
| user_ban_histories | action | indexed | Ban vs unban filtering |

---

## Migration Execution Order

Based on filename timestamps and dependencies:

1. `0001_01_01_000000_create_users_table`
2. `0001_01_01_000001_create_cache_table` (Laravel standard)
3. `0001_01_01_000002_create_jobs_table` (Queue support)
4. `2026_01_15_152640_create_permission_tables` (Spatie - creates roles, permissions, pivots)
5. `2026_01_16_074940_create_personal_access_tokens_table` (Sanctum)
6. `2026_01_31_041410_add_ban_fields_back_to_users_table` (Add is_banned, is_active)
7. `2026_01_31_074611_create_user_ban_histories_table` (Ban audit)
8. `2026_02_01_133832_add_phone_field_to_users_table` (Add phone)
9. `2026_02_07_055218_create_category_types_table` (Classifiers)
10. `2026_02_07_055222_create_categories_table` (Hierarchical categories)
11. `2026_02_08_135549_create_galleries_table` (Galleries)
12. `2026_02_08_135550_create_media_table` (Media/images)
13. `2026_02_12_000000_move_gallery_cover_to_media` (Refactor cover to media)
14. `2026_02_12_000001_create_tags_table` (Tags)
15. `2026_02_12_000002_create_gallery_tag_table` (Many-to-many)

---

## Data Integrity Notes

### Cascade Deletes
- Deleting a gallery cascades to media records
- Deleting a tag cascades to gallery_tag associations
- Deleting a user cascades to user_ban_histories

### Orphaning Allowed
- Deleting a category sets orphaned galleries' category_id to NULL (not cascade)
- Deleting a performing user sets ban_history.performed_by to NULL

### Soft Deletes
- Users, Galleries support soft deletes (deleted_at)
- Queries automatically exclude deleted records unless `withTrashed()` used
- Deleted users can be restored (Model includes `restore()` method)

---

## UNKNOWN / NOT FOUND IN CODE
- Database views (none created in migrations)
- Stored procedures (none visible)
- Triggers (none visible)
- Partitioning strategy for large tables
- Backup/restoration procedures
- Read replicas or replication lag handling

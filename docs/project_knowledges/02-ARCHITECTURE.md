# Architecture & Structure

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Vue.js Single Page App                     │
│              (TypeScript + Pinia + Vue Router)               │
│        [resources/js/vue3_dashboard_admin/]                  │
└─────────────────────────────────────────┬─────────────────────┘
                                          │
                    HTTP/REST API (Sanctum Bearer Tokens)
                                          │
┌─────────────────────────────────────────┼─────────────────────┐
│                  Laravel 12 Backend                            │
│  ┌──────────────────────────────────────┴──────────────────┐  │
│  │ API Routes (routes/api.php)                            │  │
│  │  /api/v1/users, /roles, /categories, /galleries       │  │
│  └───────────────────────┬────────────────────────────────┘  │
│                          │                                    │
│  ┌───────────────────────┴────────────────────────────────┐  │
│  │        Controllers (app/Http/Controllers/Api)          │  │
│  │   AuthController, UserController, GalleryController   │  │
│  └───────────────────────┬────────────────────────────────┘  │
│                          │                                    │
│  ┌───────────────────────┴────────────────────────────────┐  │
│  │    Service Layer (app/Services/)                      │  │
│  │  AuthService, UserService, GalleryService, etc.       │  │
│  │  - Business logic                                     │  │
│  │  - Caching (version-based)                           │  │
│  │  - Image processing (Intervention)                    │  │
│  └───────────────────────┬────────────────────────────────┘  │
│                          │                                    │
│  ┌───────────────────────┴────────────────────────────────┐  │
│  │    Models (Eloquent ORM)                              │  │
│  │  User, Gallery, Media, Category, Tag,                 │  │
│  │  UserBanHistory, Role, Permission (Spatie)            │  │
│  └───────────────────────┬────────────────────────────────┘  │
│                          │                                    │
│  ┌───────────────────────┴────────────────────────────────┐  │
│  │     Middleware                                        │  │
│  │  auth:sanctum, check.user.status, permission          │  │
│  └───────────────────────┬────────────────────────────────┘  │
└─────────────────────────────────────────┼─────────────────────┘
                                          │
            ┌──────────────────────────────┼──────────────────────────┐
            │                              │                          │
       Database                   File Storage                   Cache Layer
    (SQLite/MySQL)           (storage/app/public/             (Configurable)
                              uploads/)

```

## Directory Structure & Purpose

### Root Level Structure

```
laravel/dashboard1/
├── app/                          # Application source code
│   ├── Console/                  # Artisan commands
│   ├── Contracts/                # Interfaces/contracts
│   ├── Exceptions/               # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   └── Managements/
│   │   │   │       ├── UserController.php
│   │   │   │       ├── RoleController.php
│   │   │   │       ├── PermissionController.php
│   │   │   │       ├── CategoryController.php
│   │   │   │       ├── GalleryController.php
│   │   │   │       └── TagController.php
│   │   │   └── Web/              # Web routes controllers (if any)
│   │   ├── Middleware/
│   │   │   └── CheckUserStatus.php   # Validates user is active & not banned
│   │   └── Requests/                 # Form validation requests
│   ├── Models/
│   │   ├── User.php              # Users with Sanctum tokens, Spatie roles
│   │   ├── Gallery.php           # Gallery collections
│   │   ├── Media.php             # Gallery media assets
│   │   ├── Category.php          # Hierarchical categories
│   │   ├── CategoryType.php      # Category type classifier
│   │   ├── Tag.php               # Gallery tags
│   │   ├── UserBanHistory.php    # Audit trail for bans
│   │   └── UserAccountStatus.php # (unclear - likely unused)
│   ├── Providers/                # Service providers (app bootstrap)
│   ├── Services/                 # Business logic layer
│   │   ├── AuthService.php
│   │   ├── UserService.php
│   │   ├── GalleryService.php
│   │   ├── CategoryService.php
│   │   ├── RoleService.php
│   │   ├── PermissionService.php
│   │   ├── ProtectionService.php    # Protects critical accounts/roles
│   │   └── UserBanHistoryService.php
│   └── Traits/
│       └── CanVersionCache.php   # Cache versioning helper
├── bootstrap/                    # Framework initialization
├── config/                       # Configuration files
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── permission.php            # Spatie permission config
│   ├── protected_entities.php    # Protected accounts config
│   ├── queue.php
│   ├── sanctum.php
│   ├── services.php
│   ├── session.php
├── database/
│   ├── factories/                # Eloquent factories for testing
│   ├── migrations/               # Database schema migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_01_31_041410_add_ban_fields_back_to_users_table.php
│   │   ├── 2026_01_31_074611_create_user_ban_histories_table.php
│   │   ├── 2026_02_07_055218_create_category_types_table.php
│   │   ├── 2026_02_07_055222_create_categories_table.php
│   │   ├── 2026_02_08_135549_create_galleries_table.php
│   │   ├── 2026_02_08_135550_create_media_table.php
│   │   ├── 2026_02_12_000001_create_tags_table.php
│   │   └── 2026_02_12_000002_create_gallery_tag_table.php
│   └── seeders/                  # Database seeders
├── docs/                         # Project documentation
│   ├── gallery_implementation_plan.md
│   └── project_knowledge/        # <-- NEW: Comprehensive docs
├── public/                       # Public-facing files
│   ├── index.php                 # Entry point
│   ├── storage                   # Symlink to storage/app/public
│   └── build/                    # Compiled frontend assets
├── resources/
│   ├── css/                      # Tailwind CSS
│   ├── views/
│   │   └── app.blade.php         # SPA entry point
│   └── js/
│       └── vue3_dashboard_admin/
│           ├── app.ts            # Frontend app entry
│           ├── App.vue           # Root component
│           ├── components/       # Reusable Vue components
│           ├── composables/      # Vue 3 composables
│           ├── config/           # Frontend configuration
│           ├── layouts/          # Layout components
│           ├── mocks/            # Mock data for development
│           ├── router/           # Vue Router setup
│           ├── stores/           # Pinia stores
│           │   └── auth.ts       # Auth state management
│           ├── types/            # TypeScript type definitions
│           ├── utils/            # Utility functions
│           └── views/            # Page components (routed)
├── routes/
│   ├── api.php                   # API routes (/api/v1/*)
│   ├── console.php               # Console commands
│   └── web.php                   # Web routes (serves SPA)
├── storage/
│   ├── app/
│   │   └── public/
│   │       └── uploads/
│   │           └── gallery/      # Gallery images stored here
│   ├── framework/
│   ├── logs/
│   └── cache/
├── tests/                        # Test suites
│   ├── Feature/                  # Feature/integration tests
│   ├── Unit/                     # Unit tests
│   └── TestCase.php              # Base test class
├── vendor/                       # Composer dependencies
├── artisan                       # Laravel CLI
├── composer.json
├── package.json
├── vite.config.js
├── tsconfig.json
├── tailwind.config.js
└── phpunit.xml
```

## Data Flow Diagrams

### 1. Authentication Flow

```
Frontend                          Backend
─────────                        ────────
User Input
  │
  ├─> POST /login               
      (credentials)      ──────> AuthController::login()
                           │
                           ├─> AuthService::authenticate()
                           │   ├─ Validate email/password
                           │   ├─ Check is_active = true
                           │   ├─ Check is_banned = false
                           │   └─ Generate Sanctum token
                           │
                    <────  Return: {token, user data}
  │
  ├─> Store token in localStorage
  │
  ├─> Store user data in localStorage
  │
  └─> Set Authorization header for future requests:
      "Authorization: Bearer {token}"
```

### 2. User Management CRUD Flow

```
Frontend (Vue form)                Backend
───────────────                    ────────
1. User submits form
   │
   ├─> POST /api/v1/users         
       (user data + file)  ──────> UserController::store()
                             │
                             ├─> Validates via StoreUserRequest
                             │
                             ├─> UserService::createUser()
                             │   ├─ Process profile image if present
                             │   ├─ Hash password
                             │   ├─ Create user record
                             │   ├─ Assign roles/permissions
                             │   └─ Cache invalidation
                             │
                    <────  201 Created + user data
   │
   └─> UI updates, show success message

2. List users with filters
   │
   ├─> GET /api/v1/users
       ?search=john&role=admin
       &page=1
             ──────> UserController::index()
                       │
                       ├─> UserService::getFilteredPaginatedUsers()
                       │   ├─ Check versioned cache key
                       │   ├─ If cached, return
                       │   ├─ Else: query DB with filters
                       │   │   ├─ filter by search term
                       │   │   ├─ filter by role (join on roles table)
                       │   │   ├─ filter by status (active/banned)
                       │   │   ├─ paginate results
                       │   │   └─ format user data
                       │   └─ Store in cache (1 hour TTL)
                       │
                    <──  200 OK + paginated users
   │
   └─> Render table with pagination
```

### 3. Gallery Image Upload & Processing Flow

```
Frontend (Vue cropper)               Backend
─────────────────────                ───────
1. User selects image + crops
   │
   ├─ FormData:
   │  - title
   │  - description
   │  - category_id
   │  - visibility (public/private)
   │  - file (image)
   │  - crop: {x, y, width, height}
   │
   ├─> POST /api/v1/galleries
       (multipart/form-data)  ──────> GalleryController::store()
                                │
                                ├─> GalleryService::createGallery()
                                │   │
                                │   ├─ Begin transaction
                                │   ├─ Generate unique slug
                                │   ├─ Create Gallery record
                                │   │
                                │   ├─ processAndStoreCover():
                                │   │  └─ Load image via Intervention
                                │   │  ├─ Apply crop if provided
                                │   │  ├─ Resize to 1200x900
                                │   │  ├─ Convert to WebP
                                │   │  ├─ Store to uploads/gallery/{slug}/covers/1200x900/{date}/{file}.webp
                                │   │  ├─ Create Media record (is_cover=true)
                                │   │  │
                                │   │  ├─ Resize to 400x400
                                │   │  ├─ Convert to WebP
                                │   │  └─ Store to uploads/gallery/{slug}/covers/400x400/{date}/{file}.webp
                                │   │
                                │   ├─ Handle tags (create if missing):
                                │   │  ├─ For each tag: Tag::firstOrCreate()
                                │   │  └─ Sync gallery-tag relationship
                                │   │
                                │   └─ Commit transaction
                                │
                    <──────  201 Created + gallery data
   │
   └─> Success message, redirect to gallery view
```

## Design Patterns Used

### 1. **Service Layer Pattern** (Evidence: [app/Services/](app/Services/))
- Business logic extracted from controllers
- Services are injected via constructor DI
- Example: `AuthService` handles authentication decisions
- Benefits: Reusable logic, testable, separation of concerns

### 2. **Repository-Like Pattern** (Evidence: Models + Services)
- Models define data structure + relationships
- Services provide query/business logic around models
- Example: `UserService::getFilteredPaginatedUsers()` vs raw Model usage

### 3. **Trait-Based Mixins** (Evidence: [app/Traits/CanVersionCache.php](app/Traits/CanVersionCache.php))
- Shared behavior extracted to traits
- Used by services for cache versioning
- Services declare: `use CanVersionCache`

### 4. **Middleware Decoration** (Evidence: [routes/api.php](routes/api.php), [app/Http/Middleware/CheckUserStatus.php](app/Http/Middleware/CheckUserStatus.php))
- Route-level middleware for cross-cutting concerns
- Pattern: `middleware(['auth:sanctum', 'check.user.status', 'permission:...'])`
- Executes before controller action

### 5. **Soft Deletes** (Evidence: User.php, Gallery.php use SoftDeletes)
- Data marked as deleted but not actually removed
- Queries auto-exclude soft-deleted records unless explicitly included
- Enables restore functionality

### 6. **Many-to-Many Relationships** (Evidence: Gallery ↔ Tag)
- Implemented via junction table `gallery_tag`
- Laravel handles via `belongsToMany()` relationship
- Sync method for bulk assignment

### 7. **Polymorphic Soft Deletes** (Evidence: User soft deletes)
- Allows tracking soft-deleted records
- `deleted_at` timestamp distinguishes active from deleted

## Request-Response Cycle

### Typical API Request Lifecycle

```
1. Frontend sends HTTP request with Bearer token
   (e.g., GET /api/v1/users?page=1)

2. Request hits web server (Laravel):
   ├─ Routes matched in routes/api.php
   ├─ Middleware stack applied:
   │  ├─ auth:sanctum → validates token
   │  ├─ check.user.status → checks is_active & is_banned
   │  └─ permission:{permission} → checks Spatie permission
   └─ If all pass, route to controller

3. Controller receives request:
   ├─ Validates input via Form Request classes
   ├─ Calls Service method
   └─ Returns JSON response

4. Service processes:
   ├─ Checks cache
   ├─ If miss, queries database
   ├─ Formats data
   ├─ Stores in cache
   └─ Returns result

5. Controller returns JSON:
   {
     "message": "Users retrieved successfully",
     "data": [...],
     "meta": {...},
     "filters": {...}
   }

6. Frontend receives response:
   ├─ Updates Pinia store
   ├─ Re-renders component
   └─ Shows data to user
```

## Dependency Injection & Container

- **Container**: Laravel Service Container (built-in)
- **Configuration**: [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php)
- **Usage Pattern**: Type-hint in constructor
  ```php
  public function __construct(UserService $userService, ProtectionService $protectionService)
  {
      $this->userService = $userService;
      $this->protectionService = $protectionService;
  }
  ```

## Error Handling Strategy

### Backend Error Responses
- **Format**: JSON with `message` and optional `error` fields
- **HTTP Status Codes**:
  - 200/201: Success
  - 401: Authentication failed or inactive account
  - 403: Permission denied
  - 404: Resource not found
  - 422: Validation error
  - 500: Server error

Evidence: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php) returns responses with appropriate status codes

### Logging
- All errors logged to `storage/logs/`
- Log includes context: user_id, email, IP, error message
- Evidence: Services use `Log::error()`, `Log::warning()`, `Log::info()`

## Configuration Philosophy

- **Env-Based**: Most configuration via `.env` file
- **Protected Config Files**: [config/protected_entities.php](config/protected_entities.php) defines critical accounts
- **Feature Flags**: Not explicitly used; could be added via config
- **Sanctum Config**: [config/sanctum.php](config/sanctum.php) (Spatie permission-style)

## Frontend State Management

- **Primary Store**: [resources/js/vue3_dashboard_admin/stores/auth.ts](resources/js/vue3_dashboard_admin/stores/auth.ts)
- **State**: token, user, isLoading, error
- **Actions**: login, logout, fetchUser
- **Getters**: isAuthenticated, currentUser, hasPermission(), hasRole()
- **Persistence**: localStorage for token + user data

## Performance Considerations

### Caching
- 1-hour TTL on user lists, roles, categories
- Version-based cache invalidation (cache key includes filter params)
- Manual cache clear endpoints available

### Query Optimization
- Services use `.select()` to limit fields fetched
- `.with()` for eager loading relationships
- Indexes on frequently queried fields (slugs, dates, status)

Evidence: [app/Services/UserService.php](app/Services/UserService.php) lines 47-57 use select() and with()

### Image Optimization
- All gallery images converted to WebP (smaller file size)
- Multi-size variants (1200x900 for featured, 400x400 for thumbnails)
- Stored separately by size and date

## Scalability Notes

### Potential Bottlenecks
1. **Large User Lists**: Pagination mitigates; default 15 per page
2. **Image Storage**: Date-based subfolders prevent filesystem limits
3. **Cache Stampede**: Could occur if many users request same data simultaneously post-cache-expiry
   - Mitigation: Consider implementing cache locks (Laravel feature)

### Horizontal Scaling Considerations
1. **State**: All in database + Redis/Memcached (configurable)
2. **Tokens**: Sanctum tokens store in `personal_access_tokens` table (database-backed)
3. **File Storage**: Centralized `storage/app/public/` must be shared across servers (NFS)

---

**UNKNOWN / NOT FOUND IN CODE**:
- Load balancing configuration
- CDN setup for static/image assets
- Database replication strategy
- API versioning beyond v1 prefix

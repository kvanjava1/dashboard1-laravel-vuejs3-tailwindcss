# Project Overview

## Project Identification
- **Project Name**: Laravel Dashboard Admin (from `config/app.php` - APP_NAME)
- **Framework**: Laravel 12.0
- **PHP Version**: ^8.2
- **Status**: Active Development (Latest framework versions)

## Problem Domain & Purpose

This is an **administrative dashboard system** for managing:
- **Users** with ban/status tracking and role assignment
- **Roles and Permissions** using Spatie permission system
- **Galleries** with media asset management and image optimization
- **Categories** with hierarchical structure (parent-child relationships)
- **Tags** for gallery organization

**Evidence**: 
- Routes file [routes/api.php](routes/api.php) defines endpoints for: `/users`, `/roles`, `/permissions`, `/categories`, `/galleries`
- Gallery implementation plan [docs/gallery_implementation_plan.md](docs/gallery_implementation_plan.md) describes image storage and WebP conversion

## Complete Technology Stack

### Backend
| Component | Details | Evidence |
|-----------|---------|----------|
| **Framework** | Laravel 12.0 | `composer.json` require: `"laravel/framework": "^12.0"` |
| **Language** | PHP 8.2+ | `composer.json` require: `"php": "^8.2"` |
| **Database** | SQLite (default) or MySQL | [config/database.php](config/database.php) - default is `sqlite` |
| **ORM** | Eloquent | Implicit in Laravel; used throughout models |
| **Authentication** | Laravel Sanctum 4.2 | `composer.json`: `"laravel/sanctum": "^4.2"` |
| **Authorization** | Spatie Permission 6.24 | `composer.json`: `"spatie/laravel-permission": "^6.24"` - Used for role/permission management |
| **Image Processing** | Intervention Image 2.7 | `composer.json`: `"intervention/image": "^2.7"` - Used for WebP conversion and resizing |
| **API Rate Limiting** | Laravel native | [routes/api.php](routes/api.php) line 12: `->middleware('throttle:5,15')` |
| **Queue System** | Laravel Queues | `composer.json` includes `queue` config; dev script uses `queue:listen` |
| **Cache** | Configurable (file/database) | Service layer uses `Cache` facade extensively |

### Frontend
| Component | Details | Evidence |
|-----------|---------|----------|
| **Framework** | Vue 3 | `package.json`: `"vue": "^3.5.26"` |
| **Language** | TypeScript | `package.json` dev: `"typescript": "^5.9.3"` |
| **State Management** | Pinia 3.0 | `package.json`: `"pinia": "^3.0.4"` - Root store in [resources/js/vue3_dashboard_admin/stores/auth.ts](resources/js/vue3_dashboard_admin/stores/auth.ts) |
| **Routing** | Vue Router 4.6 | `package.json`: `"vue-router": "^4.6.4"` |
| **Styling** | Tailwind CSS 4.1 | `package.json`: `"tailwindcss": "^4.1.18"` - Config in [tailwind.config.js](tailwind.config.js) |
| **UI Components** | Headless UI + Heroicons | `package.json`: `"@headlessui/vue": "^1.7.23"`, `"@heroicons/vue": "^2.2.0"` |
| **HTTP Client** | Axios 1.13 | `package.json`: `"axios": "^1.13.2"` |
| **Build Tool** | Vite 7.3 | `package.json`: `"vite": "^7.3.1"` - Config in [vite.config.js](vite.config.js) |
| **Image Cropping** | vue-advanced-cropper 2.8 | `package.json`: `"vue-advanced-cropper": "^2.8.9"` |
| **Alerts** | SweetAlert2 11.26 | `package.json`: `"sweetalert2": "^11.26.17"` |

### Development & Testing
| Component | Details | Evidence |
|-----------|---------|----------|
| **Testing (PHP)** | PHPUnit 11.5 | `composer.json` require-dev: `"phpunit/phpunit": "^11.5.3"` |
| **Faker** | FakerPHP 1.23 | `composer.json` require-dev: `"fakerphp/faker": "^1.23"` |
| **Mock** | Mockery 1.6 | `composer.json` require-dev: `"mockery/mockery": "^1.6"` |
| **Post-Build** | Spatie Permission requires publish | config setup via `spatie/laravel-permission` |

## Development Environment Setup
- **Primary Dev Command**: `npm run dev`  
  - Runs concurrently: Laravel server, queue listener, pail logs, Vite dev server
  - Evidence: [composer.json](composer.json) scripts section - `"dev"` script uses `concurrently`
- **Build Command**: `npm run build`
  - Vite production build for frontend
  - Evidence: [package.json](package.json) scripts: `"build": "vite build"`
- **Port Configuration**:
  - Laravel server: 8000 (from context: `php artisan serve --host=0.0.0.0 --port=8000`)
  - Vite dev server: 5174 (from [vite.config.js](vite.config.js) line 19)

## Key Business Entities

### Primary Entities (from Models)
1. **Users** - System accounts with roles/permissions, ban status, profile images
2. **Galleries** - Collections of images with categories, visibility control, cover images
3. **Media** - Individual image assets (WebP format) with multiple size variants
4. **Categories** - Hierarchical organization structure (parent-child support)
5. **Tags** - Labels for gallery organization (many-to-many with galleries)
6. **Roles** - Authorization roles (standard Spatie model)
7. **Permissions** - Fine-grained permissions (standard Spatie model)
8. **UserBanHistory** - Audit trail of user ban/unban actions

Evidence: [app/Models/](app/Models/) contains: User.php, Gallery.php, Media.php, Category.php, Tag.php, UserBanHistory.php

## Core Workflows

### 1. Authentication & Session Management
**Flow**:
1. User submits email/password to `/api/v1/login` → [API route](routes/api.php)
2. `AuthController::login()` calls `AuthService::authenticate()`
3. AuthService validates credentials, checks if user is active and not banned
4. Generates Sanctum token
5. Returns token + user data to frontend

**Evidence**: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php), [app/Services/AuthService.php](app/Services/AuthService.php)

**Status Checks**:
- `is_active` must be true (line 45 in AuthService)
- `is_banned` must be false (line 54 in AuthService)
- Enforced again by middleware: [app/Http/Middleware/CheckUserStatus.php](app/Http/Middleware/CheckUserStatus.php)

### 2. User Management
**Endpoints**: 
- `GET /api/v1/users` - List with filtering/pagination
- `POST /api/v1/users` - Create new user
- `PUT /api/v1/users/{id}` - Update user
- `DELETE /api/v1/users/{id}` - Soft delete
- `POST /api/v1/users/{id}/ban` - Ban user
- `POST /api/v1/users/{id}/unban` - Unban user
- `GET /api/v1/users/{id}/ban-history` - View ban history

**Service Logic**: [app/Services/UserService.php](app/Services/UserService.php) handles:
- Filtering by search, name, email, role, status, date range
- Pagination (default 15 per page)
- Caching with version-based invalidation (1 hour TTL)
- Profile image processing (stored in `storage/profile_images/`)
- Role assignment and removal

**Permissions**: All user endpoints require specific permissions like `user_management.view`, `user_management.add`, etc.
Evidence: [routes/api.php](routes/api.php) lines 23-33

### 3. Gallery Management
**Workflow**: 
1. Admin creates gallery with title, description, category, visibility
2. Optionally uploads cover image with crop coordinates
3. GalleryService processes cover image into 2 variants:
   - `1200x900` (featured, SEO-optimized)
   - `400x400` (thumbnail/grid view)
4. All converted to WebP format
5. Stored in `storage/app/public/uploads/gallery/{slug}/covers/{size}/{Y}/{m}/{d}/{filename}.webp`
6. Gallery slug auto-generated and kept unique
7. Optional tags attached (many-to-many relationship)

**Evidence**: [app/Services/GalleryService.php](app/Services/GalleryService.php), [database/migrations/2026_02_08_135549_create_galleries_table.php](database/migrations/2026_02_08_135549_create_galleries_table.php)

**Image Processing Details** (from GalleryService):
- Uses Intervention Image library
- Defaults to Imagick driver if available, otherwise GD
- Supports server-side cropping via crop coordinates from frontend
- Stores original uploaded filename for tracking

### 4. Category & Tag Organization
**Categories**: Hierarchical (parent-child support)
- Each category belongs to a `CategoryType` (e.g., Gallery, Article)
- Categories filtered by type and status
- Auto-slug generation with timestamp collision prevention
- Cached with 1-hour TTL

**Tags**: Flat structure
- Associated with galleries via `gallery_tag` junction table
- Created on-demand when referenced in gallery creation
- Used for autocomplete in frontend forms

Evidence: [app/Services/CategoryService.php](app/Services/CategoryService.py), [app/Models/Category.php](app/Models/Category.php)

## Security Architecture

### Authentication
- **Method**: API Token (Sanctum)
- **Token Generation**: Per-device (user_agent stored in token name)
- **Token Storage**: Frontend localStorage with `auth_token` key
- **Bearer Token Format**: All API requests include `Authorization: Bearer {token}` header

Evidence: [app/Services/AuthService.php](app/Services/AuthService.php) line 64-65, [routes/api.php](routes/api.php) line 15

### Authorization
- **System**: Spatie Permission
- **Granularity**: Role + Permission based (fine-grained control)
- **Pattern**: `{entity}.{action}` (e.g., `user_management.view`, `gallery_management.add`)
- **Enforcement**: Route middleware checks `permission:` directives

Evidence: [config/permission.php](config/permission.php), [app/Services/PermissionService.php](app/Services/PermissionService.php)

### Protected Accounts
- Certain admin accounts configured as "protected" in [config/protected_entities.php](config/protected_entities.php)
- ProtectionService prevents deletion, role changes, banning of protected accounts
- Default: `super@admin.com` fully protected

Evidence: [app/Services/ProtectionService.php](app/Services/ProtectionService.php), [config/protected_entities.php](config/protected_entities.php)

### Rate Limiting
- Login endpoint: 5 attempts per 15 minutes (`throttle:5,15`)

Evidence: [routes/api.php](routes/api.php) line 12

## Caching Strategy
- **Implementation**: Uses `Cache` facade (configurable backend)
- **Pattern**: Version-based cache keys for automatic invalidation
- **Trait**: `CanVersionCache` provides `getVersionedKey()` helper
- **Default TTL**: 3600 seconds (1 hour) for most operations

Evidence: [app/Traits/CanVersionCache.php](app/Traits/CanVersionCache.php), service files use this

**Clear Cache Endpoints** (manual invalidation):
- `POST /api/v1/users/clear-cache`
- `POST /api/v1/roles/clear-cache`
- `POST /api/v1/categories/clear-cache`

## Logging & Monitoring
- Logs written to storage/logs/
- Log messages include context: user_id, ip, email, timestamp
- Events logged: login success/failure, logout, resource CRUD, errors

Evidence: [app/Services/AuthService.php](app/Services/AuthService.php) uses `Log::info()`, `Log::warning()`, `Log::error()`

## Database
- **Default**: SQLite (`database/database.sqlite`)
- **Alternative**: MySQL support configured
- **Foreign Key Constraints**: Enabled by default
- **Soft Deletes**: Implemented on User, Gallery models

Evidence: [config/database.php](config/database.php) line 18

## Frontend Architecture

### Entry Point
- **File**: [resources/js/vue3_dashboard_admin/app.ts](resources/js/vue3_dashboard_admin/app.ts)
- **Root Component**: [resources/js/vue3_dashboard_admin/App.vue](resources/js/vue3_dashboard_admin/App.vue)
- **Mount Point**: Vite injects compiled bundle into [resources/views/app.blade.php](resources/views/app.blade.php)

### State Management
- **Store**: [resources/js/vue3_dashboard_admin/stores/auth.ts](resources/js/vue3_dashboard_admin/stores/auth.ts)
- **Library**: Pinia
- **Current Implementation**: Auth store manages token, user, permissions

### Routing
- Frontend uses Vue Router for single-page navigation
- Main route: `/management/{any}` returns Vue SPA

Evidence: [routes/web.php](routes/web.php) line 6

## Deployment Considerations

### Environment Variables
- Configured via `.env` file (Laravel standard)
- Key variables: DB_CONNECTION, DB_DATABASE, APP_URL, APP_KEY

### Build Process
1. PHP deps: `composer install`
2. Frontend deps: `npm install`
3. Compile frontend: `npm run build`
4. Migrations: `php artisan migrate`

Evidence: [composer.json](composer.json) `setup` script

### Docker Support
- Project likely runs in Docker (references to `php_dev_nodejs_20` container in context)
- Dev command executed via: `docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run dev'`

## Known/Confirmed Features NOT Present
- ✅ Real-time features: Broadcasting configured but NOT used
- ✅ Email notifications: Services configured but NOT implemented
- ✅ Frontend tests: Framework present but no test files in `tests/` (only TestCase.php)
- ✅ Websocket/Chat: No implementation visible
- ✅ Two-factor authentication: Not visible in code
- ✅ API documentation: No Swagger/OpenAPI files found

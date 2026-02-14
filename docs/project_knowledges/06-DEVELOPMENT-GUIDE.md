# Development Guide

## Local Development Setup

### Prerequisites
- PHP 8.2+
- Composer (PHP dependency manager)
- Node.js 18+ (for frontend build)
- npm or yarn
- Git
- Docker (optional, if using containerized environment)

### Step-by-Step Setup

#### 1. Clone & Install Dependencies
```bash
cd /home/itboms/Developments/php/apps/php8.2/laravel/dashboard1

# Install PHP packages
composer install

# Install frontend packages
npm install
```

#### 2. Environment Configuration
```bash
# Copy .env.example to .env (if not exists)
cp .env.example .env

# Generate application key
php artisan key:generate

# If needed, configure database connection
# Edit .env: DB_CONNECTION=sqlite (default)
```

#### 3. Database Setup
```bash
# Run migrations
php artisan migrate

# Optional: Seed database with demo data
php artisan db:seed
```

#### 4. Create Symbolic Link (for file uploads)
```bash
# Link storage to public
php artisan storage:link
```

#### 5. Start Development Server
```bash
# Start all services concurrently (Laravel, queue, logs, Vite)
npm run dev

# OR individually:
# Terminal 1: Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Vite dev server (frontend hot-reload)
npm run dev

# Terminal 3: Queue listener (for background jobs)
php artisan queue:listen --tries=1

# Terminal 4: Log viewer
php artisan pail --timeout=0
```

**Access**:
- Frontend: [http://localhost:5174/management/login](http://localhost:5174/management/login)
- API: [http://localhost:8000/api/v1](http://localhost:8000/api/v1)

**Evidence**: [composer.json](composer.json) dev script, [vite.config.js](vite.config.js) server config

### Container-Based Development

```bash
# Using Docker (assumed setup exists)
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run dev'
```

---

## Building for Production

### Frontend Build
```bash
npm run build
```

**Output**: Compiled assets in `public/build/`
**Configuration**: [vite.config.js](vite.config.js)

### Backend Setup
```bash
# Install with no dev dependencies
composer install --no-dev

# Optimize autoloader
composer dump-autoload --optimize

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables (Production)
```
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql (or sqlite for production)
DB_HOST=...
DB_NAME=...
DB_USERNAME=...
DB_PASSWORD=...
CACHE_DRIVER=redis (or file)
QUEUE_CONNECTION=database (or redis)
```

---

## Project Structure Reference

### Frontend (`resources/js/vue3_dashboard_admin/`)

**Key Directories**:

```
app.ts                 # Application entry point
App.vue               # Root Vue component
├── components/       # Reusable Vue components
├── composables/      # Vue 3 composables (composition API)
├── config/          # Frontend configuration
│   └── apiRoutes.ts # API endpoint definitions
├── layouts/         # Layout wrapper components
├── mocks/           # Mock data for development
├── router/          # Vue Router configuration
├── stores/          # Pinia stores (state management)
│   └── auth.ts      # Authentication store
├── types/           # TypeScript type definitions
├── utils/           # Utility functions
└── views/           # Routed page components
```

### Backend Structure

**[app/Http/Controllers/Api/](app/Http/Controllers/Api/)**:
- `AuthController.php` - Login/logout/me
- `Managements/UserController.php` - User CRUD
- `Managements/RoleController.php` - Role CRUD
- `Managements/PermissionController.php` - Permission listing
- `Managements/CategoryController.php` - Category CRUD
- `Managements/GalleryController.php` - Gallery CRUD
- `Managements/TagController.php` - Tag options

**[app/Services/](app/Services/)**:
- `AuthService.php` - Authentication logic
- `UserService.php` - User business logic (filtering, creation, updates)
- `RoleService.php` - Role management
- `PermissionService.php` - Permission queries
- `CategoryService.php` - Category hierarchy
- `GalleryService.php` - Gallery creation, image processing
- `ProtectionService.php` - Account/role protection
- `UserBanHistoryService.php` - Ban tracking
- `PermissionService.php` - Permission categorization

**[app/Http/Requests/](app/Http/Requests/)**:
- Form Request validation classes
- Auto-validate incoming data before controller

**[app/Models/](app/Models/)**:
- Eloquent models with relationships
- business scope methods (`scopeActive()`, etc.)

---

## Code Organization Patterns

### Service Layer Pattern
**When** you need to implement business logic:

```php
// In controller:
public function store(StoreUserRequest $request): JsonResponse
{
    $validated = $request->validated();
    $userData = $this->userService->createUser($validated);
    return response()->json([...], 201);
}

// In service:
public function createUser(array $data): array
{
    // Validation, password hashing, role assignment, caching
    // Return formatted data
}
```

### Model Relationships
**Always eager-load** relationships to avoid N+1 queries:

```php
// Good
$users = User::with('roles', 'permissions')->get();

// Bad (N+1 queries)
$users = User::all();
foreach ($users as $user) {
    $roles = $user->roles; // Query per user!
}
```

### Caching Strategy
**For expensive queries**, use version-based cache:

```php
use App\Traits\CanVersionCache;

class UserService {
    use CanVersionCache;
    
    public function getFilteredUsers($filters = []) {
        $cacheKey = $this->getVersionedKey('users', $filters);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;
        
        // Expensive query
        $result = User::with('roles')->get();
        
        Cache::put($cacheKey, $result, 3600);
        return $result;
    }
    
    public function createUser($data) {
        // Create user...
        
        // Invalidate all variations
        $this->invalidateScopeCache('users');
        
        return $formatted;
    }
}
```

### Error Handling
**Always return appropriate HTTP status codes**:

```php
try {
    $data = $this->validate($request->input());
    return response()->json(['user' => $data], 200);
} catch (ValidationException $e) {
    return response()->json(['errors' => $e->errors()], 422);
} catch (AuthenticationException $e) {
    return response()->json(['message' => $e->getMessage()], 401);
} catch (Exception $e) {
    Log::error('Operation failed', ['error' => $e->getMessage()]);
    return response()->json(['message' => 'Server error'], 500);
}
```

---

## Adding New Features

### Feature: New User Role

#### 1. Create Permission (Seed or Migration)

```php
// database/seeders/PermissionSeeder.php
Permission::create(['name' => 'resource.view']);
Permission::create(['name' => 'resource.add']);
Permission::create(['name' => 'resource.edit']);
Permission::create(['name' => 'resource.delete']);
```

**Run**: `php artisan db:seed --class=PermissionSeeder`

#### 2. Create Role and Assign Permissions

```php
// In database seeder or tinker
$role = Role::create(['name' => 'resource_manager']);
$permissions = Permission::whereIn('name', [
    'resource.view',
    'resource.add',
    'resource.edit'
])->pluck('id');
$role->syncPermissions($permissions);
```

#### 3. Create or Update Controller

```php
// app/Http/Controllers/Api/Managements/ResourceController.php
class ResourceController extends Controller {
    public function index(Request $request): JsonResponse {
        // Implementation
    }
    
    public function store(StoreResourceRequest $request): JsonResponse {
        // Implementation
    }
}
```

#### 4. Create Form Request Validation

```php
// app/Http/Requests/Resource/StoreResourceRequest.php
class StoreResourceRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Middleware handles permission check
    }
    
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
```

#### 5. Register Routes

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'check.user.status'])->group(function () {
    Route::get('resources', [ResourceController::class, 'index'])->middleware('permission:resource.view');
    Route::post('resources', [ResourceController::class, 'store'])->middleware('permission:resource.add');
    Route::put('resources/{id}', [ResourceController::class, 'update'])->middleware('permission:resource.edit');
    Route::delete('resources/{id}', [ResourceController::class, 'destroy'])->middleware('permission:resource.delete');
});
```

#### 6. Create Service Layer

```php
// app/Services/ResourceService.php
class ResourceService {
    use CanVersionCache;
    
    private const CACHE_SCOPE = 'resources';
    
    public function getAllResources(array $filters = []): array {
        $cacheKey = $this->getVersionedKey(self::CACHE_SCOPE, $filters);
        $cached = Cache::get($cacheKey);
        if ($cached) return $cached;
        
        // Query logic
        $resources = Resource::filter($filters)->get();
        
        Cache::put($cacheKey, $resources->toArray(), 3600);
        return $resources->toArray();
    }
}
```

#### 7. Add Vue Components

```vue
<!-- resources/js/vue3_dashboard_admin/views/ResourceList.vue -->
<template>
  <div class="container">
    <h1>Resources</h1>
    <!-- Resource table, filters, pagination -->
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { apiRoutes } from '@/config/apiRoutes';

const authStore = useAuthStore();
const resources = ref([]);

const fetchResources = async () => {
  try {
    const response = await fetch(apiRoutes.resources.list, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      }
    });
    const data = await response.json();
    resources.value = data.data;
  } catch (error) {
    console.error('Failed to fetch resources', error);
  }
};

onMounted(() => {
  fetchResources();
});
</script>
```

#### 8. Add Route to Router

```typescript
// resources/js/vue3_dashboard_admin/router/index.ts
const routes = [
  {
    path: '/resources',
    component: () => import('@/views/ResourceList.vue'),
    meta: { requiresAuth: true }
  },
  // ...
];
```

---

## Testing

### Running Tests
```bash
# Run all tests
npm run test  # OR: ./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Feature/UserControllerTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html reports/
```

**Evidence**: [phpunit.xml](phpunit.xml), [composer.json](composer.json) test script

### Writing Tests
```php
// tests/Feature/UserControllerTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    public function test_can_list_users()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $response = $this->actingAs($user)->getJson('/api/v1/users');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }
}
```

---

## Debugging

### Laravel Tinker (REPL)
```bash
php artisan tinker

# Inside tinker:
> $user = User::find(1);
> $user->roles->pluck('name');
> Cache::flush();
```

### Logs
```bash
# Follow real-time logs (via Pail)
php artisan pail --timeout=0

# Or read from file
tail -f storage/logs/laravel.log
```

### Debugging with Debugbar
- Already included in laravel/laravel
- Visible at bottom of every page in dev mode
- Shows queries, timing, cache hits, etc.

### IDE Support (VSCode)
- Install extensions:
  - Laravel Extension Pack
  - Intelephense (PHP)
  - ESLint (JavaScript)
  - Vetur or Vue Language Features (Vue)

---

## Common Commands

### Laravel Artisan
```bash
# Database
php artisan migrate                 # Run migrations
php artisan migrate:rollback        # Rollback last batch
php artisan migrate:refresh         # Rollback & re-run
php artisan db:seed                 # Run seeders
php artisan db:seed --class=PermissionSeeder

# Cache
php artisan cache:clear
php artisan config:cache
php artisan config:clear
php artisan route:cache
php artisan view:cache

# Code Generation
php artisan make:controller UserController
php artisan make:model Gallery -m  # With migration
php artisan make:request StoreUserRequest
php artisan make:service UserService

# Queue
php artisan queue:failed            # View failed jobs
php artisan queue:retry 1           # Retry job ID 1
php artisan queue:listen            # Start listening

# Maintenance
php artisan down                    # Put app in maintenance
php artisan up                      # Bring app back up
```

### Composer
```bash
composer dumpautoload --optimize    # Optimize autoloader
composer require package-name       # Install package
composer remove package-name        # Remove package
composer update                     # Update dependencies
```

### Frontend/npm
```bash
npm install                         # Install dependencies
npm run dev                         # Start dev server (Vite)
npm run build                       # Production build
npm run build:watch                 # Watch mode (if available)
npm test                           # Run tests (if configured)
npm run lint                       # Run linter (if configured)
```

---

## Environment Variables

**Key Configuration** (in `.env`):

```env
# Application
APP_NAME="Laravel Dashboard"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Cache
CACHE_DRIVER=file

# Session
SESSION_DRIVER=file

# Queue
QUEUE_CONNECTION=sync

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025

# Authentication
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,localhost:5174

# File Upload Disk
FILESYSTEM_DISK=public
```

---

## Git Workflow

**Feature Development**:
```bash
# Create feature branch
git checkout -b feature/add-notifications

# Make changes, commit
git add .
git commit -m "feat: add notification system"

# Push and create PR
git push origin feature/add-notifications

# After review & approval, merge to main
git checkout main
git pull
git merge feature/add-notifications
git push
```

**Commit Message Convention** (Conventional Commits):
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation
- `refactor:` - Code refactoring
- `test:` - Tests only
- `chore:` - Dependencies, tooling

---

## Performance Tips

### Database Optimization
1. **Use Indexes**: Already applied; check migrations
2. **Eager Load**: `with()` relationships
3. **Select Specific Columns**: `select(['id', 'name', ...])`
4. **Pagination**: Always paginate large result sets
5. **Query Caching**: Use version-based cache for read-heavy operations

### Frontend Optimization
1. **Code Splitting**: Lazy-load route components
   ```typescript
   const ResourceList = () => import('@/views/ResourceList.vue');
   ```

2. **Image Optimization**: Already done (WebP conversion on backend)

3. **Bundle Analysis**:
   ```bash
   npm run build -- --analyze  # If Vite analyzer installed
   ```

### API Performance
1. **Response Compression**: Enable gzip in web server
2. **CORS Caching**: Set appropriate Cache-Control headers
3. **Pagination**: Limits data transfer per request

---

## Security Checklist

- ✅ **HTTPS in Production**: Must use SSL/TLS
- ✅ **Environment Variables**: Never commit `.env`
- ✅ **Password Hashing**: Model cast uses bcrypt
- ✅ **Authentication**: Sanctum token-based
- ✅ **Authorization**: Spatie permission-based
- ✅ **CSRF Protection**: Built-in Laravel (for web routes)
- ✅ **SQL Injection**: Use Eloquent ORM (parameterized)
- ✅ **XSS Prevention**: Sanitize input, Vue auto-escapes
- ✅ **Rate Limiting**: Login throttled 5/15min
- ✅ **API Throttling**: Configure global if needed
  ```php
  // config/api.php
  'throttle' => 'api',
  ```
- ✅ **Protected Accounts**: ProtectionService prevents admin deletion
- ⚠️ **File Upload Size Limits**: Configure in `.env` or nginx
  ```env
  APP_MAX_UPLOAD_SIZE=10M
  ```

---

## Troubleshooting

### Common Issues

**Problem**: `SQLSTATE[HY000]: General error: 1 Database is locked`
- **Cause**: SQLite concurrent writes
- **Solution**: 
  - Use MySQL in production
  - Or increase SQLite timeout: `DB_BUSY_TIMEOUT=5000`

**Problem**: `Could not find package ...` during `npm install`
- **Solution**: Clear cache and retry
  ```bash
  npm cache clean --force
  npm install
  ```

**Problem**: Vue components not hot-reloading
- **Solution**: Check Vite dev server is running on port 5174
  ```bash
  npm run dev
  ```

**Problem**: Database migrations not running
- **Solution**: Check DB connection in `.env`
  ```bash
  php artisan migrate --verbose
  ```

**Problem**: Permission denied when accessing storage files
- **Solution**: Run permissions command
  ```bash
  chmod -R 775 storage bootstrap
  php artisan storage:link
  ```

---

## Deployment Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan config:cache`, `route:cache`, `view:cache`
- [ ] Set up HTTPS/SSL certificate
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up log rotation: `storage/logs/`
- [ ] Configure backup strategy for database
- [ ] Set up storage symlink: `php artisan storage:link`
- [ ] Configure queue worker (supervisor/systemd)
- [ ] Enable opcode caching (OPcache in PHP)
- [ ] Set up monitoring/error tracking (Sentry, etc.)
- [ ] Configure CDN for static assets if needed
- [ ] Test authentication and authorization
- [ ] Test file uploads and image processing
- [ ] Load test API endpoints

---

## UNKNOWN / NOT FOUND IN CODE
- CI/CD pipeline configuration (GitHub Actions, GitLab CI, etc.)
- API documentation generation (Swagger/OpenAPI)
- Performance monitoring setup
- Error tracking service integration
- Database backup automation
- SSL certificate renewal process

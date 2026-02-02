# 06-DEVELOPMENT-GUIDE.md

## Development Guide

### Development Environment Setup

#### Prerequisites
- **Docker & Docker Compose**: For containerized development
- **Git**: Version control
- **VS Code**: Recommended IDE with extensions
- **Node.js 20+**: For local development (optional, containers handle this)

#### Environment Setup Steps

1. **Clone Repository**
```bash
git clone <repository-url>
cd laravel-dashboard1
```

2. **Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Configure environment variables
nano .env
```

**Required .env Configuration**:
```env
APP_NAME="Laravel User Management Dashboard"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://dashboard1.test

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=dashboard1
DB_USERNAME=user
DB_PASSWORD=password

CACHE_DRIVER=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

VITE_APP_NAME="${APP_NAME}"
```

3. **Container Setup**
```bash
# Start containers (from parent docker-compose directory)
docker-compose up -d

# Install PHP dependencies
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php composer_2.9.3.phar install'

# Generate application key
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan key:generate'

# Run database migrations
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan migrate'

# Install Node.js dependencies
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm install'

# Build frontend assets
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run build'
```

4. **Access Application**
- **Frontend**: http://dashboard1.test/management/login
- **API**: http://dashboard1.test/api/v1/

### Development Workflow

#### Daily Development Cycle

1. **Pull Latest Changes**
```bash
git pull origin main
```

2. **Install Dependencies**
```bash
# PHP dependencies
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php composer_2.9.3.phar install'

# Node.js dependencies
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm install'
```

3. **Database Updates**
```bash
# Run migrations
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan migrate'

# Seed database (if needed)
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan db:seed'
```

4. **Start Development Servers**
```bash
# Option 1: Use Laravel's development command
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && composer run dev'

# Option 2: Manual server startup
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan serve --host=0.0.0.0 --port=8000'
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run dev'
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan queue:listen'
```

#### Code Changes Workflow

1. **Backend Changes**
```bash
# Make changes to PHP files
# Run tests
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/phpunit'

# Run code analysis
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/pint'
```

2. **Frontend Changes**
```bash
# Make changes to Vue files
# Build for development
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run dev'

# Build for production
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run build'
```

3. **Database Changes**
```bash
# Create new migration
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan make:migration create_example_table'

# Run migrations
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan migrate'
```

### Code Conventions and Standards

#### PHP Code Standards

**File Naming**:
- Controllers: `UserController.php`
- Models: `User.php`
- Services: `UserService.php`
- Requests: `StoreUserRequest.php`

**Class Structure**:
```php
<?php

namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    // Properties
    protected UserService $userService;

    // Constructor
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // Public methods
    public function index(): JsonResponse
    {
        // Implementation
    }

    // Private/protected methods
    private function formatResponse(array $data): array
    {
        // Implementation
    }
}
```

**Method Naming**:
- Actions: `index()`, `store()`, `show()`, `update()`, `destroy()`
- Business logic: `createUser()`, `updateUser()`, `banUser()`
- Utilities: `formatUserData()`, `getRoleDisplayName()`

#### TypeScript/JavaScript Standards

**File Naming**:
- Components: `UserList.vue`, `UserForm.vue`
- Stores: `auth.ts`, `user.ts`
- Types: `user.ts`, `api.ts`

**Component Structure**:
```vue
<template>
  <div class="user-list">
    <!-- Template content -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useUserStore } from '@/stores/user'

// Props
interface Props {
  users: User[]
  loading: boolean
}

const props = defineProps<Props>()

// Emits
const emit = defineEmits<{
  userSelected: [user: User]
}>()

// Reactive data
const selectedUser = ref<User | null>(null)

// Computed properties
const hasUsers = computed(() => props.users.length > 0)

// Methods
const handleUserClick = (user: User) => {
  selectedUser.value = user
  emit('userSelected', user)
}
</script>

<style scoped>
.user-list {
  /* Styles */
}
</style>
```

#### Database Standards

**Table Naming**:
- Users: `users`
- Roles: `roles`
- Permissions: `permissions`
- Junction tables: `model_has_roles`, `role_has_permissions`

**Column Naming**:
- Primary keys: `id`
- Foreign keys: `user_id`, `role_id`
- Booleans: `is_active`, `is_banned`
- Timestamps: `created_at`, `updated_at`

### Testing Strategy

#### Backend Testing

**Unit Tests**:
```bash
# Run all tests
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/phpunit'

# Run specific test class
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/phpunit tests/Unit/Services/UserServiceTest.php'

# Run with coverage
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/phpunit --coverage-html=reports/coverage'
```

**Test Structure**:
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Models\User;

class UserServiceTest extends TestCase
{
    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
    }

    public function test_can_create_user()
    {
        // Test implementation
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'viewer'
        ];

        $user = $this->userService->createUser($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
    }
}
```

#### Frontend Testing

**Component Testing**:
```typescript
// UserList.test.ts
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import UserList from '@/components/UserList.vue'

describe('UserList', () => {
  it('renders user list correctly', () => {
    const users = [
      { id: 1, name: 'John Doe', email: 'john@example.com' }
    ]

    const wrapper = mount(UserList, {
      props: { users, loading: false }
    })

    expect(wrapper.text()).toContain('John Doe')
  })
})
```

### Debugging and Troubleshooting

#### Common Issues and Solutions

**Database Connection Issues**:
```bash
# Check database connectivity
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan tinker'
Psy Shell v0.12.0 (PHP 8.2.30) by Justin Hileman
>>> DB::connection()->getPdo()
```

**Permission Issues**:
```bash
# Clear permission cache
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan cache:clear'

# Check user permissions in tinker
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan tinker'
>>> $user = App\Models\User::find(1)
>>> $user->getAllPermissions()
```

**Frontend Build Issues**:
```bash
# Clear node_modules and reinstall
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && rm -rf node_modules package-lock.json'
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm install'

# Check build logs
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run build'
```

**Container Issues**:
```bash
# Check container status
docker ps

# View container logs
docker logs php_dev_php8.2
docker logs php_dev_nginx
docker logs php_dev_nodejs_20

# Restart containers
docker-compose restart
```

#### Logging and Monitoring

**Laravel Logging**:
```php
// In controllers/services
Log::info('User created', [
    'user_id' => $user->id,
    'email' => $user->email,
    'created_by' => $request->user()->id
]);

// Check logs
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && tail -f storage/logs/laravel.log'
```

**Frontend Logging**:
```typescript
// Development logging
console.log('User data:', userData)

// Production logging (consider a logging service)
if (process.env.NODE_ENV === 'development') {
  console.log('Debug info:', debugData)
}
```

### Deployment Process

#### Production Build Process

1. **Environment Setup**
```bash
# Production .env configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database configuration
DB_CONNECTION=mysql
DB_HOST=production-db-host
DB_DATABASE=production_db
DB_USERNAME=production_user
DB_PASSWORD=secure_password
```

2. **Build Assets**
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci

# Build frontend
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Database Migration**
```bash
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder
```

4. **File Permissions**
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

#### Deployment Checklist

- [ ] Environment variables configured
- [ ] Database connection established
- [ ] SSL certificate installed
- [ ] File permissions set correctly
- [ ] Application key generated
- [ ] Assets compiled and optimized
- [ ] Database migrations run
- [ ] Queue workers configured
- [ ] Monitoring and logging set up
- [ ] Backup strategy implemented

### Performance Optimization

#### Backend Optimization

**Database Query Optimization**:
```php
// Use eager loading
$users = User::with('roles')->paginate(15);

// Select only needed columns
$users = User::select(['id', 'name', 'email'])->get();

// Use database indexes for WHERE clauses
Schema::table('users', function (Blueprint $table) {
    $table->index(['email', 'is_banned']);
});
```

**Caching Strategy**:
```php
// Cache user permissions
Cache::remember("user.{$userId}.permissions", 3600, function () use ($userId) {
    return User::find($userId)->getAllPermissions();
});

// Cache role data
Cache::remember('roles.all', 3600, function () {
    return Role::with('permissions')->get();
});
```

#### Frontend Optimization

**Bundle Splitting**:
```typescript
// Automatic code splitting with Vue Router
const routes = [
  {
    path: '/users',
    component: () => import('@/views/admin/user/Index.vue')
  }
]
```

**Image Optimization**:
```vue
<template>
  <!-- Use WebP with fallbacks -->
  <picture>
    <source :srcset="user.profile_image_webp" type="image/webp">
    <img :src="user.profile_image" alt="Profile">
  </picture>
</template>
```

### Security Best Practices

#### Input Validation
```php
// Use Form Request classes
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'name' => 'required|string|max:255',
        ];
    }
}
```

#### Authentication Security
```php
// Use Sanctum middleware
Route::middleware(['auth:sanctum'])->group(function () {
    // Protected routes
});

// Rate limiting
Route::middleware(['throttle:api'])->group(function () {
    // Rate limited routes
});
```

#### Authorization Checks
```php
// Check permissions in controllers
public function update(User $user): JsonResponse
{
    $this->authorize('update', $user);
    // Update logic
}
```

### Contributing Guidelines

#### Code Review Process
1. Create feature branch from `main`
2. Implement changes with tests
3. Run code quality checks
4. Submit pull request
5. Code review and approval
6. Merge to `main`

#### Commit Message Standards
```
feat: add user ban functionality
fix: resolve permission check bug
docs: update API documentation
style: format code with Pint
refactor: extract user validation logic
test: add user service unit tests
```

#### Branch Naming
- Features: `feature/user-ban-system`
- Bug fixes: `fix/permission-validation`
- Documentation: `docs/api-reference`

### Troubleshooting Guide

#### Build Issues
```bash
# Clear all caches
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan cache:clear'
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan config:clear'
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan route:clear'
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan view:clear'

# Rebuild containers
docker-compose down
docker-compose up --build
```

#### Database Issues
```bash
# Reset database
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan migrate:fresh'

# Check migration status
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan migrate:status'
```

#### Permission Issues
```bash
# Reset permissions
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan db:seed --class=PermissionSeeder'

# Clear permission cache
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan cache:clear'
```

This development guide provides comprehensive instructions for setting up, developing, testing, and deploying the Laravel User Management Dashboard. Follow these guidelines to maintain code quality and ensure smooth development workflows.</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/project_knowledges/06-DEVELOPMENT-GUIDE.md
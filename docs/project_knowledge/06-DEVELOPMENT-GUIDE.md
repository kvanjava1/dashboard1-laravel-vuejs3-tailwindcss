# 06-DEVELOPMENT-GUIDE.md

## Development Guide

This guide covers the development environment setup, coding conventions, build processes, and deployment procedures for the Dashboard1 project.

## Environment Setup

### Prerequisites

**System Requirements:**
- **PHP**: 8.2 or higher
- **Node.js**: 20.x or higher
- **npm**: 10.x or higher
- **Composer**: 2.9.x or higher
- **Docker**: For containerized development
- **Git**: For version control

**Recommended Development Environment:**
- **OS**: Linux (Ubuntu 20.04+), macOS, or Windows with WSL2
- **IDE**: Visual Studio Code with PHP, Vue.js, and TypeScript extensions
- **Database**: MySQL 8.0+

### Docker Development Environment

The project uses Docker containers for consistent development:

#### Container Names and Purposes
- **`php_dev_php8.2`**: PHP 8.2 application server
- **`php_dev_nodejs_20`**: Node.js 20 for frontend builds
- **`php_dev_nginx`**: Nginx web server

#### Running Commands in Containers

**PHP/Composer Commands:**
```bash
# Run PHP commands
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php -v'

# Run Composer commands
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php composer_2.9.3.phar install'

# Run Artisan commands
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan migrate'
```

**Node.js/npm Commands:**
```bash
# Run npm commands
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm install'

# Run build commands
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run build'
```

**Note:** Avoid running `php`, `composer`, `npm`, or `node` commands directly outside containers.

### Local Development Setup

#### 1. Clone Repository
```bash
git clone <repository-url>
cd dashboard1
```

#### 2. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan key:generate'
```

#### 3. Install Dependencies
```bash
# Install PHP dependencies
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php composer_2.9.3.phar install'

# Install Node.js dependencies
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm install'
```

#### 4. Database Setup
```bash
# Run migrations
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan migrate'

# Seed database (optional)
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan db:seed'
```

#### 5. Build Assets
```bash
# Build frontend assets
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run build'
```

#### 6. Start Development Servers
```bash
# Start all services (PHP, Queue, Logs, Vite)
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && composer run dev'
```

#### 7. Access Application
- **Frontend**: `http://dashboard1.test/management/login`
- **API**: `http://dashboard1.test/api/v1`

### Development Workflow

#### Daily Development
```bash
# Start development servers
composer run dev

# In another terminal, watch for file changes
npm run dev
```

#### Making Changes

**Backend Changes:**
1. Modify PHP files in `app/`
2. Update routes in `routes/`
3. Create/update migrations if needed
4. Test API endpoints

**Frontend Changes:**
1. Modify Vue components in `resources/js/vue3_dashboard_admin/`
2. Update styles in `resources/css/`
3. Test UI interactions

**Database Changes:**
1. Create migration: `php artisan make:migration create_example_table`
2. Run migration: `php artisan migrate`
3. Update models and relationships

## Coding Conventions

### PHP Conventions

#### PSR Standards
- **PSR-1**: Basic coding standard
- **PSR-4**: Autoloading standard
- **PSR-12**: Extended coding style guide

#### Naming Conventions
```php
// Classes: PascalCase
class UserService { }

// Methods: camelCase
public function createUser() { }

// Properties: camelCase
protected $userService;

// Constants: UPPER_SNAKE_CASE
const DEFAULT_STATUS = 'active';

// Variables: camelCase
$userId = 123;
```

#### File Structure
```
app/
├── Http/Controllers/Api/     # API controllers
├── Services/                 # Business logic services
├── Models/                   # Eloquent models
├── Http/Requests/           # Form request validation
├── Traits/                   # Reusable traits
└── Contracts/                # Interfaces
```

#### Code Style
```php
// Use type hints
public function createUser(array $data): array

// Use return type declarations
public function getUser(int $id): ?User

// Use dependency injection
public function __construct(UserService $userService)

// Use collection methods over loops
$users = User::all()->map(function ($user) {
    return $user->name;
});
```

### TypeScript/Vue.js Conventions

#### File Naming
```
components/
├── UserList.vue
├── UserForm.vue
├── RoleSelect.vue

stores/
├── auth.ts
├── user.ts

types/
├── user.ts
├── api.ts
```

#### Component Structure
```vue
<template>
  <div class="user-list">
    <!-- Template content -->
  </div>
</template>

<script setup lang="ts">
// Imports
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

// Props
interface Props {
  users: User[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
})

// Emits
const emit = defineEmits<{
  userSelected: [user: User]
}>()

// Reactive data
const selectedUser = ref<User | null>(null)

// Computed properties
const hasUsers = computed(() => props.users.length > 0)

// Methods
const selectUser = (user: User) => {
  selectedUser.value = user
  emit('userSelected', user)
}
</script>

<style scoped>
.user-list {
  /* Component styles */
}
</style>
```

#### TypeScript Types
```typescript
// types/user.ts
export interface User {
  id: number
  name: string
  email: string
  role: string
  permissions: string[]
  status: string
  profile_image?: string
  created_at: string
  updated_at: string
}

// types/api.ts
export interface ApiResponse<T> {
  message: string
  data: T
  meta?: {
    total: number
    current_page: number
    per_page: number
  }
}
```

#### Store Structure (Pinia)
```typescript
// stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // State
  const token = ref<string | null>(null)
  const user = ref<User | null>(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value)

  // Actions
  const login = async (credentials: LoginCredentials) => {
    // Login logic
  }

  const logout = () => {
    // Logout logic
  }

  return {
    // Expose state, getters, actions
    token,
    user,
    isAuthenticated,
    login,
    logout
  }
})
```

### CSS/Tailwind Conventions

#### Class Naming
```html
<!-- Use kebab-case for custom classes -->
<div class="user-card">
  <h3 class="user-card__title">User Name</h3>
  <p class="user-card__email">user@example.com</p>
</div>
```

#### Tailwind Usage
```html
<!-- Use Tailwind utility classes -->
<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  Click me
</button>

<!-- Custom CSS for complex styles -->
<style scoped>
.user-avatar {
  @apply w-12 h-12 rounded-full object-cover;
}
</style>
```

## Build and Deployment

### Build Process

#### Development Build
```bash
# Build for development
npm run dev
```

#### Production Build
```bash
# Build optimized assets
npm run build

# Build process includes:
# - TypeScript compilation
# - Vue SFC compilation
# - CSS processing with Tailwind
# - Asset optimization
# - Code splitting
```

#### Laravel Asset Compilation
```bash
# Development
npm run dev

# Production
npm run build

# Watch mode
npm run dev -- --watch
```

### Deployment Process

#### 1. Environment Setup
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database configuration
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

#### 2. Dependency Installation
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm ci

# Build assets
npm run build
```

#### 3. Database Migration
```bash
# Run migrations
php artisan migrate --force

# Seed production data (if needed)
php artisan db:seed --class=ProductionSeeder
```

#### 4. Cache Optimization
```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

#### 5. Storage Setup
```bash
# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

#### 6. Queue Worker Setup
```bash
# Start queue worker
php artisan queue:work --tries=3 --timeout=90
```

### Deployment Checklist

- [ ] Environment variables configured
- [ ] Database connection established
- [ ] SSL certificate installed
- [ ] Domain DNS configured
- [ ] File permissions set
- [ ] Storage link created
- [ ] Caches optimized
- [ ] Queue workers running
- [ ] Monitoring tools configured
- [ ] Backup procedures in place

## Testing

### Running Tests

#### PHPUnit Tests
```bash
# Run all tests
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan test'

# Run specific test
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan test --filter=UserServiceTest'

# Run with coverage
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan test --coverage'
```

#### Test Structure
```
tests/
├── Feature/          # Feature tests (HTTP endpoints)
│   ├── AuthTest.php
│   ├── UserTest.php
├── Unit/            # Unit tests (classes, methods)
│   ├── Services/
│   ├── Models/
└── TestCase.php     # Base test case
```

### Writing Tests

#### Feature Test Example
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'forum_member',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'id', 'name', 'email', 'role', 'status'
                    ]
                ]);
    }
}
```

#### Unit Test Example
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation()
    {
        $userService = app(UserService::class);
        
        $userData = $userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'forum_member',
            'status' => 'active'
        ]);

        $this->assertEquals('Test User', $userData['name']);
        $this->assertEquals('forum_member', $userData['role']);
    }
}
```

## Code Quality Tools

### Laravel Pint (Code Formatting)
```bash
# Format code
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/pint'

# Check code style
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/pint --test'
```

### PHPStan (Static Analysis)
```bash
# Run static analysis
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/phpstan analyse'
```

### ESLint (JavaScript/TypeScript)
```bash
# Check JavaScript/TypeScript code
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npx eslint resources/js/vue3_dashboard_admin/'

# Fix auto-fixable issues
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npx eslint resources/js/vue3_dashboard_admin/ --fix'
```

## Performance Optimization

### Backend Optimizations

#### Database Query Optimization
```php
// Use eager loading to prevent N+1 queries
$users = User::with('roles', 'accountStatus')->paginate(15);

// Use select to limit columns
$users = User::select('id', 'name', 'email')->get();

// Use indexes for frequently queried columns
Schema::table('users', function (Blueprint $table) {
    $table->index('email');
    $table->index(['user_account_status_id', 'is_banned']);
});
```

#### Caching Strategy
```php
// Cache expensive operations
$roles = Cache::remember('roles', 3600, function () {
    return Role::with('permissions')->get();
});

// Cache user permissions
$userPermissions = Cache::tags(['user', 'permissions'])
    ->remember("user.{$userId}.permissions", 3600, function () use ($userId) {
        return User::find($userId)->getAllPermissions();
    });
```

#### Queue Optimization
```php
// Dispatch jobs to queue for heavy operations
dispatch(new ProcessUserImport($file));

// Use job batching for bulk operations
Bus::batch([
    new ProcessUser(1),
    new ProcessUser(2),
    new ProcessUser(3),
])->dispatch();
```

### Frontend Optimizations

#### Code Splitting
```typescript
// Lazy load routes
const routes = [
  {
    path: '/users',
    component: () => import('@/views/admin/user/Index.vue')
  }
]
```

#### Asset Optimization
```javascript
// vite.config.js
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', 'pinia'],
          ui: ['@headlessui/vue', '@heroicons/vue']
        }
      }
    }
  }
})
```

#### Image Optimization
```vue
<template>
  <!-- Use responsive images -->
  <img 
    :src="user.profile_image_url" 
    :alt="user.name"
    loading="lazy"
    class="w-12 h-12 rounded-full object-cover"
  />
</template>
```

## Monitoring and Logging

### Application Logging
```php
// Log important actions
Log::info('User created', [
    'user_id' => $user->id,
    'email' => $user->email,
    'created_by' => auth()->id()
]);

// Log errors with context
Log::error('User creation failed', [
    'error' => $e->getMessage(),
    'data' => $request->all(),
    'user_id' => auth()->id()
]);
```

### Performance Monitoring
```php
// Log slow queries
DB::listen(function ($query) {
    if ($query->time > 1000) { // Log queries taking more than 1 second
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time,
            'bindings' => $query->bindings
        ]);
    }
});
```

### Error Tracking
```php
// Global exception handler
app(ExceptionHandler::class, function (Throwable $e) {
    Log::error('Unhandled exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
});
```

## Security Best Practices

### Input Validation
```php
// Use Form Request classes
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'status' => 'required|exists:user_account_statuses,name'
        ];
    }
}
```

### Authentication Security
```php
// Use Sanctum for API authentication
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes
});

// Rate limiting
Route::middleware('throttle:5,15')->post('/login', [AuthController::class, 'login']);
```

### Authorization Checks
```php
// Check permissions in controllers
$this->authorize('update', $user);

// Check permissions in blade templates
@can('user_management.view')
  <!-- Show user management UI -->
@endcan
```

### Data Sanitization
```php
// Sanitize user inputs
$userData = [
    'name' => strip_tags($request->name),
    'bio' => strip_tags($request->bio),
    'email' => filter_var($request->email, FILTER_SANITIZE_EMAIL)
];
```

## Troubleshooting

### Common Issues

#### Database Connection Issues
```bash
# Check database configuration
php artisan config:show database

# Test database connection
php artisan tinker
DB::connection()->getPdo();
```

#### Permission Issues
```bash
# Clear permission cache
php artisan permission:cache-reset

# Check user permissions
php artisan tinker
$user = User::find(1);
$user->getAllPermissions();
```

#### Asset Compilation Issues
```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear Vite cache
rm -rf node_modules/.vite
```

#### Queue Issues
```bash
# Check queue status
php artisan queue:status

# Restart queue workers
php artisan queue:restart

# Clear failed jobs
php artisan queue:flush
```

### Debug Commands
```bash
# Show application configuration
php artisan config:show

# Show routes
php artisan route:list

# Show database migrations status
php artisan migrate:status

# Clear all Laravel caches
php artisan optimize:clear
```

## Contributing Guidelines

### Git Workflow
```bash
# Create feature branch
git checkout -b feature/user-profile-enhancement

# Make changes and commit
git add .
git commit -m "Add user profile enhancement"

# Push branch
git push origin feature/user-profile-enhancement

# Create pull request
# - Describe changes
# - Reference related issues
# - Request review from team members
```

### Code Review Checklist
- [ ] Code follows PSR standards
- [ ] TypeScript types are properly defined
- [ ] Tests are included for new features
- [ ] Documentation is updated
- [ ] Security considerations addressed
- [ ] Performance impact assessed
- [ ] Database migrations are reversible

### Commit Message Convention
```
type(scope): description

Types:
- feat: New feature
- fix: Bug fix
- docs: Documentation
- style: Code style changes
- refactor: Code refactoring
- test: Testing
- chore: Maintenance

Examples:
feat(auth): add password reset functionality
fix(user): resolve ban status calculation bug
docs(api): update user management endpoints
```

This development guide provides comprehensive information for working with the Dashboard1 project. Follow these guidelines to maintain code quality, ensure security, and contribute effectively to the project development.</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/ai/project_knowledge/06-DEVELOPMENT-GUIDE.md
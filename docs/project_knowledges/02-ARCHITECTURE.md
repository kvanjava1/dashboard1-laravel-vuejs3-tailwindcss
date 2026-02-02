# 02-ARCHITECTURE.md

## Architecture & Structure

### Application Architecture Pattern

#### MVC with Service Layer Pattern
The application follows a **Model-View-Controller (MVC)** architecture enhanced with a **Service Layer** pattern for business logic separation.

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Vue.js SPA    │    │  Laravel API    │    │   Database      │
│   (Frontend)    │◄──►│  (Backend)      │◄──►│   (MySQL/PgSQL) │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Components     │    │ Controllers     │    │   Models        │
│  Stores         │    │ Services        │    │   Migrations    │
│  Views          │    │ Middleware      │    │   Seeders       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

#### Key Architectural Components:

1. **Frontend (Vue.js SPA)**
   - Component-based architecture
   - Centralized state management (Pinia)
   - Route-based navigation with guards
   - TypeScript for type safety

2. **Backend (Laravel API)**
   - RESTful API design
   - Service layer for business logic
   - Repository pattern through Eloquent
   - Middleware for authentication/authorization

3. **Data Layer**
   - Eloquent ORM for database interactions
   - Migration-based schema management
   - Relationship mapping between entities

### Directory Structure Analysis

#### Root Level Structure
```
/
├── app/                    # Laravel application code
├── bootstrap/             # Application bootstrap files
├── config/                # Configuration files
├── database/              # Database migrations, factories, seeders
├── docs/                  # Documentation
├── public/                # Public web assets
├── resources/             # Frontend resources (Vue, CSS, etc.)
├── routes/                # Route definitions
├── storage/               # File storage, logs, cache
├── tests/                 # Test files
├── vendor/                # Composer dependencies
├── vite.config.js         # Vite build configuration
├── tailwind.config.js     # Tailwind CSS configuration
├── tsconfig.json          # TypeScript configuration
├── package.json           # Node.js dependencies
└── composer.json          # PHP dependencies
```

#### App Directory Deep Dive
```
app/
├── Console/               # Artisan commands
│   └── Commands/
├── Contracts/             # Interface definitions
├── Exceptions/            # Custom exception handlers
├── Http/                  # HTTP layer (controllers, middleware, requests)
│   ├── Controllers/
│   │   └── Api/
│   │       └── Managements/
│   ├── Middleware/
│   └── Requests/
├── Models/                # Eloquent models
├── Providers/             # Service providers
├── Services/              # Business logic services
└── Traits/                # Reusable traits
```

#### Frontend Structure (resources/js/vue3_dashboard_admin/)
```
resources/js/vue3_dashboard_admin/
├── components/            # Reusable Vue components
├── composables/           # Vue composables (custom hooks)
├── config/                # Frontend configuration
├── layouts/               # Layout components
├── router/                # Vue Router configuration
│   └── routes/            # Route definitions
├── stores/                # Pinia stores
├── types/                 # TypeScript type definitions
├── utils/                 # Utility functions
├── views/                 # Page components
└── App.vue               # Root component
```

### Data Flow and Component Interactions

#### Request Flow (Frontend → Backend)
```
1. User Action (Vue Component)
2. State Update (Pinia Store)
3. API Call (Axios)
4. Route Handling (Laravel Route)
5. Middleware Processing (Auth, Permissions)
6. Controller Action
7. Service Layer (Business Logic)
8. Model Operations (Database)
9. Response Formatting
10. JSON Response to Frontend
11. State Update (Pinia Store)
12. UI Re-render (Vue Component)
```

#### Authentication Flow
```
Login Form → Auth Store → API Call → Sanctum Token → LocalStorage → Route Guards → Protected Routes
```

#### User Management Flow
```
User List View → User Service → User Model → Database Query → Paginated Results → Vue Component → Data Table
```

### Design Patterns Used

#### 1. Service Layer Pattern
```php
// Service classes encapsulate business logic
class UserService {
    public function createUser(array $data): array {
        // Business logic here
    }
}
```

#### 2. Repository Pattern (via Eloquent)
```php
// Eloquent models serve as repositories
class User extends Model {
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
}
```

#### 3. Factory Pattern (Laravel)
```php
// Factories for test data creation
class UserFactory extends Factory {
    protected $model = User::class;
}
```

#### 4. Strategy Pattern (Permission System)
```php
// Different permission strategies via Spatie package
$user->hasPermission('user_management.view');
$user->hasRole('administrator');
```

#### 5. Observer Pattern (Model Events)
```php
// Model events for audit trails
class User extends Model {
    protected static function booted() {
        static::created(function ($user) {
            Log::info('User created', ['user_id' => $user->id]);
        });
    }
}
```

### Configuration Strategy

#### Environment-Based Configuration
- **.env files**: Environment-specific settings
- **Config files**: Application configuration
- **Service Providers**: Dependency injection setup

#### Key Configuration Files:
- `config/app.php`: Application settings
- `config/auth.php`: Authentication configuration
- `config/permission.php`: Spatie permission settings
- `config/protected_entities.php`: Account/role protection rules
- `config/database.php`: Database connection settings
- `config/filesystems.php`: File storage configuration

#### Configuration Hierarchy:
```
1. Environment Variables (.env)
2. Config Files (config/*.php)
3. Default Values (fallback)
```

### Component Communication Patterns

#### Frontend Communication:
```typescript
// Parent-Child Communication
<template>
  <ChildComponent @event="handleEvent" />
</template>

// Store-Based Communication
const userStore = useUserStore()
userStore.updateUser(userData)

// Event Bus (if needed)
// Not used - Pinia handles global state
```

#### Backend Communication:
```php
// Dependency Injection
public function __construct(UserService $userService) {
    $this->userService = $userService;
}

// Service Method Calls
$userData = $this->userService->createUser($validatedData);
```

### State Management Approach

#### Frontend State (Pinia)
```typescript
// Centralized state management
export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)

  const login = async (credentials: LoginCredentials) => {
    // Login logic
  }

  return {
    user,
    token,
    login
  }
})
```

#### Backend State
- **Session State**: Laravel sessions (database driver)
- **Application State**: Cached data, configuration
- **Database State**: Persistent data storage

### Security Architecture

#### Authentication Layers:
1. **Laravel Sanctum**: API token authentication
2. **Middleware**: Route protection
3. **Permission Checks**: Granular access control
4. **Account Protection**: Critical account safeguards

#### Authorization Strategy:
```php
// Route-level protection
Route::middleware(['permission:user_management.view'])->group(function () {
    // Protected routes
});

// Method-level protection
public function updateUser(int $userId, array $data): array {
    $this->authorize('update', $user);
    // Update logic
}
```

### Error Handling Strategy

#### Frontend Error Handling:
```typescript
try {
  await api.updateUser(userId, userData)
} catch (error) {
  if (error.response?.status === 403) {
    // Permission denied
  } else if (error.response?.status === 422) {
    // Validation errors
  }
}
```

#### Backend Error Handling:
```php
try {
  $user = $this->userService->createUser($data);
  return response()->json(['user' => $user]);
} catch (\Exception $e) {
  Log::error('User creation failed', [
    'error' => $e->getMessage(),
    'data' => $data
  ]);
  return response()->json(['error' => 'Creation failed'], 500);
}
```

### Performance Optimization Patterns

#### Database Optimization:
- **Eager Loading**: Prevent N+1 queries
- **Selective Columns**: Only load needed data
- **Indexing**: Proper database indexes
- **Pagination**: Limit result sets

#### Frontend Optimization:
- **Lazy Loading**: Route-based code splitting
- **Computed Properties**: Cached calculations
- **Virtual Scrolling**: Large list handling

#### Caching Strategy:
- **Permission Caching**: User permissions cached
- **Configuration Caching**: App config cached
- **Query Result Caching**: Frequent queries cached

### Testing Strategy

#### Backend Testing:
```php
class UserServiceTest extends TestCase {
    public function test_can_create_user() {
        // Test business logic
    }
}
```

#### Frontend Testing:
- Component testing with Vue Test Utils
- Store testing with Pinia testing utilities
- E2E testing with Cypress (if implemented)

### Deployment Considerations

#### Build Process:
```bash
# Backend build
composer install --optimize-autoloader
php artisan config:cache
php artisan route:cache

# Frontend build
npm run build
```

#### Environment Separation:
- **Development**: Local development with hot reload
- **Staging**: Pre-production testing
- **Production**: Optimized builds with caching

#### Container Strategy:
- **PHP Container**: Application runtime
- **Node.js Container**: Build process and development
- **Nginx Container**: Web server and static file serving
- **Database Container**: Data persistence</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/project_knowledges/02-ARCHITECTURE.md
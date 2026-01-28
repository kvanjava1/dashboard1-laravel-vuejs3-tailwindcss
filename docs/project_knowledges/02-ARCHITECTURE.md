# 02-ARCHITECTURE.md

## Application Architecture Pattern
This project follows a **Service-Oriented MVC Architecture** with a clear separation of concerns:

- **MVC Layer**: Controllers handle HTTP requests, Models manage data
- **Service Layer**: Business logic abstracted into dedicated services
- **Repository Pattern**: Data access abstracted (though not fully implemented)
- **API-First Design**: RESTful API with SPA frontend

## Directory Structure

```
/app
├── Console/           # Artisan commands
├── Contracts/         # Interface definitions
├── Exceptions/        # Custom exceptions
├── Http/
│   ├── Controllers/   # HTTP request handlers
│   │   ├── Api/       # API controllers
│   │   └── Web/       # Web controllers (empty)
│   ├── Middleware/    # HTTP middleware
│   └── Requests/      # Form request validation
├── Models/            # Eloquent models
├── Providers/         # Service providers
├── Services/          # Business logic services
├── Traits/            # Reusable model traits
└── Contracts/         # Interface contracts

/bootstrap             # Framework bootstrap
/cache                 # Compiled views, routes, etc.
/config                # Configuration files
/database
├── factories/         # Model factories
├── migrations/        # Database schema
├── seeders/           # Database seeders
└── tests/             # PHPUnit tests

/public                # Web-accessible assets
/resources
├── css/              # Stylesheets
├── js/vue3_dashboard_admin/  # Vue.js application
└── views/            # Blade templates

/routes               # Route definitions
/storage              # File storage
/tests                # Test files
/vendor               # Composer dependencies
```

## Data Flow and Component Interactions

### Request Flow
```
Client Request → Route → Middleware → Controller → Service → Model → Database
                      ↓
Response ← Controller ← Service ← Model ← Database
```

### API Request Example
```
POST /api/v1/login
├── Route: AuthController@login
├── Middleware: throttle:5,15
├── Controller: Validates request, calls AuthService
├── Service: Handles authentication logic
├── Model: User model with Sanctum tokens
└── Response: JSON with token and user data
```

### Frontend Architecture
```
Vue 3 SPA
├── App.vue (Root component)
├── router/ (Vue Router configuration)
├── stores/ (Pinia state management)
├── components/ (Reusable UI components)
├── views/ (Page components)
├── composables/ (Vue composables)
├── layouts/ (Layout components)
└── utils/ (Utility functions)
```

## Design Patterns Used

### Service Layer Pattern
- Business logic extracted from controllers
- Services handle complex operations
- Example: `UserService` manages user CRUD with validation

```php
// Controller delegates to service
public function store(StoreUserRequest $request): JsonResponse
{
    $userData = $this->userService->createUser($request->validated());
    return response()->json(['user' => $userData]);
}
```

### Repository Pattern (Partial)
- Data access abstracted
- Models handle basic queries
- Services contain complex business queries

### Strategy Pattern (Implicit)
- Different authentication methods via Sanctum
- Role-based permission checks

### Observer Pattern
- Laravel events for model changes
- Implicit in Eloquent relationships

## Configuration Strategy

### Environment Configuration
- `.env` file for environment-specific settings
- `config/` directory for application configuration
- Docker container configuration for development

### Key Configuration Files
- `config/app.php`: Application settings
- `config/auth.php`: Authentication configuration
- `config/permission.php`: Spatie Permission settings
- `config/database.php`: Database connections
- `config/sanctum.php`: API token settings

### Docker Configuration
- Separate containers for PHP, Node.js, Nginx
- Volume mounting for development
- Container-specific commands for tooling

## Component Communication

### Backend Communication
- **Controllers ↔ Services**: Dependency injection
- **Services ↔ Models**: Eloquent ORM
- **Models ↔ Database**: Laravel migrations/schema

### Frontend Communication
- **Components ↔ Stores**: Pinia reactive state
- **Components ↔ API**: Axios HTTP client
- **Router ↔ Components**: Vue Router navigation

### Cross-Cutting Concerns
- **Logging**: Laravel Log facade throughout services
- **Validation**: Form request classes
- **Error Handling**: Try-catch blocks with JSON responses
- **Security**: Middleware for auth and permissions
# 02-ARCHITECTURE.md

## Application Architecture Pattern

Dashboard1 follows a **Service-Oriented MVC Architecture** with the following layers:

```
┌─────────────────┐
│   Vue.js SPA    │ ← Frontend Layer
│  (Components)   │
├─────────────────┤
│   Pinia Stores  │ ← State Management
├─────────────────┤
│   REST API      │ ← API Layer
│  (Laravel)      │
├─────────────────┤
│  Controllers    │ ← Presentation Layer
├─────────────────┤
│   Services      │ ← Business Logic Layer
├─────────────────┤
│    Models       │ ← Data Access Layer
├─────────────────┤
│   Database      │ ← Persistence Layer
└─────────────────┘
```

### Architecture Principles
- **Separation of Concerns**: Each layer has a specific responsibility
- **Dependency Injection**: Services are injected into controllers
- **Single Responsibility**: Each class/service handles one concern
- **SOLID Principles**: Interface segregation, dependency inversion
- **RESTful Design**: Clean API endpoints with standard HTTP methods

## Directory Structure Analysis

### Root Level Structure
```
/
├── app/                    # Laravel application code
├── bootstrap/              # Application bootstrap files
├── config/                 # Configuration files
├── database/               # Migrations, seeders, factories
├── docs/                   # Documentation
├── public/                 # Public web assets
├── resources/              # Views, assets (CSS, JS, Vue)
├── routes/                 # Route definitions
├── storage/                # File storage, logs, cache
├── tests/                  # Test files
├── vendor/                 # Composer dependencies
├── artisan                 # Laravel CLI tool
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies
├── vite.config.js          # Frontend build config
├── tailwind.config.js      # CSS framework config
└── tsconfig.json           # TypeScript config
```

### App Directory Breakdown
```
app/
├── Console/                # Artisan commands
├── Exceptions/             # Custom exceptions
├── Http/                   # HTTP layer (controllers, middleware, requests)
├── Models/                 # Eloquent models
├── Providers/              # Service providers
├── Services/               # Business logic services
├── Traits/                 # Reusable traits
├── Contracts/              # Interfaces/contracts
└── bootstrap/              # Bootstrap files
```

### Resources Directory
```
resources/
├── css/                    # Stylesheets
├── js/                     # JavaScript/TypeScript source
│   └── vue3_dashboard_admin/  # Vue.js application
│       ├── components/     # Reusable Vue components
│       ├── views/          # Page-level components
│       ├── stores/         # Pinia state stores
│       ├── router/         # Vue Router configuration
│       ├── config/         # Application configuration
│       └── types/          # TypeScript type definitions
└── views/                  # Blade templates
```

## Data Flow and Component Interactions

### Request Flow (Frontend to Backend)
```
1. User Action → Vue Component
2. Component → Pinia Store Action
3. Store → API Call (fetch/axios)
4. HTTP Request → Laravel Route
5. Route → Controller Method
6. Controller → Service Method
7. Service → Model/Database
8. Response ← Service ← Controller
9. JSON Response → Store → Component Update
```

### Authentication Flow
```
Login Form → AuthStore.login() → API /login
    ↓
Validate Credentials → Generate Token
    ↓
Return Token + User Data → Store in localStorage
    ↓
Update Store State → Redirect to Dashboard
```

### User Management Flow
```
User List View → UserStore.fetchUsers() → API /users
    ↓
Apply Filters → Paginate Results
    ↓
Controller → UserService.getFilteredPaginatedUsers()
    ↓
Query Builder → Eager Load Relations
    ↓
Format Data → Return JSON
```

### Permission Check Flow
```
Route Access → Router Guard → AuthStore.hasPermission()
    ↓
Check user.permissions array
    ↓
Allow/Deny Access
```

## Design Patterns Used

### 1. Service Layer Pattern
- **Purpose**: Encapsulate business logic outside controllers
- **Implementation**: `UserService`, `AuthService`, `RoleService`, etc.
- **Benefits**: Testable business logic, reusable across controllers

### 2. Repository Pattern (Partial)
- **Purpose**: Abstract data access
- **Implementation**: Services act as repositories for models
- **Example**: `UserService.getUserById()` instead of `User::find()`

### 3. Strategy Pattern
- **Purpose**: Configurable protection behavior
- **Implementation**: `ProtectionService` with configurable rules
- **Benefits**: Flexible protection without code changes

### 4. Observer Pattern
- **Purpose**: Event-driven architecture
- **Implementation**: Laravel events/listeners (implicit in framework)
- **Usage**: Model events, queued jobs

### 5. Factory Pattern
- **Purpose**: Object creation abstraction
- **Implementation**: Laravel factories for testing/seeding

### 6. Singleton Pattern
- **Purpose**: Single instance services
- **Implementation**: Laravel service container bindings

### 7. Adapter Pattern
- **Purpose**: Interface adaptation
- **Implementation**: Laravel service providers adapting third-party packages

## Configuration Strategy

### Key Configuration Files

#### `config/app.php`
- Application name, environment, timezone
- Service providers registration
- Aliases for facades

#### `config/auth.php`
- Authentication guards (sanctum for API)
- Password reset settings
- User providers

#### `config/database.php`
- Database connections (MySQL primary)
- Migration paths
- Connection pooling

#### `config/permission.php` (Spatie)
- Permission table names
- Model relationships
- Cache settings

#### `config/protected_entities.php` (Custom)
- Protected accounts and roles
- Protection behavior settings
- Exception handling

#### `config/queue.php`
- Queue drivers (database, redis)
- Job retry policies
- Failed job handling

#### `config/logging.php`
- Log channels (stack, single, daily)
- Log levels
- External services (papertrail, etc.)

### Environment Configuration
- `.env` file for environment-specific values
- Database credentials
- API keys
- Debug settings
- Cache/queue drivers

## Component Architecture (Frontend)

### Vue.js Application Structure
```
App.vue (Root)
├── Router (Vue Router)
│   ├── Auth Routes (/login, /no-permissions)
│   ├── Dashboard Routes (/dashboard/*)
│   ├── User Management (/user_management/*)
│   └── Role Management (/role_management/*)
├── Pinia Stores
│   ├── Auth Store (authentication, permissions)
│   └── Future: User Store, Role Store
└── Components
    ├── Layout Components (headers, sidebars)
    ├── Form Components (inputs, selects)
    ├── Data Components (tables, cards)
    └── UI Components (buttons, modals)
```

### State Management (Pinia)
- **Auth Store**: Token management, user data, permissions
- **Reactive State**: User authentication status, current user
- **Actions**: login, logout, session restoration
- **Getters**: Computed properties for permissions/roles

### Routing Architecture
- **Base Path**: `/management/` (configured in router)
- **Route Guards**: Authentication and permission checks
- **Meta Fields**: Required permissions, page titles
- **Dynamic Imports**: Lazy loading of route components

## Database Architecture

### Schema Design
- **InnoDB Engine**: ACID compliance, foreign keys
- **Soft Deletes**: Preserved deleted records
- **Timestamps**: Automatic created_at/updated_at
- **Indexes**: Optimized queries on frequently searched columns

### Migration Strategy
- **Versioned Migrations**: Timestamped files
- **Rollback Support**: Down methods for reversibility
- **Data Migration**: Safe data transformations
- **Seeders**: Initial data population

### Relationship Management
- **Foreign Keys**: Referential integrity
- **Polymorphic Relations**: Flexible associations (Spatie permissions)
- **Eager Loading**: N+1 query prevention
- **Cascading Deletes**: Automatic cleanup

## Security Architecture

### Authentication
- **Token-Based**: Sanctum personal access tokens
- **Stateless API**: No session storage on server
- **Token Expiration**: Configurable lifetimes

### Authorization
- **Role-Based Access Control**: Users → Roles → Permissions
- **Permission Checks**: Middleware on routes
- **Frontend Guards**: Route-level permission validation

### Data Protection
- **Input Validation**: Request classes with rules
- **Mass Assignment Protection**: Fillable properties
- **SQL Injection Prevention**: Parameterized queries
- **XSS Protection**: Vue.js output encoding

### Account Protection
- **Configurable Rules**: Protected entities configuration
- **Exception Handling**: Graceful denial of protected operations
- **Audit Logging**: All protection violations logged

## Performance Optimizations

### Backend
- **Query Optimization**: Eager loading, select statements
- **Caching**: Laravel Cache facade for expensive operations
- **Pagination**: Limit result sets
- **Database Indexes**: Optimized lookups

### Frontend
- **Code Splitting**: Vite dynamic imports
- **Tree Shaking**: Unused code elimination
- **Asset Optimization**: Minification, compression
- **Lazy Loading**: Route-based component loading

### Infrastructure
- **Containerization**: Docker for consistent environments
- **Process Management**: PHP-FPM, Nginx optimization
- **CDN Ready**: Static asset external hosting preparation</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/ai/project_knowledge/02-ARCHITECTURE.md
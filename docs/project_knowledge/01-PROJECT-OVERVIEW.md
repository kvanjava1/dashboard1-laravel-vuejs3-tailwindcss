# 01-PROJECT-OVERVIEW.md

## Project Name
**Dashboard1** - Laravel Admin Dashboard

## Primary Purpose and Domain
Dashboard1 is a comprehensive admin dashboard built with Laravel and Vue.js, designed for managing users, roles, and permissions in a community/forum platform. The system provides a secure, role-based access control (RBAC) interface for administrators to manage user accounts, assign permissions, and handle moderation tasks including user banning and unbanning.

## Business Context and Problem Solved
This dashboard addresses the need for robust user management in online communities and forums. It solves the problem of:
- Centralized user administration
- Role-based access control
- User moderation and banning
- Permission management
- Secure authentication and authorization

The system is particularly suited for forum platforms, community websites, or any application requiring granular user management and administrative oversight.

## Key Business Entities and Relationships

### Core Entities
1. **Users** - Community members with profiles, roles, and account statuses
2. **Roles** - Permission groups (e.g., administrator, moderator, forum_member)
3. **Permissions** - Granular access controls (e.g., user_management.view, role_management.edit)
4. **User Account Statuses** - Account states (active, not_activated, banned)
5. **User Ban History** - Audit trail of ban/unban actions

### Key Relationships
- Users belong to one Role (via Spatie Permission)
- Users have one Account Status
- Users can have multiple Permissions (through roles)
- Users can have Ban History records
- Roles have many Permissions
- Permissions belong to many Roles

## Technology Stack

### Backend
- **Framework**: Laravel 12.0
- **Language**: PHP 8.2
- **Authentication**: Laravel Sanctum (API tokens)
- **Authorization**: Spatie Laravel Permission (RBAC)
- **Database**: MySQL (via migrations)
- **ORM**: Eloquent
- **API**: RESTful JSON API
- **Caching**: Laravel Cache (multiple backends)
- **Queues**: Laravel Queues
- **Logging**: Laravel Logging

### Frontend
- **Framework**: Vue.js 3.5.26
- **Language**: TypeScript 5.9.3
- **Build Tool**: Vite 7.3.1
- **Styling**: TailwindCSS 4.1.18
- **State Management**: Pinia 3.0.4
- **Routing**: Vue Router 4.6.4
- **HTTP Client**: Fetch API (native)
- **Icons**: Material Symbols, Heroicons
- **Forms**: Native HTML5 + validation
- **UI Components**: Headless UI, Custom components

### Development Tools
- **Package Manager**: Composer (PHP), npm (Node.js)
- **Code Quality**: Laravel Pint, ESLint
- **Testing**: PHPUnit
- **Asset Compilation**: Laravel Vite Plugin
- **Development Server**: Laravel Sail (Docker)

### Infrastructure
- **Web Server**: Nginx
- **Application Server**: PHP-FPM
- **Database**: MySQL
- **Containerization**: Docker (php_dev_php8.2, php_dev_nodejs_20, php_dev_nginx)
- **Version Control**: Git

## Core Workflows

### 1. User Authentication
1. User submits login credentials
2. System validates credentials against database
3. On success: generates Sanctum token, returns user data with roles/permissions
4. Frontend stores token in localStorage, redirects to dashboard

### 2. User Management
1. Admin views paginated user list with filters
2. Admin can create new users (assigns role and status)
3. Admin can edit existing users (limited by protection rules)
4. Admin can ban/unban users with reason tracking
5. System logs all ban actions to history table

### 3. Role Management
1. Admin views roles with associated permissions
2. Admin can create new roles with selected permissions
3. Admin can modify existing roles (subject to protection)
4. System prevents deletion of protected roles

### 4. Permission System
1. Permissions are grouped by category (user_management, role_management, etc.)
2. Roles are assigned collections of permissions
3. Users inherit permissions through their assigned role
4. Frontend checks permissions for route access and UI elements

### 5. Account Protection
1. Certain accounts (super@admin.com) are protected from deletion/modification
2. Certain roles (super_admin) cannot be deleted or modified
3. Protection rules are configurable via `config/protected_entities.php`

## Security Measures
- API token authentication via Sanctum
- Role-based permission checks on all endpoints
- Account protection system for critical users/roles
- CSRF protection on web routes
- Input validation and sanitization
- SQL injection prevention via Eloquent
- XSS protection via Vue.js templating
- Secure password hashing (bcrypt)

## Performance Considerations
- Database query optimization with eager loading
- Pagination on large datasets
- Caching for frequently accessed data
- Lazy loading of relationships
- Efficient permission checking
- Minimal frontend bundle size via Vite tree-shaking</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/ai/project_knowledge/01-PROJECT-OVERVIEW.md
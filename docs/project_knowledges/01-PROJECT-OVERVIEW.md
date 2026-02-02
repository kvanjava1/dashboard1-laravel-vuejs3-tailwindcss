# 01-PROJECT-OVERVIEW.md

## Project Overview

### Project Name
**Laravel User Management Dashboard**

### Primary Purpose and Domain
This is a comprehensive user management system built with Laravel and Vue.js, designed for administrative control over user accounts, roles, and permissions. The system provides a web-based dashboard for managing users in a role-based access control (RBAC) environment.

### Business Context and Problem Solved
The application addresses the need for secure, scalable user management in web applications that require:
- Multi-level user access control
- Account protection mechanisms
- Audit trails for user actions
- Role-based permissions
- User banning/unbanning capabilities
- Profile management with image uploads

### Key Business Entities and Relationships

#### Core Entities:
1. **Users** - System users with authentication and profile data
2. **Roles** - Permission groupings (super_admin, administrator, editor, viewer, moderator)
3. **Permissions** - Granular access controls (user_management.view, user_management.add, etc.)
4. **User Ban History** - Audit trail of ban/unban actions

#### Key Relationships:
- Users ↔ Roles (Many-to-Many through Spatie Permission package)
- Users ↔ Permissions (Many-to-Many through roles)
- Users → User Ban History (One-to-Many)
- Users → Users (Self-referencing for performed_by in ban history)

### Technology Stack

#### Backend (Laravel 12.0)
- **Framework**: Laravel 12.0 (PHP 8.2)
- **Authentication**: Laravel Sanctum (API token-based)
- **Database**: MySQL/PostgreSQL (Eloquent ORM)
- **Permissions**: Spatie Laravel Permission (v6.24)
- **File Storage**: Laravel Storage (local/public disk)
- **Caching**: Laravel Cache (multiple drivers)
- **Queues**: Laravel Queues (database driver)
- **Testing**: PHPUnit 11.5.3
- **Development Tools**: Laravel Sail, Pint, Pail

#### Frontend (Vue.js 3)
- **Framework**: Vue.js 3.5.26
- **Build Tool**: Vite 7.3.1
- **State Management**: Pinia 3.0.4
- **Routing**: Vue Router 4.6.4
- **UI Components**: Headless UI 1.7.23, Heroicons 2.2.0
- **HTTP Client**: Axios 1.13.2
- **Styling**: Tailwind CSS 4.1.18
- **Image Handling**: Vue Advanced Cropper 2.8.9
- **Notifications**: SweetAlert2 11.26.17
- **TypeScript**: TypeScript 5.9.3

#### Development Environment
- **Containerization**: Docker (PHP 8.2, Node.js 20, Nginx)
- **Version Control**: Git
- **Code Quality**: Laravel Pint, ESLint
- **API Documentation**: Inline PHPDoc comments

### Core Workflows

#### 1. User Authentication Flow
```
Login Request → Sanctum Token Validation → User Session → Permission Check → Dashboard Access
```

#### 2. User Management Flow
```
User List → Filter/Search → CRUD Operations → Role Assignment → Profile Updates
```

#### 3. Ban/Unban Workflow
```
Ban Request → Protection Check → Ban Execution → History Logging → Status Update
```

#### 4. Role Management Flow
```
Role Creation → Permission Assignment → User Role Updates → Access Control
```

### Security Features
- **Account Protection**: Configurable protection for critical accounts
- **Role-Based Access Control**: Granular permissions system
- **Audit Trails**: Complete logging of user actions
- **Rate Limiting**: Throttled authentication endpoints
- **Input Validation**: Comprehensive request validation
- **CSRF Protection**: Laravel Sanctum token validation

### Deployment Architecture
- **Web Server**: Nginx (reverse proxy)
- **Application Server**: PHP-FPM 8.2
- **Database**: MySQL/PostgreSQL
- **File Storage**: Local filesystem with public access
- **Session Storage**: Database/cache
- **Queue Processing**: Database queues

### Key Business Rules
1. **Super Admin Protection**: Critical accounts cannot be deleted or have roles changed
2. **Permanent Bans**: All bans are permanent (no time-based expiration)
3. **Soft Deletes**: Users are soft-deleted to maintain data integrity
4. **Role Hierarchy**: Permissions are assigned through roles, not directly to users
5. **Audit Requirements**: All ban/unban actions must be logged with reasons

### Performance Considerations
- **Pagination**: Large user datasets are paginated (15 per page default)
- **Eager Loading**: Relationships loaded efficiently to prevent N+1 queries
- **Caching**: Permission checks and role data cached where appropriate
- **Image Optimization**: Profile images converted to WebP format
- **Database Indexing**: Proper indexing on frequently queried columns

### Scalability Features
- **Queue System**: Background processing for heavy operations
- **Database Optimization**: Efficient queries with selective column loading
- **API Design**: RESTful endpoints with consistent response formats
- **Modular Architecture**: Service layer separation for maintainability</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/project_knowledges/01-PROJECT-OVERVIEW.md
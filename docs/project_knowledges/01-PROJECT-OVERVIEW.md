# 01-PROJECT-OVERVIEW.md

## Project Name
Laravel Vue.js Admin Dashboard

## Primary Purpose and Domain
This is a comprehensive admin dashboard application built with Laravel backend and Vue.js 3 frontend. The system provides user management, role-based access control, and serves as a foundation for administrative interfaces with forum-like features.

## Technology Stack

### Backend
- **Framework**: Laravel 12.0
- **PHP Version**: 8.2.30
- **Database**: MySQL (via Laravel migrations)
- **Authentication**: Laravel Sanctum (API token-based)
- **Authorization**: Spatie Laravel Permission (role-based permissions)
- **Queue System**: Laravel Queues
- **Caching**: Laravel Cache
- **File Storage**: Laravel Storage (local filesystem)

### Frontend
- **Framework**: Vue.js 3.5.26
- **Build Tool**: Vite 7.3.1
- **State Management**: Pinia 3.0.4
- **Routing**: Vue Router 4.6.4
- **Styling**: Tailwind CSS 4.1.18
- **UI Components**: Headless UI, Heroicons
- **HTTP Client**: Axios 1.13.2

### Development Tools
- **Containerization**: Docker (nginx, PHP 8.2, Node.js 20)
- **Testing**: PHPUnit 11.5.3
- **Code Quality**: Laravel Pint
- **Package Management**: Composer, npm

## Business Context
This application serves as an administrative dashboard for managing users, roles, and permissions in a system that includes forum-like features. It's designed for organizations that need:
- User account management
- Role-based access control
- Administrative oversight
- Secure API access

## Key Business Entities

### User
- Core entity representing system users
- Includes profile information
- Supports soft deletes and ban management
- Has roles and permissions

### Role
- Defines user capabilities and access levels
- Uses Spatie Permission package
- Hierarchical permission system

### Permission
- Granular access controls
- Grouped by features (user_management, role_management, etc.)

### Example Management
- Sample CRUD entity for demonstration
- Shows standard management patterns

## Core Workflows

### User Management
1. User registration/authentication
2. Profile management
3. Role assignment
4. Account status management (active/inactive/banned)

### Administrative Oversight
1. User CRUD operations with filtering
2. Role and permission management
3. System monitoring and logging

## Security Features
- API token authentication (Sanctum)
- Role-based permissions
- User banning system
- Password hashing
- Rate limiting on auth endpoints
- Input validation and sanitization
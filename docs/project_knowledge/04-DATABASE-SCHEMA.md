# 04-DATABASE-SCHEMA.md

## Database Schema Overview

Dashboard1 uses MySQL as the primary database with InnoDB engine for ACID compliance and foreign key constraints. The schema follows Laravel conventions with soft deletes, timestamps, and proper indexing.

## Core Tables and Relationships

### Entity-Relationship Diagram (Text Format)

```
┌─────────────────┐       ┌─────────────────┐
│     users       │       │user_account_    │
│                 │       │   statuses      │
│ - id (PK)       │◄──────┤                 │
│ - name          │       │ - id (PK)       │
│ - email         │       │ - name          │
│ - phone         │       │ - display_name  │
│ - user_account_ │       │ - color         │
│   status_id (FK)│       │ - is_active     │
│ - password      │       │ - sort_order    │
│ - profile_image │       │ - timestamps    │
│ - username      │       └─────────────────┘
│ - bio           │
│ - date_of_birth │
│ - location      │
│ - timezone      │
│ - is_banned     │
│ - ban_reason    │
│ - banned_until  │
│ - timestamps    │
│ - deleted_at    │
└─────────────────┘
         │
         │
         ▼
┌─────────────────┐       ┌─────────────────┐
│user_ban_history │       │     roles       │
│                 │       │                 │
│ - id (PK)       │       │ - id (PK)       │
│ - user_id (FK)  │       │ - name          │
│ - action        │       │ - guard_name    │
│ - reason        │       │ - timestamps    │
│ - banned_until  │       └─────────────────┘
│ - performed_by  │             │
│   (FK to users) │             │
│ - timestamps    │             │
└─────────────────┘             │
                               │
                               ▼
                      ┌─────────────────┐
                      │   permissions   │
                      │                 │
                      │ - id (PK)       │
                      │ - name          │
                      │ - guard_name    │
                      │ - timestamps    │
                      └─────────────────┘

┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│ model_has_roles │       │model_has_       │       │role_has_        │
│                 │       │ permissions     │       │ permissions     │
│ - role_id (FK)  │       │                 │       │                 │
│ - model_type    │       │ - permission_id │       │ - permission_id │
│ - model_id      │       │   (FK)          │       │   (FK)          │
│   (Polymorphic) │       │ - model_type    │       │ - role_id (FK)  │
│                 │       │ - model_id      │       │                 │
└─────────────────┘       │   (Polymorphic) │       └─────────────────┘
                         └─────────────────┘
```

## Table Schemas

### users
Primary user table containing authentication and profile information.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Full name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email address |
| email_verified_at | TIMESTAMP | NULLABLE | Email verification timestamp |
| phone | VARCHAR(255) | NULLABLE | Phone number |
| user_account_status_id | BIGINT UNSIGNED | FK, NULLABLE | Foreign key to user_account_statuses |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| profile_image | VARCHAR(255) | NULLABLE | Profile image path |
| username | VARCHAR(255) | UNIQUE, NULLABLE | Unique username |
| bio | TEXT | NULLABLE | User biography |
| date_of_birth | DATE | NULLABLE | Date of birth |
| location | VARCHAR(255) | NULLABLE | User location |
| timezone | VARCHAR(255) | DEFAULT 'UTC' | User timezone |
| is_banned | BOOLEAN | DEFAULT FALSE | Ban status |
| ban_reason | TEXT | NULLABLE | Ban reason |
| banned_until | TIMESTAMP | NULLABLE | Ban expiration |
| remember_token | VARCHAR(100) | NULLABLE | Remember token |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)
- UNIQUE KEY (username)
- INDEX (user_account_status_id)
- INDEX (deleted_at)

**Relationships:**
- BelongsTo: user_account_statuses (user_account_status_id)
- HasMany: user_ban_history (user_id)
- ManyToMany: roles (via model_has_roles)
- ManyToMany: permissions (via model_has_permissions)

### user_account_statuses
Configurable account status types (active, banned, not_activated, etc.)

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | UNIQUE, NOT NULL | Status identifier |
| display_name | VARCHAR(255) | NOT NULL | Human-readable name |
| color | VARCHAR(255) | NULLABLE | UI color code |
| is_active | BOOLEAN | DEFAULT TRUE | Status availability |
| sort_order | INT | DEFAULT 0 | Display order |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (name)

**Relationships:**
- HasMany: users (user_account_status_id)

### user_ban_histories
Audit trail for all ban and unban actions.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK, NOT NULL | Banned user |
| action | ENUM('ban','unban') | NOT NULL | Action type |
| reason | TEXT | NOT NULL | Action reason |
| banned_until | TIMESTAMP | NULLABLE | Ban expiration (bans only) |
| performed_by | BIGINT UNSIGNED | FK, NULLABLE | Admin who performed action |
| created_at | TIMESTAMP | NULLABLE | Action timestamp |
| updated_at | TIMESTAMP | NULLABLE | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id, created_at)
- INDEX (action)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL

**Relationships:**
- BelongsTo: users (user_id)
- BelongsTo: users (performed_by)

### roles (Spatie Permission)
Role definitions for RBAC system.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Role identifier |
| guard_name | VARCHAR(255) | NOT NULL | Guard name (web/api) |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (name, guard_name)

**Relationships:**
- ManyToMany: users (via model_has_roles)
- ManyToMany: permissions (via role_has_permissions)

### permissions (Spatie Permission)
Permission definitions for granular access control.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Permission identifier |
| guard_name | VARCHAR(255) | NOT NULL | Guard name (web/api) |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (name, guard_name)

**Relationships:**
- ManyToMany: roles (via role_has_permissions)
- ManyToMany: users (via model_has_permissions)

### model_has_roles (Spatie Permission)
Polymorphic many-to-many relationship between models and roles.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| role_id | BIGINT UNSIGNED | PK, FK | Role ID |
| model_type | VARCHAR(255) | PK | Model class name |
| model_id | BIGINT UNSIGNED | PK | Model instance ID |

**Indexes:**
- PRIMARY KEY (role_id, model_id, model_type)
- INDEX (model_id, model_type)
- FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE

### model_has_permissions (Spatie Permission)
Polymorphic many-to-many relationship between models and permissions.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| permission_id | BIGINT UNSIGNED | PK, FK | Permission ID |
| model_type | VARCHAR(255) | PK | Model class name |
| model_id | BIGINT UNSIGNED | PK | Model instance ID |

**Indexes:**
- PRIMARY KEY (permission_id, model_id, model_type)
- INDEX (model_id, model_type)
- FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE

### role_has_permissions (Spatie Permission)
Many-to-many relationship between roles and permissions.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| permission_id | BIGINT UNSIGNED | PK, FK | Permission ID |
| role_id | BIGINT UNSIGNED | PK, FK | Role ID |

**Indexes:**
- PRIMARY KEY (permission_id, role_id)
- FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
- FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE

## Laravel Standard Tables

### password_reset_tokens
Token storage for password reset functionality.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| email | VARCHAR(255) | PK | User email |
| token | VARCHAR(255) | NOT NULL | Reset token |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |

### sessions
Session storage for web authentication.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | VARCHAR(255) | PK | Session ID |
| user_id | BIGINT UNSIGNED | FK, NULLABLE, INDEX | Associated user |
| ip_address | VARCHAR(45) | NULLABLE | Client IP |
| user_agent | TEXT | NULLABLE | User agent string |
| payload | LONGTEXT | NOT NULL | Session data |
| last_activity | INT | INDEX | Last activity timestamp |

### personal_access_tokens (Sanctum)
API token storage for Sanctum authentication.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Token name |
| token | VARCHAR(64) | UNIQUE, NOT NULL | Hashed token |
| abilities | TEXT | NULLABLE | Token abilities |
| tokenable_type | VARCHAR(255) | NOT NULL | Model type |
| tokenable_id | BIGINT UNSIGNED | NOT NULL | Model ID |
| last_used_at | TIMESTAMP | NULLABLE | Last usage |
| expires_at | TIMESTAMP | NULLABLE, INDEX | Expiration |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (token)
- INDEX (tokenable_type, tokenable_id)

### jobs (Laravel Queue)
Job queue storage.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| queue | VARCHAR(255) | NOT NULL | Queue name |
| payload | LONGTEXT | NOT NULL | Job payload |
| attempts | TINYINT UNSIGNED | NOT NULL | Attempt count |
| reserved_at | INT UNSIGNED | NULLABLE | Reservation timestamp |
| available_at | INT UNSIGNED | NOT NULL | Available timestamp |
| created_at | INT UNSIGNED | NOT NULL | Creation timestamp |

### cache
Cache storage.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| key | VARCHAR(255) | PK | Cache key |
| value | MEDIUMTEXT | NOT NULL | Cached value |
| expiration | INT | NOT NULL | Expiration timestamp |

## Schema Evolution (Migration History)

### Initial Schema (0001_01_01_000000_create_users_table.php)
- Basic Laravel user table
- Password reset tokens
- Sessions table

### Forum Features (2024_01_24_000000_add_forum_fields_to_users_table.php)
- Profile image, username, bio
- Date of birth, location, timezone
- Ban fields (is_banned, ban_reason, banned_until)

### Enhanced User Fields (2026_01_24_142724_add_phone_and_status_to_users_table.php)
- Phone number field
- Status field (later replaced with relationship)

### Permission System (2026_01_15_152640_create_permission_tables.php)
- Spatie Laravel Permission tables
- Roles, permissions, pivot tables

### Account Status System (2026_01_28_180425_create_user_account_statuses_table.php)
- Configurable account statuses
- Display names, colors, sort order

### Status Relationship (2026_01_28_181244_add_user_account_status_id_to_users_table.php)
- Foreign key to user_account_statuses
- Data migration from status column

### Ban Fields Cleanup (2026_01_28_184137_drop_ban_fields_from_users_table.php)
- Temporary removal of ban fields
- Later restored in separate table

### Status Column Removal (2026_01_28_184339_drop_status_column_from_users_table.php)
- Remove old status column
- Complete migration to relationship

### Ban Fields Restoration (2026_01_31_041410_add_ban_fields_back_to_users_table.php)
- Restore ban fields to users table
- Keep ban history separate

### Ban History System (2026_01_31_074635_create_user_ban_histories_table.php)
- Complete audit trail for ban actions
- Links to performing users

## Database Constraints and Rules

### Foreign Key Constraints
- All foreign keys use appropriate ON DELETE actions
- CASCADE for dependent data (user_ban_histories)
- SET NULL for optional references (performed_by)

### Unique Constraints
- Email uniqueness (active users only, soft deletes allow reuse)
- Username uniqueness
- Permission and role name uniqueness per guard

### Data Integrity Rules
- Ban status logic prevents invalid states
- Status relationships maintain consistency
- Polymorphic relations support multiple model types

### Performance Optimizations
- Indexes on frequently queried columns
- Composite indexes for complex queries
- Foreign key indexes automatically created

## Data Relationships Summary

### One-to-Many Relationships
- user_account_statuses → users (1:N)
- users → user_ban_histories (1:N)

### Many-to-Many Relationships
- users ↔ roles (via model_has_roles)
- users ↔ permissions (via model_has_permissions)
- roles ↔ permissions (via role_has_permissions)

### Polymorphic Relationships
- model_has_roles: Any model can have roles
- model_has_permissions: Any model can have permissions

### Self-Referencing Relationships
- users.performed_by → users (for ban history)

## Migration Strategy

### Version Control
- Timestamped migration files
- Sequential execution order
- Rollback capability for all changes

### Data Migration
- Safe data transformations
- Foreign key population
- Backward compatibility preservation

### Schema Changes
- Non-destructive alterations
- Index additions for performance
- Constraint additions for integrity

## Indexing Strategy

### Primary Indexes
- All tables have primary key indexes
- Auto-incrementing BIGINT for performance

### Foreign Key Indexes
- Automatic creation by InnoDB
- Composite indexes for pivot tables

### Query Optimization Indexes
- user_ban_histories: (user_id, created_at) for history queries
- user_ban_histories: (action) for action filtering
- users: (deleted_at) for soft delete queries

### Unique Indexes
- Email and username uniqueness
- Permission/role name uniqueness per guard

## Backup and Recovery

### Critical Data
- User accounts and authentication data
- Role and permission assignments
- Ban history and audit trails

### Backup Strategy
- Daily automated backups
- Point-in-time recovery capability
- Encrypted backup storage

### Recovery Procedures
- Schema recreation via migrations
- Data restoration from backups
- Integrity verification post-recovery</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/ai/project_knowledge/04-DATABASE-SCHEMA.md
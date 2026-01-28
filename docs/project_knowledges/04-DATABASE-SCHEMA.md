# 04-DATABASE-SCHEMA.md

## Database Schema Overview

This application uses MySQL with Laravel migrations to manage schema evolution. The database follows Laravel conventions with soft deletes and timestamp tracking.

## Core Tables

### users
Primary user table with authentication and profile data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | varchar(255) | NOT NULL | User's full name |
| email | varchar(255) | UNIQUE, NOT NULL | Email address |
| phone | varchar(255) | NULL | Phone number |
| status | varchar(255) | DEFAULT 'active' | Account status |
| email_verified_at | timestamp | NULL | Email verification timestamp |
| password | varchar(255) | NOT NULL | Hashed password |
| remember_token | varchar(100) | NULL | Remember token |
| profile_image | varchar(255) | NULL | Profile image path |
| username | varchar(255) | UNIQUE, NULL | Forum username |
| bio | text | NULL | User biography |
| date_of_birth | date | NULL | Birth date |
| location | varchar(255) | NULL | User location |
| is_banned | boolean | DEFAULT false | Ban status |
| ban_reason | text | NULL | Ban reason |
| banned_until | timestamp | NULL | Ban expiration |
| timezone | varchar(255) | DEFAULT 'UTC' | User timezone |
| created_at | timestamp | NULL | Creation timestamp |
| updated_at | timestamp | NULL | Update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

### password_reset_tokens
Stores password reset tokens.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| email | varchar(255) | PRIMARY KEY | User email |
| token | varchar(255) | NOT NULL | Reset token |
| created_at | timestamp | NULL | Creation timestamp |

### sessions
Laravel session storage.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | varchar(255) | PRIMARY KEY | Session ID |
| user_id | bigint unsigned | NULL, INDEX | Associated user |
| ip_address | varchar(45) | NULL | IP address |
| user_agent | text | NULL | User agent string |
| payload | longtext | NOT NULL | Session data |
| last_activity | int | INDEX, NOT NULL | Last activity timestamp |

## Permission System Tables (Spatie Laravel Permission)

### permissions
Individual permissions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Permission ID |
| name | varchar(255) | NOT NULL | Permission name |
| guard_name | varchar(255) | NOT NULL | Guard name |
| created_at | timestamp | NULL | Creation timestamp |
| updated_at | timestamp | NULL | Update timestamp |

**Unique Constraint**: (name, guard_name)

### roles
User roles.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Role ID |
| name | varchar(255) | NOT NULL | Role name |
| guard_name | varchar(255) | NOT NULL | Guard name |
| created_at | timestamp | NULL | Creation timestamp |
| updated_at | timestamp | NULL | Update timestamp |

**Unique Constraint**: (name, guard_name)

### model_has_permissions
Pivot table for direct permission assignment.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| permission_id | bigint unsigned | NOT NULL, FOREIGN KEY | Permission ID |
| model_type | varchar(255) | NOT NULL | Model class name |
| model_id | bigint unsigned | NOT NULL | Model instance ID |

**Primary Key**: (permission_id, model_id, model_type)

### model_has_roles
Pivot table for role assignment.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| role_id | bigint unsigned | NOT NULL, FOREIGN KEY | Role ID |
| model_type | varchar(255) | NOT NULL | Model class name |
| model_id | bigint unsigned | NOT NULL | Model instance ID |

**Primary Key**: (role_id, model_id, model_type)

### role_has_permissions
Many-to-many relationship between roles and permissions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| permission_id | bigint unsigned | NOT NULL, FOREIGN KEY | Permission ID |
| role_id | bigint unsigned | NOT NULL, FOREIGN KEY | Role ID |

**Primary Key**: (permission_id, role_id)

## Sanctum Authentication

### personal_access_tokens
API tokens for Sanctum authentication.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Token ID |
| tokenable_type | varchar(255) | NOT NULL | Model class name |
| tokenable_id | bigint unsigned | NOT NULL, INDEX | Model instance ID |
| name | varchar(255) | NOT NULL | Token name |
| token | varchar(64) | UNIQUE, NOT NULL | Hashed token |
| abilities | text | NULL | Token abilities (JSON) |
| last_used_at | timestamp | NULL | Last usage timestamp |
| expires_at | timestamp | NULL, INDEX | Expiration timestamp |
| created_at | timestamp | NULL | Creation timestamp |
| updated_at | timestamp | NULL | Update timestamp |

## Laravel Framework Tables

### cache
Cache storage.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| key | varchar(255) | PRIMARY KEY | Cache key |
| value | mediumtext | NOT NULL | Cached value |
| expiration | int | NOT NULL | Expiration timestamp |

### jobs
Queue jobs.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Job ID |
| queue | varchar(255) | NOT NULL | Queue name |
| payload | longtext | NOT NULL | Job payload |
| attempts | tinyint unsigned | NOT NULL | Attempt count |
| reserved_at | int unsigned | NULL | Reservation timestamp |
| available_at | int unsigned | NOT NULL | Available timestamp |
| created_at | int unsigned | NOT NULL | Creation timestamp |

## Example/Demo Tables

### example_management
Sample CRUD entity.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Record ID |
| name | varchar(255) | NOT NULL | Example name |
| created_at | timestamp | NULL | Creation timestamp |
| updated_at | timestamp | NULL | Update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

## Entity Relationships

```
User (1) ──── (N) Personal Access Tokens
User (N) ──── (N) Roles (via model_has_roles)
User (N) ──── (N) Permissions (via model_has_permissions)
Role (N) ──── (N) Permissions (via role_has_permissions)
User (1) ──── (1) Session (current)
```

## Schema Evolution

The schema has evolved through several migrations:

1. **Initial Setup** (2024): Basic user table with auth
2. **Forum Features** (2024): Added profile and forum fields
3. **Permission System** (2026-01-15): Spatie permission tables
4. **API Authentication** (2026-01-16): Sanctum tokens
5. **Enhanced User Fields** (2026-01-24): Phone and status
6. **Example Management** (2026-01-25): Demo CRUD table

## Indexes and Performance

### Primary Indexes
- All tables have primary key indexes
- Foreign key constraints with indexes

### Additional Indexes
- `users.email` (unique)
- `users.username` (unique)
- `sessions.user_id`
- `sessions.last_activity`
- `personal_access_tokens.tokenable_type + tokenable_id`
- `personal_access_tokens.token`
- `personal_access_tokens.expires_at`

### Composite Indexes
- Permission and role pivot tables have composite primary keys
- Model morph indexes for polymorphic relationships

## Data Integrity

### Foreign Key Constraints
- Permission and role relationships maintain referential integrity
- Cascade deletes prevent orphaned records

### Unique Constraints
- Email and username uniqueness
- Permission/role name uniqueness per guard
- Token uniqueness

### Check Constraints
- Status enum validation (active/inactive/pending)
- Boolean fields for banned status

## Migration Strategy

- **Incremental**: Each change in separate migration
- **Reversible**: All migrations have down() methods
- **Versioned**: Timestamp-based migration naming
- **Environment-aware**: Different configs for dev/prod
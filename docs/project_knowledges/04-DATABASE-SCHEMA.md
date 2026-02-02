# 04-DATABASE-SCHEMA.md

## Database Schema Analysis

### Database Design Overview

The application uses a **MySQL/PostgreSQL** database with a **normalized relational schema** optimized for user management operations. The schema implements **role-based access control (RBAC)** using the Spatie Laravel Permission package.

### Core Tables and Relationships

#### Entity-Relationship Diagram (Text Representation)
```
┌─────────────┐     ┌─────────────┐     ┌─────────────────┐
│   users     │     │    roles    │     │  permissions    │
├─────────────┤     ├─────────────┤     ├─────────────────┤
│ id (PK)     │     │ id (PK)     │     │ id (PK)         │
│ name        │     │ name        │     │ name            │
│ email       │     │ guard_name  │     │ guard_name      │
│ password    │     │ created_at  │     │ created_at      │
│ phone       │     │ updated_at  │     │ updated_at      │
│ profile_img │     └─────────────┘     └─────────────────┘
│ is_banned   │             │                       │
│ is_active   │             │                       │
│ created_at  │             │                       │
│ updated_at  │             │                       │
│ deleted_at  │             ▼                       ▼
└─────────────┘    ┌─────────────────┐    ┌─────────────────┐
                   │model_has_roles  │    │role_has_permiss │
                   ├─────────────────┤    ├─────────────────┤
                   │ role_id (FK)    │    │ role_id (FK)    │
                   │ model_type      │    │ permission_id   │
                   │ model_id (FK)   │    │                 │
                   └─────────────────┘    └─────────────────┘
                            │
                            ▼
                   ┌─────────────────┐     ┌─────────────────┐
                   │user_ban_history │     │  sessions       │
                   ├─────────────────┤     ├─────────────────┤
                   │ id (PK)         │     │ id (PK)         │
                   │ user_id (FK)    │     │ user_id (FK)    │
                   │ action          │     │ payload         │
                   │ reason          │     │ last_activity   │
                   │ banned_until    │     └─────────────────┘
                   │ is_forever      │
                   │ performed_by    │
                   │ created_at      │
                   └─────────────────┘
```

### Detailed Table Schemas

#### 1. users Table
**Purpose**: Core user account information and authentication data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier |
| name | VARCHAR(255) | NOT NULL | Full display name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Login email address |
| email_verified_at | TIMESTAMP | NULLABLE | Email verification timestamp |
| password | VARCHAR(255) | NOT NULL | Bcrypt hashed password |
| phone | VARCHAR(255) | NULLABLE | Contact phone number |
| profile_image | VARCHAR(255) | NULLABLE | Path to profile image file |
| is_banned | BOOLEAN | DEFAULT FALSE, NOT NULL | Account ban status |
| is_active | BOOLEAN | DEFAULT TRUE, NOT NULL | Account activation status |
| remember_token | VARCHAR(100) | NULLABLE | Password reset token |
| created_at | TIMESTAMP | NULLABLE | Record creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Record update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `email`
- INDEX on `deleted_at` (for soft delete queries)

**Relationships**:
- One-to-Many: `user_ban_history.performed_by` → `users.id`
- Many-to-Many: `users` ↔ `roles` (via `model_has_roles`)
- Many-to-Many: `users` ↔ `permissions` (via `model_has_roles` → `role_has_permissions`)

#### 2. roles Table (Spatie Permission)
**Purpose**: Role definitions for access control grouping.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique role identifier |
| name | VARCHAR(255) | NOT NULL | Role name (e.g., 'administrator') |
| guard_name | VARCHAR(255) | NOT NULL | Authentication guard name |
| created_at | TIMESTAMP | NULLABLE | Record creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Record update timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `name`, `guard_name`

**Relationships**:
- Many-to-Many: `roles` ↔ `users` (via `model_has_roles`)
- Many-to-Many: `roles` ↔ `permissions` (via `role_has_permissions`)

#### 3. permissions Table (Spatie Permission)
**Purpose**: Individual permission definitions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique permission identifier |
| name | VARCHAR(255) | NOT NULL | Permission name (e.g., 'user_management.view') |
| guard_name | VARCHAR(255) | NOT NULL | Authentication guard name |
| created_at | TIMESTAMP | NULLABLE | Record creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Record update timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `name`, `guard_name`

**Relationships**:
- Many-to-Many: `permissions` ↔ `roles` (via `role_has_permissions`)

#### 4. model_has_roles Table (Spatie Permission)
**Purpose**: Junction table linking users to their assigned roles.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| role_id | BIGINT UNSIGNED | FOREIGN KEY → roles.id | Role identifier |
| model_type | VARCHAR(255) | NOT NULL | Model class name (App\Models\User) |
| model_id | BIGINT UNSIGNED | FOREIGN KEY → users.id | User identifier |

**Indexes**:
- PRIMARY KEY on `role_id`, `model_id`, `model_type`
- INDEX on `model_id`, `model_type`

#### 5. model_has_permissions Table (Spatie Permission)
**Purpose**: Direct permission assignments (bypassing roles).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| permission_id | BIGINT UNSIGNED | FOREIGN KEY → permissions.id | Permission identifier |
| model_type | VARCHAR(255) | NOT NULL | Model class name |
| model_id | BIGINT UNSIGNED | FOREIGN KEY → users.id | User identifier |

**Indexes**:
- PRIMARY KEY on `permission_id`, `model_id`, `model_type`
- INDEX on `model_id`, `model_type`

#### 6. role_has_permissions Table (Spatie Permission)
**Purpose**: Links roles to their assigned permissions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| permission_id | BIGINT UNSIGNED | FOREIGN KEY → permissions.id | Permission identifier |
| role_id | BIGINT UNSIGNED | FOREIGN KEY → roles.id | Role identifier |

**Indexes**:
- PRIMARY KEY on `permission_id`, `role_id`

#### 7. user_ban_histories Table
**Purpose**: Audit trail for all ban and unban actions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique history record ID |
| user_id | BIGINT UNSIGNED | FOREIGN KEY → users.id, NOT NULL | User being banned/unbanned |
| action | ENUM('ban', 'unban') | NOT NULL | Type of action performed |
| reason | TEXT | NOT NULL | Reason for the action |
| banned_until | TIMESTAMP | NULLABLE | Future ban expiration (currently unused) |
| is_forever | BOOLEAN | DEFAULT FALSE, NOT NULL | Whether ban is permanent |
| performed_by | BIGINT UNSIGNED | FOREIGN KEY → users.id, NULLABLE | Administrator who performed action |
| created_at | TIMESTAMP | NULLABLE | Action timestamp |
| updated_at | TIMESTAMP | NULLABLE | Record update timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `user_id`, `created_at`
- INDEX on `action`
- FOREIGN KEY on `user_id` → `users.id` (CASCADE on delete)
- FOREIGN KEY on `performed_by` → `users.id` (SET NULL on delete)

#### 8. sessions Table (Laravel Built-in)
**Purpose**: Session storage for web authentication.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | VARCHAR(255) | PRIMARY KEY | Session identifier |
| user_id | BIGINT UNSIGNED | FOREIGN KEY → users.id, NULLABLE, INDEX | Associated user |
| ip_address | VARCHAR(45) | NULLABLE | Client IP address |
| user_agent | TEXT | NULLABLE | Client user agent string |
| payload | LONGTEXT | NOT NULL | Serialized session data |
| last_activity | INT | INDEX, NOT NULL | Unix timestamp of last activity |

#### 9. password_reset_tokens Table (Laravel Built-in)
**Purpose**: Password reset token storage.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| email | VARCHAR(255) | PRIMARY KEY | User email address |
| token | VARCHAR(255) | NOT NULL | Reset token |
| created_at | TIMESTAMP | NULLABLE | Token creation timestamp |

### Database Schema Evolution

#### Migration Timeline
1. **0001_01_01_000000_create_users_table.php**
   - Basic user table with authentication fields
   - Password reset tokens table
   - Sessions table

2. **0001_01_01_000001_create_cache_table.php**
   - Laravel caching tables

3. **0001_01_01_000002_create_jobs_table.php**
   - Laravel queue tables

4. **2026_01_15_152640_create_permission_tables.php**
   - Spatie permission system tables
   - Roles, permissions, and relationship tables

5. **2026_01_16_074940_create_personal_access_tokens_table.php**
   - Laravel Sanctum personal access tokens

6. **2026_01_31_041410_add_ban_fields_back_to_users_table.php**
   - Added `is_banned` and `is_active` fields
   - Added `profile_image` field

7. **2026_01_31_074611_create_user_ban_histories_table.php**
   - Complete ban/unban audit trail table

8. **2026_02_01_133832_add_phone_field_to_users_table.php**
   - Added phone number field to users table

### Key Database Design Patterns

#### 1. Soft Deletes Pattern
- Users table uses `deleted_at` for data preservation
- Maintains referential integrity
- Allows account restoration

#### 2. Audit Trail Pattern
- `user_ban_histories` table tracks all moderation actions
- Includes performer information and timestamps
- Supports compliance and debugging

#### 3. Role-Based Access Control (RBAC)
- Three-level hierarchy: Users → Roles → Permissions
- Flexible permission assignment
- Supports both role-based and direct permissions

#### 4. Polymorphic Relationships
- `model_has_roles` and `model_has_permissions` support multiple model types
- Extensible for future entity types

### Indexing Strategy

#### Performance Indexes
- **Primary Keys**: All tables have auto-incrementing primary keys
- **Foreign Keys**: Automatic indexing on foreign key columns
- **Unique Constraints**: Email uniqueness, role/permission name uniqueness
- **Composite Indexes**: Multi-column indexes for complex queries
- **Partial Indexes**: Conditional indexes where applicable

#### Query Optimization
```sql
-- Optimized user search query
SELECT u.id, u.name, u.email, r.name as role_name
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id
LEFT JOIN roles r ON mhr.role_id = r.id
WHERE u.deleted_at IS NULL
  AND (u.name LIKE ? OR u.email LIKE ?)
  AND u.is_banned = ?
ORDER BY u.created_at DESC
LIMIT 15 OFFSET 0
```

### Data Integrity Constraints

#### Foreign Key Constraints
- **CASCADE on DELETE**: Ban history follows user deletion
- **SET NULL on DELETE**: Performed_by field nullified if admin deleted
- **RESTRICT on UPDATE**: Prevent orphaned references

#### Business Logic Constraints
- **Email Uniqueness**: Enforced at database and application levels
- **Enum Constraints**: Action field limited to 'ban'/'unban'
- **Check Constraints**: Boolean fields default to appropriate values

### Database Performance Considerations

#### Query Patterns Analysis
1. **User Listing**: Paginated queries with filters and sorting
2. **Authentication**: Email lookup with password verification
3. **Permission Checks**: Role and permission lookups per request
4. **Audit Queries**: Historical ban/unban data retrieval

#### Optimization Strategies
- **Connection Pooling**: Database connection reuse
- **Query Caching**: Frequently accessed data cached
- **Index Usage**: Proper indexing for WHERE clauses
- **Eager Loading**: Prevent N+1 query problems in application code

### Backup and Recovery Strategy

#### Critical Data
- **User Accounts**: Core business data, requires regular backup
- **Ban History**: Audit trail, compliance requirement
- **Role Assignments**: Access control configuration

#### Backup Frequency
- **Full Backups**: Daily during low-usage periods
- **Incremental Backups**: Hourly for transaction logs
- **Configuration Backups**: On configuration changes

### Future Schema Extensions

#### Potential Enhancements
1. **User Profile Extensions**: Additional profile fields table
2. **Role Hierarchies**: Parent-child role relationships
3. **Permission Groups**: Logical grouping of permissions
4. **Audit Enhancements**: Extended audit logging for all actions
5. **Multi-Tenancy**: Organization-based data separation

#### Scalability Considerations
- **Partitioning**: Large tables split by date/user ranges
- **Read Replicas**: Separate read and write databases
- **Sharding**: Horizontal scaling for large user bases
- **Archiving**: Old audit data moved to archive tables</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/project_knowledges/04-DATABASE-SCHEMA.md
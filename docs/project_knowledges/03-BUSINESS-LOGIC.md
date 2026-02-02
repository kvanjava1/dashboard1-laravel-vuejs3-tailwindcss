# 03-BUSINESS-LOGIC.md

## Business Logic Analysis

### Core Business Workflows

#### 1. User Registration and Onboarding
**Purpose**: Create new user accounts with proper role assignment and validation.

**Step-by-Step Process**:
```
1. Receive registration request with user data
2. Validate input data (name, email, password, phone, role)
3. Check for existing soft-deleted user with same email
4. If soft-deleted user exists:
   a. Restore the user
   b. Update user data
   c. Re-assign role
5. If new user:
   a. Create user record
   b. Hash password
   c. Assign role
6. Handle profile image upload (convert to WebP)
7. Log creation/restoration action
8. Return formatted user data
```

**Business Rules**:
- Email must be unique across all users (including soft-deleted)
- Password must be hashed before storage
- Default role assignment required
- Profile images converted to WebP format (80% quality)
- Soft-deleted users can be restored instead of creating duplicates

#### 2. User Authentication and Session Management
**Purpose**: Secure login/logout with token-based authentication.

**Step-by-Step Process**:
```
Login Flow:
1. Receive login credentials (email, password)
2. Rate limiting check (5 attempts per 15 minutes)
3. Validate credentials against database
4. Check if user account is active
5. Generate Sanctum API token
6. Return user data with permissions and token

Logout Flow:
1. Receive logout request with valid token
2. Revoke current access token
3. Clear client-side token storage
4. Redirect to login page
```

**Business Rules**:
- Rate limiting prevents brute force attacks
- Inactive users cannot login
- Tokens expire and require refresh
- Single active session per user (token-based)

#### 3. User Management and CRUD Operations
**Purpose**: Comprehensive user account management with filtering and pagination.

**Step-by-Step Process**:
```
List Users:
1. Apply search filters (name, email, phone)
2. Apply status filters (active, inactive, banned)
3. Apply role filters
4. Apply date range filters
5. Sort by specified column and direction
6. Paginate results (15 per page default)
7. Format user data with relationships

Create User:
1. Validate all input data
2. Check account protection rules
3. Create user with encrypted password
4. Assign role and permissions
5. Handle profile image upload
6. Log creation action

Update User:
1. Validate input data
2. Check account protection (role changes)
3. Update user fields
4. Handle password changes (hashing)
5. Update profile image if provided
6. Sync role assignments
7. Log update action

Delete User:
1. Check account protection rules
2. Prevent self-deletion
3. Soft delete user record
4. Log deletion with performer info
```

**Business Rules**:
- Protected accounts cannot be modified/deleted
- Users cannot delete their own accounts
- Role changes require specific permissions
- All actions are logged with performer information
- Soft deletes preserve data integrity

#### 4. User Banning and Moderation System
**Purpose**: Control user access through banning/unbanning with audit trails.

**Step-by-Step Process**:
```
Ban User:
1. Validate ban request (reason required)
2. Check account protection rules
3. Update user is_banned status to true
4. Log ban action in user_ban_history table
5. Record ban reason, performer, and timestamp

Unban User:
1. Verify user is currently banned
2. Update user is_banned status to false
3. Log unban action in history table
4. Record unban reason and performer
```

**Business Rules**:
- All bans are permanent (no time-based expiration)
- Ban reasons are mandatory and logged
- Protected accounts cannot be banned
- Complete audit trail maintained
- Unban requires explicit action

#### 5. Role and Permission Management
**Purpose**: Manage access control through role-based permissions.

**Step-by-Step Process**:
```
Create Role:
1. Validate role name and permissions
2. Check role protection rules
3. Create role record
4. Assign selected permissions to role

Update Role:
1. Check role protection rules
2. Update role name if allowed
3. Sync permissions if allowed
4. Update related user permissions

Delete Role:
1. Check role protection rules
2. Ensure no users assigned to role
3. Delete role record
4. Clean up permission assignments
```

**Business Rules**:
- Super admin role cannot be modified/deleted
- Permissions assigned through roles, not directly to users
- Role changes affect all assigned users
- Protected roles have restricted operations

### Approval/Review Systems

#### Account Protection System
**Purpose**: Prevent accidental modification of critical accounts.

**Protection Levels**:
1. **Deletion Protection**: Cannot delete protected accounts
2. **Role Change Protection**: Cannot modify roles of protected accounts
3. **Profile Update Protection**: Limited profile editing for protected accounts
4. **Ban Protection**: Cannot ban protected accounts

**Configuration**:
```php
'protected_accounts' => [
    'super@admin.com' => [
        'protect_deletion' => true,
        'protect_role_change' => true,
        'protect_profile_update' => true,
        'reason' => 'Super administrator account'
    ]
]
```

### Calculation Logic and Algorithms

#### User Status Calculation
```php
public function getStatusAttribute(): string
{
    if ($this->is_banned) {
        return 'banned';
    }
    return $this->is_active ? 'active' : 'inactive';
}
```

#### Permission Checking Algorithm
```php
public function hasPermission(string $permission): bool
{
    return $this->permissions->contains('name', $permission);
}
```

#### Role Hierarchy Resolution
```php
public function hasRole(string $role): bool
{
    return $this->roles->contains('name', $role);
}
```

### Validation Rules and Business Constraints

#### User Creation Validation
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'nullable|string|max:20',
        'role' => 'required|string|exists:roles,name',
        'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ];
}
```

#### Ban Request Validation
```php
[
    'reason' => 'required|string|max:1000',
    'is_forever' => 'boolean',
    'banned_until' => 'nullable|date|after:now'
]
```

#### Business Constraints
- **Email Uniqueness**: Enforced across active and soft-deleted users
- **Role Existence**: All assigned roles must exist in database
- **Permission Validity**: All permissions must be defined in system
- **Account Status**: Only active users can perform actions
- **Self-Protection**: Users cannot modify their own critical settings

### Integration Points

#### External Systems Integration
- **File Storage**: Laravel Storage facade for profile images
- **Email System**: Laravel Mail (not currently implemented)
- **Cache System**: Laravel Cache for performance optimization
- **Queue System**: Laravel Queues for background processing
- **Logging System**: Laravel Log for audit trails

#### Third-Party Package Integration
- **Spatie Permission**: Role and permission management
- **Laravel Sanctum**: API authentication
- **Intervention Image**: Image processing (WebP conversion)
- **SweetAlert2**: Frontend notifications
- **Vue Advanced Cropper**: Image cropping functionality

### Data Processing Workflows

#### Profile Image Processing
```
1. Receive uploaded image file
2. Validate file type and size
3. Generate unique filename with WebP extension
4. Create dated directory structure (avatar/YYYY/MM/DD/)
5. Convert image to WebP format (80% quality)
6. Store in public disk
7. Update user profile_image field
8. Delete old image if exists
```

#### User Search and Filtering
```
1. Start with base User query
2. Apply search filters (name, email with LIKE)
3. Apply exact filters (role, status, ban status)
4. Apply date range filters
5. Apply sorting (name, email, created_at, updated_at)
6. Eager load relationships (roles)
7. Paginate results
8. Format response data
```

### Audit and Compliance

#### Audit Trail Implementation
- **User Actions**: All CRUD operations logged
- **Ban Actions**: Complete ban/unban history maintained
- **Authentication**: Login/logout events tracked
- **Role Changes**: Permission modifications audited

#### Compliance Features
- **Data Retention**: Soft deletes preserve historical data
- **Access Logging**: All administrative actions logged
- **Reason Requirements**: Ban/unban actions require justification
- **Performer Tracking**: All actions track who performed them

### Business Rule Engine

#### Protection Service Logic
```php
class ProtectionService {
    public function isAccountProtectedFromDeletion(User $user): bool {
        // Check if user email is in protected list
        // Return protection status
    }

    public function getAccountProtectionReason(User $user): string {
        // Return human-readable protection reason
    }
}
```

#### Permission Resolution
```php
class User extends Model {
    public function getAllPermissions(): Collection {
        return $this->getPermissionsViaRoles();
    }

    public function hasPermission(string $permission): bool {
        return $this->hasPermissionViaRole($permission);
    }
}
```

### Error Handling and Recovery

#### Business Logic Error Scenarios
- **Protected Account Violation**: Clear error messages with reasons
- **Permission Denied**: 403 responses with specific error details
- **Validation Failures**: 422 responses with field-specific errors
- **Resource Not Found**: 404 responses for missing entities
- **System Errors**: 500 responses with logging

#### Recovery Mechanisms
- **Soft Deletes**: Restore accidentally deleted users
- **Audit Trails**: Track and potentially reverse actions
- **Transaction Rollbacks**: Database consistency maintenance
- **Graceful Degradation**: System continues operating during failures

### Performance Optimization Logic

#### Query Optimization
- **Selective Loading**: Only load required columns
- **Eager Loading**: Prevent N+1 query problems
- **Pagination**: Limit result set sizes
- **Indexing**: Database indexes on frequently queried fields

#### Caching Strategy
- **Permission Caching**: User permissions cached per session
- **Configuration Caching**: App settings cached
- **Role Data Caching**: Role definitions cached

### Future Extensibility Points

#### Modular Business Logic
- **Service Layer**: Easy to extend with new business rules
- **Event System**: Laravel events for decoupling
- **Middleware**: Pluggable request processing
- **Traits**: Reusable model behaviors

#### Scalability Considerations
- **Queue Processing**: Heavy operations moved to background
- **Database Sharding**: Potential for large user bases
- **API Versioning**: Backward compatibility maintenance
- **Microservice Ready**: Modular architecture supports splitting</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/project_knowledges/03-BUSINESS-LOGIC.md
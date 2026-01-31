# 03-BUSINESS-LOGIC.md

## Core Business Workflows

### 1. User Registration and Onboarding

**Workflow Steps:**
1. **Account Creation**
   - Admin initiates user creation via dashboard
   - Required fields: name, email, password, role, status
   - Optional: phone, bio, profile image

2. **Validation and Constraints**
   - Email uniqueness (across active and soft-deleted users)
   - Password strength requirements
   - Role assignment validation
   - Status must exist in user_account_statuses table

3. **Account Restoration**
   - If email exists in soft-deleted users
   - Restore account with updated information
   - Reassign role and status
   - Log restoration action

4. **Post-Creation Actions**
   - Generate profile image URL if uploaded
   - Sync role permissions
   - Log creation event

**Business Rules:**
- Soft-deleted users can be restored instead of creating duplicates
- Passwords are hashed using bcrypt
- Profile images stored in `storage/app/public/avatars/`
- Default status is configurable

### 2. User Authentication and Session Management

**Login Workflow:**
1. **Credential Validation**
   - Email/password authentication via Laravel Auth
   - Rate limiting: 5 attempts per 15 minutes
   - Account status check (must be active)

2. **Token Generation**
   - Create Sanctum personal access token
   - Include user agent in token name
   - Return token with user data

3. **User Data Enrichment**
   - Load user roles and permissions
   - Format user object with computed fields
   - Include profile image URL

4. **Session Persistence**
   - Store token in localStorage
   - Store user data in localStorage
   - Redirect to dashboard

**Logout Workflow:**
1. **Token Revocation**
   - Delete current access token from database
   - Log logout event with metadata

2. **Client Cleanup**
   - Clear localStorage
   - Reset Pinia store state
   - Redirect to login

**Session Restoration:**
1. **Local Storage Check**
   - Retrieve token and user data from localStorage
   - Validate token existence

2. **Server Validation**
   - Call `/me` endpoint to refresh user data
   - Update localStorage with fresh data
   - Handle token expiration gracefully

### 3. User Management and Administration

**User Listing with Filtering:**
1. **Query Building**
   - Base query with role and status relationships
   - Apply search filters (name, email)
   - Apply field-specific filters (phone, location, etc.)

2. **Role and Status Filtering**
   - Filter by assigned role
   - Filter by account status
   - Filter by ban status

3. **Sorting and Pagination**
   - Configurable sort fields and directions
   - Paginated results (default 15 per page)
   - Include metadata (total, pages, etc.)

4. **Data Formatting**
   - Format user data for API response
   - Include computed fields (status, role_display_name)
   - Generate profile image URLs

**User Profile Updates:**
1. **Protection Checks**
   - Verify account not protected from profile updates
   - Check role change permissions if role being modified

2. **Field Validation**
   - Email uniqueness (excluding current user)
   - Status existence validation
   - File upload validation for profile images

3. **Data Updates**
   - Update basic fields (name, email, phone, etc.)
   - Handle password changes (bcrypt hashing)
   - Process profile image uploads
   - Sync role assignments

4. **Audit Logging**
   - Log update actions with user ID and changes
   - Track who performed the update

### 4. User Moderation and Ban Management

**Ban Workflow:**
1. **Eligibility Verification**
   - Check if account is protected from banning
   - Verify user exists and is not already banned

2. **Ban Execution**
   - Set `is_banned = true`
   - Record ban reason
   - Set optional `banned_until` timestamp
   - Update user record

3. **Audit Trail Creation**
   - Create UserBanHistory record
   - Link to performing user
   - Store ban reason and duration

4. **Notification/Logging**
   - Log ban action with full context
   - Record in application logs

**Unban Workflow:**
1. **Current Status Check**
   - Verify user is currently banned
   - Check if ban has expired naturally

2. **Unban Execution**
   - Set `is_banned = false`
   - Clear `ban_reason` and `banned_until`
   - Update user record

3. **Audit Trail Creation**
   - Create UserBanHistory record for unban action
   - Link to performing user
   - Store unban reason

4. **Status Updates**
   - User can immediately access system
   - Previous ban history preserved

**Ban History Tracking:**
1. **History Retrieval**
   - Query all ban/unban actions for user
   - Include performer information
   - Order by most recent first

2. **Data Presentation**
   - Format for API response
   - Include timestamps and reasons
   - Show performer details

### 5. Role and Permission Management

**Role Creation:**
1. **Validation**
   - Role name uniqueness
   - Guard name validation (default: 'web')
   - Permission selection validation

2. **Role Creation**
   - Create role record
   - Sync selected permissions
   - Log creation event

3. **Permission Assignment**
   - Attach permissions to role
   - Update permission cache if enabled

**Role Modification:**
1. **Protection Checks**
   - Verify role not protected from modification
   - Check user permissions for role editing

2. **Update Execution**
   - Update role name if changed
   - Sync permissions (add/remove as needed)
   - Log modification event

**Role Deletion:**
1. **Protection Verification**
   - Check if role is protected from deletion
   - Verify no users currently assigned to role

2. **Safe Deletion**
   - Remove role-permission associations
   - Delete role record
   - Log deletion event

### 6. Permission System Operations

**Permission Categorization:**
1. **Dynamic Grouping**
   - Parse permission names by dot notation
   - Group permissions by first segment (category)
   - Sort categories alphabetically

2. **Label Generation**
   - Convert snake_case to Title Case
   - Replace dots with spaces
   - Create human-readable labels

**Permission Assignment:**
1. **Role-Based Assignment**
   - Permissions assigned to roles
   - Users inherit permissions through roles
   - No direct user-permission assignment

2. **Permission Checking**
   - Frontend: Check user.permissions array
   - Backend: Middleware validation
   - Route guards and component visibility

## Approval/Review Systems

**Ban Approval Process:**
- **Automatic Approval**: Admins can ban directly
- **Reason Required**: All bans must include reason
- **Audit Trail**: All actions logged with performer
- **No Escalation**: Single-level approval (admin decision)

**User Creation Review:**
- **Admin-Only Creation**: Only admins can create users
- **Immediate Activation**: Users active upon creation
- **No Approval Workflow**: Direct creation by authorized personnel

## Calculation Logic and Algorithms

**User Ban Status Calculation:**
```php
public function isBanned(): bool
{
    return $this->is_banned && 
           ($this->banned_until === null || $this->banned_until->isFuture());
}
```

**Ban Status Text Generation:**
```php
private function getBanStatusText(User $user): string
{
    if (!$user->is_banned) return 'Not Banned';
    if ($user->isPermanentlyBanned()) return 'Permanently Banned';
    if ($user->isTemporarilyBanned()) return 'Temporarily Banned';
    if ($user->hasExpiredBan()) return 'Ban Expired';
    return 'Banned';
}
```

**Pagination Metadata:**
```php
'meta' => [
    'total' => $users->total(),
    'total_pages' => $users->lastPage(),
    'current_page' => $users->currentPage(),
    'per_page' => $users->perPage(),
    'from' => $users->firstItem(),
    'to' => $users->lastItem(),
]
```

## Validation Rules and Business Constraints

### User Creation Validation
```php
'rules' => [
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|string|min:8|confirmed',
    'role' => 'required|string|exists:roles,name',
    'status' => 'required|string|exists:user_account_statuses,name',
    'phone' => 'nullable|string|max:20',
    'bio' => 'nullable|string|max:1000',
    'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
]
```

### User Update Validation
```php
'rules' => [
    'name' => 'sometimes|required|string|max:255',
    'email' => 'sometimes|required|email|unique:users,email,' . $userId,
    'phone' => 'nullable|string|max:20',
    'status' => 'sometimes|required|exists:user_account_statuses,name',
    'role' => 'sometimes|required|exists:roles,name',
    'bio' => 'nullable|string|max:1000',
    'password' => 'nullable|string|min:8|confirmed',
    'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
]
```

### Ban Validation
```php
'rules' => [
    'reason' => 'required|string|max:1000',
    'banned_until' => 'nullable|date|after:now',
]
```

### Business Constraints

#### Account Protection Rules
- **Deletion Protection**: Certain accounts cannot be deleted
- **Role Change Protection**: Certain accounts cannot have roles modified
- **Profile Update Protection**: Certain accounts limited to password/image updates
- **Ban Protection**: Certain accounts immune to banning

#### Role Protection Rules
- **Deletion Protection**: Critical roles cannot be deleted
- **Modification Protection**: Critical roles cannot be modified

#### Data Integrity Constraints
- **Email Uniqueness**: Emails must be unique across active users
- **Role Existence**: Assigned roles must exist
- **Status Existence**: Account statuses must exist
- **Foreign Key Constraints**: Database-level referential integrity

#### Operational Constraints
- **Self-Deletion Prevention**: Users cannot delete their own accounts
- **Super Admin Protection**: Super admin role has elevated protections
- **Soft Delete Preservation**: Deleted accounts maintain referential integrity

## Integration Points with External Systems

### Current Integrations
- **None Active**: System is self-contained
- **Framework Integrations**:
  - Laravel Sanctum (authentication)
  - Spatie Laravel Permission (authorization)
  - Laravel Vite Plugin (asset compilation)

### Potential Integration Points
- **Email Services**: For notifications (configurable)
- **File Storage**: External CDN for profile images
- **Logging Services**: External log aggregation
- **Cache Services**: Redis/external caching
- **Queue Services**: External job processing

### Configuration for Future Integrations
```php
// config/services.php
'mail' => [
    'driver' => env('MAIL_DRIVER', 'smtp'),
    'host' => env('MAIL_HOST'),
    // ... mail configuration
],

'filesystems' => [
    'disks' => [
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            // ... S3 configuration
        ]
    ]
]
```

## Business Rules Engine

### Protection Service Logic
```php
class ProtectionService
{
    public function isAccountProtectedFromDeletion(User $user): bool
    {
        $protectedAccounts = config('protected_entities.protected_accounts');
        
        foreach ($protectedAccounts as $email => $settings) {
            if ($user->email === $email && $settings['protect_deletion'] ?? false) {
                return true;
            }
        }
        
        return false;
    }
    
    // Similar methods for other protections
}
```

### Ban Status Logic
```php
class User extends Model
{
    public function isBanned(): bool
    {
        return $this->is_banned && 
               ($this->banned_until === null || $this->banned_until->isFuture());
    }
    
    public function isPermanentlyBanned(): bool
    {
        return $this->is_banned && $this->banned_until === null;
    }
    
    public function isTemporarilyBanned(): bool
    {
        return $this->is_banned && $this->banned_until !== null && $this->banned_until->isFuture();
    }
    
    public function hasExpiredBan(): bool
    {
        return $this->is_banned && $this->banned_until !== null && $this->banned_until->isPast();
    }
}
```

### Permission Inheritance Logic
- Users inherit permissions from their assigned role
- Permissions checked via `user.permissions` array
- Frontend guards use `authStore.hasPermission(permission)`
- Backend middleware validates via Spatie Permission

### Status Management Logic
- Account statuses are configurable
- Default status assigned during user creation
- Status changes logged and auditable
- Status affects user access and display</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/ai/project_knowledge/03-BUSINESS-LOGIC.md
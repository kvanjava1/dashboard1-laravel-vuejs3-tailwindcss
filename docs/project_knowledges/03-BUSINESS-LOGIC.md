# 03-BUSINESS-LOGIC.md

## Core Business Workflows

### User Authentication & Authorization

#### Login Process
1. **Client Request**: User submits email/password
2. **Rate Limiting**: 5 attempts per 15 minutes
3. **Validation**: Email format, password presence
4. **Authentication**: Check credentials against User model
5. **Token Generation**: Create Sanctum personal access token
6. **Response**: Return token, user data, permissions

#### User Registration (Admin Only)
1. **Permission Check**: `user_management.add` permission required
2. **Validation**: Email uniqueness, password strength
3. **User Creation**: Create User model with hashed password
4. **Role Assignment**: Assign default or specified roles
5. **Profile Setup**: Handle profile image upload if provided
6. **Audit Logging**: Log creation with `created_by` field

#### User Management
1. **List Users**: Paginated, filtered by role/status/search
2. **View User**: Retrieve full user details with roles
3. **Update User**: Modify profile, roles, status
4. **Delete User**: Soft delete with restrictions on super admin
5. **Bulk Operations**: Not implemented (potential enhancement)

### Role-Based Access Control

#### Permission System
- **Granular Permissions**: Feature-specific actions
- **Role Assignment**: Users can have multiple roles
- **Permission Groups**:
  - `user_management.*`: CRUD operations on users
  - `role_management.*`: CRUD operations on roles
  - `profile.*`: User profile management
  - `settings.*`: System configuration

#### Super Admin Protections
The system implements configurable protection for critical accounts and roles through the `ProtectionService` and `config/protected_entities.php`:

- **Cannot delete super admin users**: Configured in `protected_accounts['super@admin.com']['protect_deletion']`
- **Cannot modify super admin roles**: Configured in `protected_roles['super_admin']['protect_modification']`
- **Super admin has all permissions automatically**: Granted via `RolePermissionSeeder`

**Protection Logic**:
```php
// Check account protection
if ($protectionService->isAccountProtectedFromDeletion($user)) {
    $protectionService->throwProtectionException('Cannot delete protected account');
}

// Check role protection
if ($protectionService->isRoleProtectedFromModification($role)) {
    $protectionService->throwProtectionException('Cannot modify protected role');
}
```

### User Profile Management

#### Profile Updates
1. **Validation**: Input sanitization and validation
2. **Image Upload**: Store in `storage/app/public/profiles/`
3. **Data Update**: Update user fields
4. **Audit Trail**: Track who made changes

#### Forum Profile Features
- **Activity Tracking**: Post count, topic count, last activity
- **Moderation**: Ban status, ban reasons, ban duration
- **Preferences**: Timezone, notification settings

### Administrative Oversight

#### User Monitoring
- **Status Management**: Active/Inactive/Pending states
- **Activity Logging**: Track user actions
- **Search & Filter**: Find users by various criteria
- **Bulk Status Updates**: Change multiple users' status

#### System Administration
- **Role Management**: Create/edit/delete roles
- **Permission Assignment**: Assign permissions to roles
- **User-Role Assignment**: Assign roles to users

## Approval/Review Systems

### User Account Approval
- **Status Workflow**:
  - `pending` → `active` (admin approval)
  - `active` ↔ `inactive` (admin control)
  - `banned` (moderation action)

### Content Moderation (Framework)
- **Ban System**: Temporary or permanent bans
- **Reason Tracking**: Store ban reasons
- **Expiration Handling**: Automatic unban on expiry

## Calculation Logic and Algorithms

### Permission Resolution
- **Role-Based**: Check if user has required permission via roles
- **Direct Assignment**: Check user-specific permissions
- **Hierarchical**: Super admin bypasses all checks

### Search and Filtering
- **User Search**: Name and email LIKE queries
- **Date Range**: Created date filtering
- **Role Filtering**: Join with role_user pivot table

## Validation Rules and Business Constraints

### User Validation Rules
```php
// StoreUserRequest validation
'name' => 'required|string|max:255',
'email' => 'required|email|unique:users,email',
'password' => 'required|string|min:8',
'phone' => 'nullable|string|max:20',
'status' => 'required|in:active,inactive,pending',
'roles' => 'array',
'roles.*' => 'exists:roles,id'
```

### Business Constraints
- **Email Uniqueness**: No duplicate emails across active users
- **Super Admin Protection**: Cannot delete/modify super admin
- **Self-Deletion Prevention**: Users cannot delete own accounts
- **Role Requirements**: Certain roles require specific permissions

### Forum Constraints
- **Username Uniqueness**: Unique across all users
- **Age Verification**: Date of birth validation
- **Ban Logic**: Cannot login when banned, auto-unban on expiry

## Integration Points

### External Systems (Framework)
- **Email Service**: Laravel Mail (configured but not implemented)
- **File Storage**: Local filesystem with public access
- **Cache System**: Redis/file cache for performance
- **Queue System**: Database queues for background jobs

### API Integrations
- **Sanctum Tokens**: API authentication
- **CORS**: Cross-origin resource sharing
- **Rate Limiting**: Throttle middleware on auth endpoints

## Error Handling and Edge Cases

### Authentication Errors
- **Invalid Credentials**: 401 with generic message
- **Rate Limited**: 429 Too Many Requests
- **Banned User**: 401 Authentication failed

### Authorization Errors
- **Missing Permissions**: 403 Forbidden
- **Role Conflicts**: 403 with specific messages

### Data Validation Errors
- **Validation Failures**: 422 with field-specific errors
- **Unique Constraint Violations**: Handled by validation rules

### System Errors
- **Database Connection**: 500 Internal Server Error
- **File Upload Failures**: Handled with try-catch
- **External Service Failures**: Logged and user-friendly messages
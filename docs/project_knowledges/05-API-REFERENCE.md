# 05-API-REFERENCE.md

## API Reference Documentation

### API Overview

The application provides a **RESTful JSON API** built with Laravel, using **Laravel Sanctum** for authentication. The API follows REST conventions with consistent response formats and comprehensive error handling.

**Base URL**: `/api/v1`
**Authentication**: Bearer Token (Laravel Sanctum)
**Content-Type**: `application/json`
**Rate Limiting**: Applied to authentication endpoints

### Authentication Endpoints

#### POST `/api/v1/login`
Authenticate user and receive access token.

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Success Response (200)**:
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+1234567890",
    "status": "active",
    "profile_image": "http://example.com/storage/avatar/2024/01/01/abc123.webp",
    "role": "administrator",
    "role_display_name": "Administrator",
    "permissions": [
      "user_management.view",
      "user_management.add",
      "user_management.edit",
      "user_management.delete",
      "user_management.ban",
      "user_management.unban"
    ],
    "is_banned": false,
    "is_active": true,
    "protection": {
      "can_delete": true,
      "can_edit": true,
      "can_ban": true,
      "can_change_role": true,
      "reason": null
    },
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "joined_date": "Jan 1, 2024"
  },
  "token": "1|abc123def456..."
}
```

**Error Responses**:
- `401`: Invalid credentials
- `429`: Too many requests (rate limited)

#### POST `/api/v1/logout`
Revoke current access token.

**Headers**:
```
Authorization: Bearer {token}
```

**Success Response (200)**:
```json
{
  "message": "Logged out successfully"
}
```

#### GET `/api/v1/me`
Get current authenticated user information.

**Headers**:
```
Authorization: Bearer {token}
```

**Success Response (200)**:
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    // ... same as login response
  }
}
```

### User Management Endpoints

#### GET `/api/v1/users`
List users with filtering, sorting, and pagination.

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 15)
- `search` (string): Search in name and email
- `name` (string): Filter by name
- `email` (string): Filter by email
- `phone` (string): Filter by phone
- `role` (string): Filter by role name
- `status` (string): Filter by status (active, inactive, banned)
- `is_banned` (boolean): Filter by ban status
- `date_from` (date): Filter users created after date
- `date_to` (date): Filter users created before date
- `sort_by` (string): Sort field (name, email, created_at, updated_at)
- `sort_order` (string): Sort direction (asc, desc)

**Success Response (200)**:
```json
{
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "status": "active",
      "profile_image": "http://example.com/storage/avatar/2024/01/01/abc123.webp",
      "role": "administrator",
      "role_display_name": "Administrator",
      "permissions": ["user_management.view"],
      "is_banned": false,
      "is_active": true,
      "protection": {
        "can_delete": true,
        "can_edit": true,
        "can_ban": true,
        "can_change_role": true,
        "reason": null
      },
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "joined_date": "Jan 1, 2024"
    }
  ],
  "meta": {
    "total": 100,
    "total_pages": 7,
    "current_page": 1,
    "per_page": 15,
    "from": 1,
    "to": 15
  },
  "filters": {
    "available_roles": [
      {
        "id": 1,
        "name": "administrator",
        "display_name": "Administrator"
      }
    ],
    "status_options": [
      {
        "value": "active",
        "label": "Active"
      },
      {
        "value": "inactive",
        "label": "Inactive"
      }
    ]
  }
}
```

#### POST `/api/v1/users`
Create a new user.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Required Permissions**: `user_management.add`

**Request Body** (Form Data):
- `name` (string, required): Full name
- `email` (string, required): Email address
- `password` (string, required): Password (min 8 characters)
- `phone` (string, optional): Phone number
- `role` (string, required): Role name
- `profile_image` (file, optional): Profile image (JPEG, PNG, max 2MB)

**Success Response (201)**:
```json
{
  "message": "User created successfully",
  "user": {
    "id": 2,
    "name": "Jane Smith",
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "phone": "+1234567890",
    "profile_image": null,
    "role": "editor",
    "is_banned": false,
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "restored": false
  }
}
```

**Error Responses**:
- `403`: Insufficient permissions
- `422`: Validation errors
- `409`: Email already exists

#### GET `/api/v1/users/{id}`
Get specific user details.

**Headers**:
```
Authorization: Bearer {token}
```

**Required Permissions**: `user_management.view`

**Success Response (200)**:
```json
{
  "message": "User retrieved successfully",
  "user": {
    // Same structure as user list item
  }
}
```

#### PUT `/api/v1/users/{id}`
Update an existing user.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Required Permissions**: `user_management.edit`

**Request Body** (Form Data):
- `name` (string, optional): Full name
- `first_name` (string, optional): First name (combined with last_name)
- `last_name` (string, optional): Last name (combined with first_name)
- `email` (string, optional): Email address
- `phone` (string, optional): Phone number
- `password` (string, optional): New password
- `is_active` (boolean, optional): Account status
- `role` (string, optional): New role name
- `profile_image` (file, optional): New profile image

**Success Response (200)**:
```json
{
  "message": "User updated successfully",
  "user": {
    // Updated user data
  }
}
```

#### DELETE `/api/v1/users/{id}`
Soft delete a user.

**Headers**:
```
Authorization: Bearer {token}
```

**Required Permissions**: `user_management.delete`

**Success Response (200)**:
```json
{
  "message": "User deleted successfully"
}
```

### User Ban Management Endpoints

#### POST `/api/v1/users/{id}/ban`
Ban a user account.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Required Permissions**: `user_management.ban`

**Request Body**:
```json
{
  "reason": "Violation of terms of service",
  "is_forever": true,
  "banned_until": null
}
```

**Success Response (200)**:
```json
{
  "message": "User banned successfully",
  "data": {
    // Updated user data with is_banned: true
  }
}
```

#### POST `/api/v1/users/{id}/unban`
Unban a user account.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Required Permissions**: `user_management.unban`

**Request Body**:
```json
{
  "reason": "Account reinstated after appeal"
}
```

**Success Response (200)**:
```json
{
  "message": "User unbanned successfully",
  "data": {
    // Updated user data with is_banned: false
  }
}
```

#### GET `/api/v1/users/{id}/ban-history`
Get user's ban/unban history.

**Headers**:
```
Authorization: Bearer {token}
```

**Required Permissions**: `user_management.view`

**Success Response (200)**:
```json
{
  "message": "User ban history retrieved successfully",
  "data": [
    {
      "id": 1,
      "action": "ban",
      "reason": "Violation of terms",
      "banned_until": null,
      "is_forever": true,
      "performed_by": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "action": "unban",
      "reason": "Appeal approved",
      "banned_until": null,
      "is_forever": false,
      "performed_by": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "created_at": "2024-01-02T00:00:00.000000Z"
    }
  ]
}
```

### Role Management Endpoints

#### GET `/api/v1/roles/options`
Get available roles for dropdowns.

**Headers**:
```
Authorization: Bearer {token}
```

**Required Permissions**: `role_management.view`

**Success Response (200)**:
```json
{
  "message": "Roles retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "super_admin",
      "display_name": "Super Administrator"
    },
    {
      "id": 2,
      "name": "administrator",
      "display_name": "Administrator"
    }
  ]
}
```

#### GET `/api/v1/roles`
List all roles with permissions.

**Headers**:
```
Authorization: Bearer {token}
```

**Required Permissions**: `role_management.view`

**Success Response (200)**:
```json
{
  "message": "Roles retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "administrator",
      "display_name": "Administrator",
      "permissions": [
        {
          "id": 1,
          "name": "user_management.view",
          "display_name": "View Users"
        }
      ],
      "user_count": 5,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

#### POST `/api/v1/roles`
Create a new role.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Required Permissions**: `role_management.add`

**Request Body**:
```json
{
  "name": "editor",
  "permissions": ["user_management.view", "user_management.edit"]
}
```

#### GET `/api/v1/roles/{id}`
Get specific role details.

#### PUT `/api/v1/roles/{id}`
Update an existing role.

#### DELETE `/api/v1/roles/{id}`
Delete a role.

### Permission Endpoints

#### GET `/api/v1/permissions`
List all permissions.

**Headers**:
```
Authorization: Bearer {token}
```

**Required Permissions**: `role_management.view`

**Success Response (200)**:
```json
{
  "message": "Permissions retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "user_management.view",
      "display_name": "View Users"
    }
  ]
}
```

#### GET `/api/v1/permissions/grouped`
Get permissions grouped by category.

**Success Response (200)**:
```json
{
  "message": "Grouped permissions retrieved successfully",
  "data": {
    "User Management": [
      {
        "id": 1,
        "name": "user_management.view",
        "display_name": "View Users"
      },
      {
        "id": 2,
        "name": "user_management.add",
        "display_name": "Add Users"
      }
    ],
    "Role Management": [
      // ... role permissions
    ]
  }
}
```

### Error Response Formats

#### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

#### Authentication Error (401)
```json
{
  "message": "Unauthenticated."
}
```

#### Permission Denied (403)
```json
{
  "message": "Insufficient permissions",
  "required_permission": "user_management.delete"
}
```

#### Resource Not Found (404)
```json
{
  "message": "User not found"
}
```

#### Server Error (500)
```json
{
  "message": "Internal server error",
  "error": "Detailed error message (only in debug mode)"
}
```

### Rate Limiting

- **Login endpoint**: 5 attempts per 15 minutes
- **Other endpoints**: No specific rate limiting (handled by web server)

### API Versioning

- Current version: `v1`
- Version specified in URL path: `/api/v1/`
- Future versions will use `/api/v2/`, etc.

### Content Types

- **Request**: `application/json` or `multipart/form-data` (for file uploads)
- **Response**: `application/json`
- **File Uploads**: Support JPEG, PNG, GIF images (max 2MB)

### Authentication Flow

1. **Login**: POST `/api/v1/login` with credentials
2. **Receive Token**: Store token in localStorage/sessionStorage
3. **Include Token**: Add `Authorization: Bearer {token}` header to all requests
4. **Token Refresh**: Tokens are long-lived; refresh on 401 responses
5. **Logout**: POST `/api/v1/logout` to revoke token

### Pagination

All list endpoints support pagination with the following parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Response includes pagination metadata in the `meta` object.

### Filtering and Sorting

List endpoints support various filters and sorting options as documented in the endpoint specifications. Filters are applied using query parameters, and sorting uses `sort_by` and `sort_order` parameters.

### File Upload Handling

Profile image uploads are handled via multipart form data. Images are automatically converted to WebP format and stored in a dated directory structure (`avatar/YYYY/MM/DD/filename.webp`).</content>
<parameter name="filePath">/home/itboms/Developments/php/apps/php8.2/laravel/dashboard1/docs/project_knowledges/05-API-REFERENCE.md
# 05-API-REFERENCE.md

## API Overview

The application provides a RESTful API with versioning. All endpoints are prefixed with `/api/v1/` and use JSON for request/response bodies. Authentication uses Laravel Sanctum tokens.

## Authentication Endpoints

### POST /api/v1/login
User authentication endpoint.

**Rate Limiting**: 5 requests per 15 minutes

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
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "roles": ["admin"],
    "permissions": ["user_management.view", "user_management.add"]
  }
}
```

**Error Responses**:
- `401`: Invalid credentials
- `429`: Too many requests

### POST /api/v1/logout
User logout endpoint.

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

### GET /api/v1/me
Get current authenticated user.

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
    "profile_image_url": "/storage/profiles/avatar.jpg",
    "roles": ["admin"],
    "permissions": ["user_management.view"]
  }
}
```

## User Management Endpoints

All user management endpoints require authentication and appropriate permissions.

### GET /api/v1/users
List users with pagination and filtering.

**Permissions Required**: `user_management.view`

**Query Parameters**:
- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 15)
- `search` (string): Search in name/email
- `role` (string): Filter by role name
- `status` (string): Filter by status (active/inactive/pending)
- `date_from` (date): Filter by creation date from
- `date_to` (date): Filter by creation date to
- `sort_by` (string): Sort field (default: created_at)
- `sort_order` (string): Sort order (asc/desc)

**Success Response (200)**:
```json
{
  "message": "Users retrieved successfully",
  "data": [...],
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
      {"id": 1, "name": "admin", "display_name": "Administrator"}
    ],
    "status_options": [
      {"value": "active", "label": "Active"},
      {"value": "inactive", "label": "Inactive"},
      {"value": "pending", "label": "Pending"}
    ]
  }
}
```

### POST /api/v1/users
Create a new user.

**Permissions Required**: `user_management.add`

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "phone": "+1234567890",
  "status": "active",
  "roles": [1, 2],
  "profile_image": "file" // Multipart form data
}
```

**Success Response (201)**:
```json
{
  "message": "User created successfully",
  "user": {...}
}
```

### GET /api/v1/users/{id}
Get specific user details.

**Permissions Required**: `user_management.view`

**Success Response (200)**:
```json
{
  "message": "User retrieved successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "status": "active",
    "roles": [...],
    "permissions": [...],
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

### PUT/PATCH /api/v1/users/{id}
Update user information.

**Permissions Required**: `user_management.edit`

**Request Body**: Same as create, all fields optional

**Success Response (200)**:
```json
{
  "message": "User updated successfully",
  "user": {...}
}
```

### DELETE /api/v1/users/{id}
Soft delete a user.

**Permissions Required**: `user_management.delete`

**Restrictions**:
- Cannot delete super admin users
- Cannot delete own account

**Success Response (200)**:
```json
{
  "message": "User deleted successfully"
}
```

## Role Management Endpoints

### GET /api/v1/roles/options
Get available roles for dropdowns.

**Permissions Required**: `role_management.view`

**Success Response (200)**:
```json
[
  {"id": 1, "name": "admin", "display_name": "Administrator"},
  {"id": 2, "name": "user", "display_name": "User"}
]
```

### GET /api/v1/roles
List all roles.

**Permissions Required**: `role_management.view`

### POST /api/v1/roles
Create a new role.

**Permissions Required**: `role_management.add`

### GET /api/v1/roles/{id}
Get role details.

**Permissions Required**: `role_management.view`

### PUT/PATCH /api/v1/roles/{id}
Update role.

**Permissions Required**: `role_management.edit`

### DELETE /api/v1/roles/{id}
Delete role.

**Permissions Required**: `role_management.delete`

## Permission Endpoints

### GET /api/v1/permissions
List all permissions.

**Permissions Required**: `permission.view`

### GET /api/v1/permissions/grouped
Get permissions grouped by feature.

**Permissions Required**: `permission.view`

**Response**:
```json
{
  "user_management": [
    "user_management.view",
    "user_management.add",
    "user_management.edit",
    "user_management.delete"
  ],
  "role_management": [
    "role_management.view",
    "role_management.add"
  ]
}
```

## Example Management Endpoints

CRUD endpoints for the example_management table.

### GET /api/v1/examples
List examples.

**Permissions Required**: `example_management.view`

### POST /api/v1/examples
Create example.

**Permissions Required**: `example_management.add`

### GET /api/v1/examples/{id}
Get example.

**Permissions Required**: `example_management.view`

### PUT/PATCH /api/v1/examples/{id}
Update example.

**Permissions Required**: `example_management.edit`

### DELETE /api/v1/examples/{id}
Delete example.

**Permissions Required**: `example_management.delete`

## Common Response Patterns

### Success Response
```json
{
  "message": "Operation successful",
  "data": {...} // or specific field name
}
```

### Error Response
```json
{
  "message": "Error description",
  "error": "Detailed error message"
}
```

### Validation Error Response
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

## Authentication Headers

All protected endpoints require:
```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
```

## Rate Limiting

- Authentication endpoints: 5 requests per 15 minutes
- Other endpoints: No specific rate limiting (configurable)

## Error Codes

- `200`: Success
- `201`: Created
- `401`: Unauthorized (invalid/missing token)
- `403`: Forbidden (insufficient permissions)
- `404`: Not Found
- `422`: Validation Error
- `429`: Too Many Requests
- `500`: Internal Server Error

## API Versioning

Current version: `v1`
- All endpoints prefixed with `/api/v1/`
- Version maintained in route definitions
- Breaking changes will increment version
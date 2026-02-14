# API Reference

## API Versioning & General Notes

- **Base URL**: `/api/v1/`
- **Authentication**: Bearer token (Sanctum) in `Authorization` header
  - Format: `Authorization: Bearer {token}`
- **Content-Type**: `application/json` for requests and responses
- **Rate Limiting**: Login (`/login`) is throttled to 5 attempts per 15 minutes

**Evidence**: [routes/api.php](routes/api.php)

---

## Authentication Endpoints

### POST /login

**Purpose**: Authenticate user and receive API token

**Public**: Yes (Rate limited: 5/15min)

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Success Response** (200 OK):
```json
{
  "message": "Login successful",
  "token": "1|abcdef...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "status": "active",
    "profile_image": "https://app.local/storage/profile_images/user1.jpg",
    "role": "admin",
    "permissions": [
      "user_management.view",
      "user_management.add",
      "user_management.edit",
      "user_management.delete",
      "gallery_management.view",
      "gallery_management.add",
      "gallery_management.edit",
      "gallery_management.delete"
    ]
  }
}
```

**Error Response** (401 Unauthorized):
```json
{
  "message": "Invalid email or password"
}
```

**Special Cases**:
- If `is_active = false`: "Your account is not active. Please contact administrator."
- If `is_banned = true`: "Your account has been banned. Please contact administrator."

**Evidence**: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php), [app/Services/AuthService.php](app/Services/AuthService.php)

---

### POST /logout

**Purpose**: Revoke current API token and end session

**Authentication**: Required (Bearer token)

**Request Body**: Empty

**Success Response** (200 OK):
```json
{
  "message": "Logged out successfully"
}
```

**Evidence**: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php)

---

### GET /me

**Purpose**: Retrieve current authenticated user's data

**Authentication**: Required

**Request Body**: Empty

**Success Response** (200 OK):
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "status": "active",
    "profile_image": "https://app.local/storage/profile_images/user1.jpg",
    "role": "admin",
    "permissions": ["user_management.view", ...]
  }
}
```

---

## User Management Endpoints

### GET /users

**Purpose**: List users with filtering, sorting, and pagination

**Authentication**: Required  
**Permission**: `user_management.view`

**Query Parameters**:
| Parameter | Type | Default | Example |
|-----------|------|---------|---------|
| page | int | 1 | `?page=2` |
| per_page | int | 15 | `?per_page=50` |
| search | string | - | `?search=john` |
| name | string | - | `?name=John` |
| email | string | - | `?email=john@example.com` |
| phone | string | - | `?phone=1234567890` |
| role | string | - | `?role=admin` |
| status | string | - | `?status=active` (active/inactive/banned) |
| is_banned | bool | - | `?is_banned=true` |
| date_from | date | - | `?date_from=2026-01-01` |
| date_to | date | - | `?date_to=2026-12-31` |
| sort_by | string | created_at | `?sort_by=name` (name/email/created_at/updated_at) |
| sort_order | string | desc | `?sort_order=asc` (asc/desc) |

**Success Response** (200 OK):
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
      "profile_image": "https://app.local/storage/profile_images/user1.jpg",
      "role": "admin",
      "permissions": [...]
    },
    ...
  ],
  "meta": {
    "total": 45,
    "total_pages": 3,
    "current_page": 1,
    "per_page": 15,
    "from": 1,
    "to": 15
  },
  "filters": {
    "available_roles": [
      { "id": 1, "name": "super_admin", "display_name": "Super Administrator" },
      { "id": 2, "name": "admin", "display_name": "Administrator" }
    ],
    "status_options": [
      { "value": "active", "label": "Active" },
      { "value": "inactive", "label": "Inactive" }
    ]
  }
}
```

**Evidence**: [app/Http/Controllers/Api/Managements/UserController.php](app/Http/Controllers/Api/Managements/UserController.php), [routes/api.php](routes/api.php) line 23

---

### POST /users

**Purpose**: Create a new user account

**Authentication**: Required  
**Permission**: `user_management.add`

**Request Body** (form-data for file upload):
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "SecurePass123!",
  "phone": "+1987654321",
  "profile_image": <file>,
  "roles": [2, 3]
}
```

**Success Response** (201 Created):
```json
{
  "message": "User created successfully",
  "user": {
    "id": 42,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1987654321",
    "status": "active",
    "profile_image": "https://app.local/storage/profile_images/user42.jpg",
    "role": "admin",
    "permissions": [...]
  }
}
```

**Special Response** (if restoring soft-deleted user):
```json
{
  "message": "User account restored and updated successfully",
  "user": { ... }
}
```

**Validation Error** (422 Unprocessable Entity):
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

**Evidence**: [app/Http/Controllers/Api/Managements/UserController.php](app/Http/Controllers/Api/Managements/UserController.php) store()

---

### GET /users/{id}

**Purpose**: Retrieve specific user details

**Authentication**: Required  
**Permission**: `user_management.view`

**Path Parameters**:
- `id` (int, required): User ID

**Success Response** (200 OK):
```json
{
  "message": "User retrieved successfully",
  "user": { ... (full user object) ... }
}
```

**Not Found** (404 Not Found):
```json
{
  "message": "Failed to retrieve user",
  "error": "User not found or has been deleted"
}
```

---

### PUT /users/{id} or PATCH /users/{id}

**Purpose**: Update existing user

**Authentication**: Required  
**Permission**: `user_management.edit`

**Request Body**:
```json
{
  "name": "Jane Smith Updated",
  "email": "jane.updated@example.com",
  "phone": "+1111111111",
  "password": "NewSecurePass456!",
  "profile_image": <file>,
  "roles": [2]
}
```

**Success Response** (200 OK):
```json
{
  "message": "User updated successfully",
  "user": { ... (updated user object) ... }
}
```

**Protected Account Error** (403 Forbidden):
```json
{
  "message": "This account is protected and cannot be modified"
}
```

**Evidence**: Protected checks via [app/Services/ProtectionService.php](app/Services/ProtectionService.php)

---

### DELETE /users/{id}

**Purpose**: Soft delete (deactivate) a user

**Authentication**: Required  
**Permission**: `user_management.delete`

**Request Body**: Empty

**Success Response** (200 OK):
```json
{
  "message": "User deleted successfully"
}
```

**Protected Account Error** (403 Forbidden):
```json
{
  "message": "Cannot delete protected account"
}
```

---

### POST /users/{id}/ban

**Purpose**: Ban a user account (set is_banned=true and record reason)

**Authentication**: Required  
**Permission**: `user_management.ban`

**Request Body**:
```json
{
  "reason": "Inappropriate behavior",
  "banned_until": "2026-03-14T00:00:00Z",
  "is_forever": false
}
```

**Success Response** (200 OK):
```json
{
  "message": "User banned successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "status": "banned",
    ...
  },
  "ban_history": {
    "id": 5,
    "action": "ban",
    "reason": "Inappropriate behavior",
    "banned_until": "2026-03-14T00:00:00Z",
    "is_forever": false,
    "performed_by": 2,
    "created_at": "2026-02-14T10:30:00Z"
  }
}
```

**Protected Account Error** (403 Forbidden):
```json
{
  "message": "Cannot ban protected account"
}
```

---

### POST /users/{id}/unban

**Purpose**: Unban a user account (set is_banned=false)

**Authentication**: Required  
**Permission**: `user_management.unban`

**Request Body**:
```json
{
  "reason": "Appeal approved"
}
```

**Success Response** (200 OK):
```json
{
  "message": "User unbanned successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "status": "active",
    ...
  }
}
```

---

### GET /users/{id}/ban-history

**Purpose**: Retrieve ban/unban history for a user

**Authentication**: Required  
**Permission**: `user_management.view`

**Success Response** (200 OK):
```json
{
  "message": "Ban history retrieved successfully",
  "data": [
    {
      "id": 5,
      "user_id": 1,
      "action": "ban",
      "reason": "Inappropriate behavior",
      "banned_until": "2026-03-14T00:00:00Z",
      "is_forever": false,
      "performed_by": 2,
      "performed_by_name": "Admin User",
      "created_at": "2026-02-14T10:30:00Z"
    },
    {
      "id": 6,
      "user_id": 1,
      "action": "unban",
      "reason": "Appeal approved",
      "banned_until": null,
      "is_forever": false,
      "performed_by": 3,
      "performed_by_name": "Moderator User",
      "created_at": "2026-02-21T14:45:00Z"
    }
  ]
}
```

---

### POST /users/clear-cache

**Purpose**: Manually invalidate user list cache

**Authentication**: Required  
**Permission**: `user_management.edit`

**Request Body**: Empty

**Success Response** (200 OK):
```json
{
  "message": "User cache cleared successfully"
}
```

**Evidence**: Manual cache invalidation endpoint

---

## Role Management Endpoints

### GET /roles/options

**Purpose**: Get simplified role list for dropdowns

**Authentication**: Required  
**Permission**: `role_management.view`

**Success Response** (200 OK):
```json
{
  "message": "Roles retrieved successfully",
  "data": [
    { "id": 1, "name": "super_admin", "display_name": "Super Administrator" },
    { "id": 2, "name": "admin", "display_name": "Administrator" }
  ]
}
```

---

### GET /roles

**Purpose**: List all roles with permissions and user counts

**Authentication**: Required  
**Permission**: `role_management.view`

**Query Parameters**:
| Parameter | Type | Default | Notes |
|-----------|------|---------|-------|
| page | int | 1 | Pagination |
| per_page | int | 15 | Items per page |
| search | string | - | Search by role name |
| sort_by | string | created_at | Sort field |
| sort_order | string | desc | asc/desc |

**Success Response** (200 OK):
```json
{
  "message": "Roles retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "super_admin",
      "display_name": "Super Administrator",
      "permissions": [
        {
          "id": 1,
          "name": "user_management.view",
          "label": "View Users",
          ...
        }
      ],
      "users_count": 1,
      "guard_name": "web",
      "created_at": "2026-01-15T00:00:00Z",
      "updated_at": "2026-01-15T00:00:00Z"
    }
  ],
  "meta": { ... }
}
```

---

### POST /roles

**Purpose**: Create new role with permissions

**Authentication**: Required  
**Permission**: `role_management.add`

**Request Body**:
```json
{
  "name": "editor",
  "display_name": "Content Editor",
  "permissions": [12, 13, 14]
}
```

**Success Response** (201 Created):
```json
{
  "message": "Role created successfully",
  "role": { ... (full role with permissions) ... }
}
```

---

### PUT /roles/{id} or PATCH /roles/{id}

**Purpose**: Update role and its permissions

**Authentication**: Required  
**Permission**: `role_management.edit`

**Request Body**:
```json
{
  "name": "editor",
  "display_name": "Content Editor Updated",
  "permissions": [12, 13, 14, 15]
}
```

**Success Response** (200 OK):
```json
{
  "message": "Role updated successfully",
  "role": { ... }
}
```

---

### DELETE /roles/{id}

**Purpose**: Delete a role

**Authentication**: Required  
**Permission**: `role_management.delete`

**Protected Role Error** (403 Forbidden):
```json
{
  "message": "Cannot delete protected role: super_admin"
}
```

---

### POST /roles/clear-cache

**Purpose**: Manually invalidate role cache

**Authentication**: Required  
**Permission**: `role_management.edit`

---

## Permission Endpoints

### GET /permissions

**Purpose**: List all permissions

**Authentication**: Required  
**Permission**: `role_management.view`

**Success Response** (200 OK):
```json
{
  "message": "Permissions retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "user_management.view",
      "label": "View Users",
      "category": "user_management",
      "guard_name": "web",
      "created_at": "2026-01-15T00:00:00Z"
    },
    {
      "id": 2,
      "name": "user_management.add",
      "label": "Create Users",
      "category": "user_management",
      "guard_name": "web",
      "created_at": "2026-01-15T00:00:00Z"
    },
    ...
  ]
}
```

---

### GET /permissions/grouped

**Purpose**: List permissions grouped by category

**Authentication**: Required  
**Permission**: `role_management.view`

**Success Response** (200 OK):
```json
{
  "message": "Permissions retrieved and grouped successfully",
  "data": {
    "user_management": [
      { "id": 1, "name": "user_management.view", "label": "View Users", ... },
      { "id": 2, "name": "user_management.add", "label": "Create Users", ... }
    ],
    "gallery_management": [
      { "id": 5, "name": "gallery_management.view", "label": "View Galleries", ... }
    ],
    "role_management": [ ... ]
  }
}
```

---

## Category Management Endpoints

### GET /categories/options

**Purpose**: Get simplified category list for dropdowns

**Authentication**: Required  
**Permission**: `category_management.view`

**Success Response** (200 OK):
```json
{
  "message": "Categories retrieved successfully",
  "data": [
    { "id": 1, "name": "Photography", "slug": "photography" },
    { "id": 2, "name": "Landscape", "slug": "landscape" },
    { "id": 3, "name": "Portrait", "slug": "portrait" }
  ]
}
```

---

### GET /categories

**Purpose**: List all categories (flat structure; frontend converts to tree)

**Authentication**: Required  
**Permission**: `category_management.view`

**Query Parameters**:
| Parameter | Type | Example |
|-----------|------|---------|
| search | string | `?search=photo` |
| type | string | `?type=gallery` |
| status | string | `?status=active` |
| slug | string | `?slug=photography` |

**Success Response** (200 OK):
```json
{
  "message": "Categories retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Photography",
      "slug": "photography",
      "description": "Photography categories",
      "parent_id": null,
      "is_active": true,
      "type": {
        "id": 1,
        "name": "Gallery",
        "slug": "gallery"
      },
      "created_at": "2026-02-07T00:00:00Z"
    },
    {
      "id": 2,
      "name": "Landscape",
      "slug": "landscape",
      "description": null,
      "parent_id": 1,
      "is_active": true,
      "type": { ... },
      "created_at": "2026-02-07T00:00:00Z"
    }
  ]
}
```

---

### POST /categories

**Purpose**: Create new category

**Authentication**: Required  
**Permission**: `category_management.add`

**Request Body**:
```json
{
  "type": "gallery",
  "name": "Street Photography",
  "description": "Urban and street photography",
  "parent_id": 1,
  "is_active": true
}
```

**Success Response** (201 Created):
```json
{
  "message": "Category created successfully",
  "category": { ... (full category object) ... }
}
```

---

### GET /categories/{id}

**Purpose**: Retrieve specific category

**Authentication**: Required  
**Permission**: `category_management.view`

---

### PUT /categories/{id}

**Purpose**: Update category

**Authentication**: Required  
**Permission**: `category_management.edit`

---

### DELETE /categories/{id}

**Purpose**: Delete category

**Authentication**: Required  
**Permission**: `category_management.delete`

---

### POST /categories/clear-cache

**Purpose**: Manually invalidate category cache

**Authentication**: Required  
**Permission**: `category_management.edit`

---

## Gallery Management Endpoints

### GET /galleries

**Purpose**: List galleries with filtering and pagination

**Authentication**: Required  
**Permission**: `gallery_management.view`

**Query Parameters**:
| Parameter | Type | Example |
|-----------|------|---------|
| page | int | `?page=1` |
| per_page | int | `?per_page=20` |
| search | string | `?search=summer` |
| category_id | int | `?category_id=5` |
| status | string | `?status=active` |
| visibility | string | `?visibility=public` |

**Success Response** (200 OK):
```json
{
  "message": "Galleries retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Summer 2025",
      "slug": "summer-2025",
      "description": "Beautiful summer photos",
      "category": { "id": 5, "name": "Travel" },
      "status": "active",
      "visibility": "public",
      "item_count": 42,
      "cover": {
        "id": 15,
        "filename": "uploads/gallery/summer-2025/covers/1200x900/2026/02/14/abc123.webp",
        "extension": "webp",
        "alt_text": "Beach sunset"
      },
      "tags": [
        { "id": 1, "name": "summer", "slug": "summer" },
        { "id": 2, "name": "travel", "slug": "travel" }
      ],
      "created_at": "2026-02-08T00:00:00Z"
    }
  ],
  "meta": { "total": 15, "current_page": 1 }
}
```

---

### POST /galleries

**Purpose**: Create new gallery with optional cover image

**Authentication**: Required  
**Permission**: `gallery_management.add`

**Request Body** (multipart/form-data):
```
title: "Summer Beach Photos"
description: "Collection of beach photos"
category_id: "5"
visibility: "public"
status: "active"
file: <image file>
crop: {"x": 100, "y": 50, "width": 400, "height": 300}
tags: ["beach", "summer", "vacation"]
```

**Success Response** (201 Created):
```json
{
  "message": "Gallery created successfully",
  "gallery": {
    "id": 42,
    "title": "Summer Beach Photos",
    "slug": "summer-beach-photos",
    "description": "Collection of beach photos",
    "category_id": 5,
    "is_active": true,
    "is_public": true,
    "item_count": 1,
    "cover": {
      "id": 128,
      "filename": "uploads/gallery/summer-beach-photos/covers/1200x900/2026/02/14/unique123.webp",
      "extension": "webp",
      "is_cover": true
    },
    "tags": [
      { "id": 10, "name": "beach", "slug": "beach" },
      { "id": 11, "name": "summer", "slug": "summer" }
    ],
    "created_at": "2026-02-14T10:30:00Z"
  }
}
```

**Evidence**: [app/Services/GalleryService.php](app/Services/GalleryService.php), [routes/api.php](routes/api.php)

---

### GET /galleries/{id}

**Purpose**: Retrieve gallery with all media and details

**Authentication**: Required  
**Permission**: `gallery_management.view`

**Success Response** (200 OK):
```json
{
  "message": "Gallery retrieved successfully",
  "gallery": {
    "id": 1,
    "title": "Summer 2025",
    "slug": "summer-2025",
    ...additional fields...,
    "media": [
      {
        "id": 101,
        "filename": "uploads/gallery/summer-2025/1200x900/2026/02/14/photo1.webp",
        "extension": "webp",
        "size": 245000,
        "alt_text": "Beach sunset",
        "sort_order": 1,
        "is_cover": false,
        "url": "https://app.local/storage/uploads/gallery/summer-2025/1200x900/2026/02/14/photo1.webp"
      }
    ]
  }
}
```

---

### PUT /galleries/{id}

**Purpose**: Update gallery metadata and/or cover

**Authentication**: Required  
**Permission**: `gallery_management.edit`

**Request Body**:
```
title: "Updated Title"
description: "Updated description"
category_id: "6"
visibility: "private"
status: "inactive"
file: <new cover image> (optional)
crop: {...} (optional)
tags: ["new-tag"]
```

**Success Response** (200 OK):
```json
{
  "message": "Gallery updated successfully",
  "gallery": { ... }
}
```

---

### DELETE /galleries/{id}

**Purpose**: Soft delete gallery

**Authentication**: Required  
**Permission**: `gallery_management.delete`

**Success Response** (200 OK):
```json
{
  "message": "Gallery deleted successfully"
}
```

---

## Tag Endpoints

### GET /tags/options

**Purpose**: Get all tags for autocomplete

**Authentication**: Required

**Success Response** (200 OK):
```json
{
  "message": "Tags retrieved successfully",
  "data": [
    { "id": 1, "name": "beach", "slug": "beach" },
    { "id": 2, "name": "summer", "slug": "summer" },
    { "id": 3, "name": "vacation", "slug": "vacation" }
  ]
}
```

---

## Error Response Format

**Standard Error Response**:
```json
{
  "message": "Error description",
  "error": "Detailed error message or exception"
}
```

**HTTP Status Codes Used**:
| Code | Meaning | Example |
|------|---------|---------|
| 200 | Success | GET, successful PUT |
| 201 | Created | POST creates resource |
| 400 | Bad Request | Malformed JSON |
| 401 | Unauthenticated | Missing/invalid token |
| 403 | Forbidden | Insufficient permissions / Protected account |
| 404 | Not Found | Resource ID doesn't exist |
| 422 | Validation Error | Invalid input data |
| 429 | Too Many Requests | Rate limit exceeded (login) |
| 500 | Server Error | Unhandled exception |

---

## Middleware & Validation

**Active Middleware on all protected routes**:
1. `auth:sanctum` - Validates Bearer token
2. `check.user.status` - Verifies is_active & is_banned flags
3. `permission:{permission}` - Checks Spatie permission

**Evidence**: [routes/api.php](routes/api.php), [app/Http/Middleware/CheckUserStatus.php](app/Http/Middleware/CheckUserStatus.php)

---

## Pagination Notes

**Default**: 15 items per page
**Maximum** (depends on implementation, likely 100)
**Fields in meta response**:
- `total` - Total items across all pages
- `total_pages` - Calculated from total / per_page
- `current_page` - Current page number
- `per_page` - Items per page
- `from` - Starting item number on this page
- `to` - Ending item number on this page

---

## Cache Invalidation

**Manual Clear Endpoints**:
- `POST /users/clear-cache` - Clear user list cache
- `POST /roles/clear-cache` - Clear roles cache
- `POST /categories/clear-cache` - Clear categories cache

**Automatic Invalidation**:
- When any user is created/updated/deleted → user cache invalidated
- When any role is created/updated/deleted → role cache invalidated
- When any category is created/updated/deleted → category cache invalidated

**Version-Based Keys**:
- Cache keys include filter parameters
- Different filters = different cache entries
- One cache clear invalidates all variations for entity

---

## UNKNOWN / NOT FOUND IN CODE
- API webhooks or events
- GraphQL endpoint (only REST API visible)
- File upload size limits
- Batch endpoints (bulk operations)
- Export endpoints (CSV/JSON export)
- API response time limits
- Concurrent request limits (beyond rate limiting)
- CORS headers configuration

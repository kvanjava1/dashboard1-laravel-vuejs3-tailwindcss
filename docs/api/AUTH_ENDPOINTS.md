# Backend API Documentation

## Authentication Endpoints

### Base URL
```
http://localhost/api/v1
```

### 1. Login
**Endpoint:** `POST /login`
**Rate Limited:** 5 attempts per 15 minutes

**Request:**
```json
{
  "email": "super@admin.com",
  "password": "12345678"
}
```

**Success Response (200):**
```json
{
  "message": "Login successful",
  "token": "1|abc123xyz...",
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "super@admin.com",
    "role": "super_admin",
    "permissions": [
      "dashboard.view",
      "user_management.add",
      "user_management.edit",
      "user_management.delete",
      "user_management.search",
      "report.view",
      "report.export"
    ]
  }
}
```

**Error Response (401):**
```json
{
  "message": "Invalid email or password"
}
```

**Rate Limit Error (429):**
```json
{
  "message": "Too Many Requests"
}
```

---

### 2. Get Current User
**Endpoint:** `GET /me`
**Auth Required:** Yes (Bearer Token)
**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "super@admin.com",
    "role": "super_admin",
    "permissions": [
      "dashboard.view",
      "user_management.add",
      "user_management.edit",
      "user_management.delete",
      "user_management.search",
      "report.view",
      "report.export"
    ]
  }
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated"
}
```

---

### 3. Logout
**Endpoint:** `POST /logout`
**Auth Required:** Yes (Bearer Token)
**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated"
}
```

---

## Key Features

✅ **Rate Limiting** - Login endpoint limited to 5 attempts per 15 minutes
✅ **JWT Tokens** - Sanctum token-based authentication
✅ **Role-Based Access** - Super admin has all permissions
✅ **Permissions Management** - Returns all user permissions with login
✅ **Device Tracking** - Token created per device/user agent
✅ **Logging** - All login attempts are logged

## Test Credentials

| Email | Password | Role |
|-------|----------|------|
| super@admin.com | 12345678 | super_admin |

## Architecture

- **AuthController** - Handles login/logout endpoints
- **AuthService** - Business logic for authentication
- **LoginRequest** - Validates email and password
- **User Model** - Enhanced with HasRoles trait for Spatie
- **Sanctum** - Token-based authentication

# Authentication & Authorization Architecture

## Complete System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Vue.js 3 Frontend                        │
├─────────────────────────────────────────────────────────────┤
│  App.vue → Restores session on mount                        │
│  ↓                                                           │
│  Router Guards → Check auth & permissions                   │
│  ↓                                                           │
│  Login.vue → Calls authStore.login()                        │
│  ↓                                                           │
│  Dashboard.vue → Protected route                            │
│  ↓                                                           │
│  Pinia Auth Store → Manages state & API calls               │
│  ↓                                                           │
│  useApi Composable → Adds Authorization header              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│              Laravel Backend (API v1)                        │
├─────────────────────────────────────────────────────────────┤
│  POST /api/v1/login                                         │
│  ├─ Validates credentials                                   │
│  ├─ Creates Sanctum token                                   │
│  ├─ Returns user + permissions                              │
│  └─ Rate limited (5/15min)                                  │
│                                                              │
│  GET /api/v1/me (protected)                                 │
│  ├─ Returns current user data                               │
│  └─ Requires Bearer token                                   │
│                                                              │
│  POST /api/v1/logout (protected)                            │
│  ├─ Revokes token                                           │
│  └─ Requires Bearer token                                   │
│                                                              │
│  Database (Spatie Permission)                               │
│  ├─ Users table                                             │
│  ├─ Roles table (super_admin)                               │
│  ├─ Permissions table (7 permissions)                       │
│  └─ Role-Permission relationships                           │
└─────────────────────────────────────────────────────────────┘
```

## Frontend Flow

### 1. App Initialization (App.vue)
```typescript
onMounted(async () => {
  await authStore.restoreSession()  // Restore from localStorage
})
```

**What happens:**
- ✅ Loads token from `localStorage.auth_token`
- ✅ Loads user data from `localStorage.user`
- ✅ Verifies token is still valid via API
- ✅ Clears auth if token expired

### 2. Route Protection (Router Guards)
```typescript
beforeEach((to, from, next) => {
  // Check authentication
  // Check permissions
  // Check roles
  // Redirect if needed
})
```

**Guard Logic:**
```
Is route public?
  ├─ YES → Allow
  └─ NO → Check authentication
        ├─ Authenticated?
        │  ├─ YES → Check permissions → Allow/Redirect
        │  └─ NO → Redirect to login
```

### 3. Login Flow (Login.vue → AuthStore)
```
User enters email/password
    ↓
handleLogin() calls authStore.login()
    ↓
AuthService makes POST /api/v1/login
    ↓
Backend validates & returns token + user
    ↓
Store saves to localStorage
    ↓
Router redirects to dashboard
    ↓
Route guard checks permission 'dashboard.view'
    ↓
Dashboard loads
```

### 4. Protected API Requests (useApi Composable)
```typescript
const { post } = useApi()

// Token automatically added to header
await post('/api/v1/users', {name: 'John'})

// Headers:
// Authorization: Bearer token123...
// Content-Type: application/json
```

## Backend Flow

### 1. Login Endpoint
```
POST /api/v1/login
├─ LoginRequest validates input
├─ AuthController calls AuthService
├─ AuthService:
│  ├─ auth()->attempt() checks credentials
│  ├─ Creates Sanctum token: createToken()
│  ├─ Loads user with roles: user->load('roles')
│  ├─ Gets permissions: user->getAllPermissions()
│  └─ Returns token + user data
└─ Response: 200 with token & user
```

### 2. Protected Routes
```
GET /api/v1/me
├─ middleware 'auth:sanctum' checks Bearer token
├─ Sanctum validates token
├─ Loads user data with permissions
└─ Returns user info
```

**Sanctum Middleware Flow:**
```
Request with Bearer token
    ↓
Sanctum checks token in database
    ↓
Token valid?
    ├─ YES → auth()->user() available
    └─ NO → Return 401 Unauthorized
```

### 3. Database Structure
```
users table
├─ id, name, email, password, ...

roles table
├─ id, name ("super_admin")

permissions table
├─ id, name ("dashboard.view", "user_management.add", ...)

role_has_permissions (pivot)
├─ role_id, permission_id
└─ super_admin linked to ALL permissions

model_has_roles (pivot)
├─ model_id, role_type, role_id
└─ user_id 1 linked to super_admin role
```

## Data Flow: Login to Dashboard Access

### Step 1: User Submits Login Form
```vue
<form @submit="handleLogin">
  Input: email, password
  ↓
  handleLogin() calls authStore.login(email, password)
```

### Step 2: Auth Store Makes API Request
```typescript
// stores/auth.ts
login(email, password) {
  fetch('/api/v1/login', {
    method: 'POST',
    body: JSON.stringify({email, password})
  })
}
```

### Step 3: Backend Authenticates
```php
// AuthController
public function login(LoginRequest $request) {
  if (!auth()->attempt($validated)) {
    return response()->json(['message' => 'Invalid credentials'], 401)
  }
  
  $user = auth()->user()
  $token = $user->createToken('auth_token')->plainTextToken
  $permissions = $user->getAllPermissions()
  
  return response()->json([
    'token' => $token,
    'user' => [..., 'permissions' => $permissions]
  ])
}
```

### Step 4: Frontend Stores Data
```typescript
// stores/auth.ts
token.value = response.token
user.value = response.user
localStorage.setItem('auth_token', token)
localStorage.setItem('user', JSON.stringify(user))
```

### Step 5: Router Redirects
```typescript
// Login.vue
router.push({ name: 'dashboard.index' })
```

### Step 6: Route Guard Checks Permission
```typescript
// router/routes/index.ts
beforeEach((to, from, next) => {
  if (to.meta.requiredPermission) {
    if (authStore.hasPermission('dashboard.view')) {
      next()  // ✓ User has permission, allow
    } else {
      next({ name: 'dashboard.index' })  // Redirect
    }
  }
})
```

### Step 7: Dashboard Component Loads
```vue
<template>
  <div v-if="authStore.isAuthenticated">
    Dashboard content for {{ authStore.currentUser.name }}
  </div>
</template>
```

## Permission Checking in Components

### Check Single Permission
```vue
<button v-if="authStore.hasPermission('user_management.add')">
  Add User
</button>
```

### Check Any Permission
```vue
<div v-if="authStore.hasAnyPermission(['user_management.add', 'user_management.edit'])">
  Can modify users
</div>
```

### Check All Permissions
```vue
<div v-if="authStore.hasAllPermissions(['dashboard.view', 'user_management.search'])">
  Can access dashboard and search users
</div>
```

### Check Role
```vue
<div v-if="authStore.hasRole('super_admin')">
  Admin controls
</div>
```

## Error Handling

### Authentication Errors
```
Scenario: Invalid credentials
  ↓
Backend returns 401
  ↓
AuthService throws exception
  ↓
Login.vue catches & shows error
  ↓
Error message: "Invalid email or password"
```

### Session Errors
```
Scenario: Token expired
  ↓
API returns 401 on /api/v1/me request
  ↓
useApi composable clears auth
  ↓
Router redirects to /login
```

### Rate Limit Error
```
Scenario: 5+ failed login attempts in 15 minutes
  ↓
Backend returns 429
  ↓
Error message: "Too Many Requests"
```

## Security Features

### ✅ Token Storage
- Stored in localStorage
- Attached to all API requests via Bearer header
- Cleared on logout

### ✅ Session Persistence
- User session survives page refresh
- Restored via `/api/v1/me` API call
- Token validated server-side

### ✅ Automatic Logout
- 401 response triggers logout
- Token cleared from localStorage
- User redirected to login

### ✅ Rate Limiting
- Login endpoint: 5 attempts per 15 minutes
- Prevents brute force attacks

### ✅ CORS Support
- Sanctum handles CORS automatically
- Only localhost and same-origin allowed

### ✅ Permission-Based Access
- Every action checked against permissions
- UI hidden for unauthorized users
- Backend validates on API calls

## Files Summary

| File | Purpose |
|------|---------|
| `stores/auth.ts` | Pinia store for auth state |
| `composables/useApi.ts` | API helper with auth header |
| `router/routes/index.ts` | Router with guards |
| `router/routes/auth.ts` | Login route |
| `router/routes/dashboard.ts` | Protected dashboard route |
| `views/admin/auth/Login.vue` | Login page |
| `views/admin/dashboard/Index.vue` | Dashboard (protected) |
| `App.vue` | Session restoration |
| `app/Http/Controllers/Api/AuthController.php` | Auth endpoints |
| `app/Http/Requests/Auth/LoginRequest.php` | Input validation |
| `app/Services/AuthService.php` | Auth business logic |
| `database/seeders/RolePermissionSeeder.php` | Seed roles & permissions |

## Environment Variables

### Frontend (.env)
```
VITE_API_BASE_URL=http://localhost/api/v1
```

### Backend (.env)
```
SANCTUM_STATEFUL_DOMAINS=localhost
SANCTUM_PROTECTED_ROUTES=false
```

## Testing Credentials

| Email | Password | Role | Permissions |
|-------|----------|------|-------------|
| super@admin.com | 12345678 | super_admin | All (7) |

## Next Steps

1. ✅ **Authentication Complete** - Login/logout working
2. ✅ **Route Guards Complete** - Protected routes working
3. ⏳ **User Management CRUD** - Create API endpoints for users
4. ⏳ **Role Management** - Create API endpoints for roles
5. ⏳ **Permission Management** - Create API endpoints for permissions

All authentication infrastructure is now in place!

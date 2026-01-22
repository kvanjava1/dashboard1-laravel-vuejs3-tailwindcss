# Route Guards Documentation

## Overview
Route guards protect routes based on authentication status and user permissions. They run before each route navigation.

## File Location
```
resources/js/vue3_dashboard_admin/router/routes/index.ts
```

## How It Works

### Guard Flow
```
beforeEach() Guard
    ↓
Check if route requires auth
    ↓
Check if user is authenticated
    ↓
Check permissions/roles
    ↓
Allow or redirect
```

## Route Configuration

### Protected Route with Permission
```typescript
{
    path: '/dashboard',
    name: 'dashboard.index',
    component: Dashboard,
    meta: {
        requiresAuth: true,
        requiredPermission: 'dashboard.view',
        title: 'Dashboard'
    }
}
```

### Protected Route with Role
```typescript
{
    path: '/admin-settings',
    name: 'admin.settings',
    component: AdminSettings,
    meta: {
        requiresAuth: true,
        requiredRole: 'super_admin',
        title: 'Admin Settings'
    }
}
```

### Public Route (No Auth Required)
```typescript
{
    path: '/login',
    name: 'login',
    component: Login
    // No requiresAuth meta
}
```

## Features

### 1. Authentication Check
- ✅ Blocks unauthenticated users from protected routes
- ✅ Redirects to login with return URL
- ✅ Allows authenticated users to access protected routes

```typescript
if (requiresAuth && !isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
}
```

### 2. Permission Check
Verifies user has required permission before allowing access.

```typescript
if (to.meta.requiredPermission) {
    const hasPermission = authStore.hasPermission(to.meta.requiredPermission)
    if (!hasPermission) {
        next({ name: 'dashboard.index' })
    }
}
```

### 3. Role Check
Verifies user has required role before allowing access.

```typescript
if (to.meta.requiredRole) {
    const hasRole = authStore.hasRole(to.meta.requiredRole)
    if (!hasRole) {
        next({ name: 'dashboard.index' })
    }
}
```

### 4. Auto-Redirect Logic
- Authenticated user accessing login page → Redirects to dashboard
- Unauthenticated user accessing protected route → Redirects to login
- User without permission → Redirects to dashboard

## Usage Examples

### Adding Permission Check to Route
```typescript
// user-management.ts
const userRoutes: RouteRecordRaw[] = [
    {
        path: '/users',
        name: 'user-management.index',
        component: UserList,
        meta: {
            requiresAuth: true,
            requiredPermission: 'user_management.search',
            title: 'Users'
        }
    },
    {
        path: '/users/:id/edit',
        name: 'user-management.edit',
        component: UserEdit,
        meta: {
            requiresAuth: true,
            requiredPermission: 'user_management.edit',
            title: 'Edit User'
        }
    }
]
```

### Accessing Redirect URL in Login Component
```typescript
import { useRoute } from 'vue-router'

const route = useRoute()
const redirectUrl = route.query.redirect as string

// After successful login
router.push(redirectUrl || '/dashboard')
```

### Adding Multiple Permission Check
```typescript
// Route requires ANY of these permissions
meta: {
    requiredPermission: 'user_management.add'
    // Use authStore.hasAnyPermission() in component if needed
}
```

## Available Permissions

| Permission | Description |
|------------|-------------|
| `dashboard.view` | View dashboard |
| `user_management.add` | Create new user |
| `user_management.edit` | Edit user |
| `user_management.delete` | Delete user |
| `user_management.search` | Search users |
| `report.view` | View reports |
| `report.export` | Export reports |

## Available Roles

| Role | Permissions |
|------|-------------|
| `super_admin` | All permissions (automatic) |

## Error Handling

### Unauthorized Access
```
Console Warning:
"User does not have permission: user_management.add"
"User does not have role: super_admin"

User Action:
Redirected to dashboard.index
```

### Session Expired
```
API returns 401
Auth store logs out user
User redirected to login page
```

## Best Practices

1. **Always set requiresAuth for protected routes**
```typescript
meta: { requiresAuth: true }
```

2. **Set requiredPermission for permission-based routes**
```typescript
meta: { requiredPermission: 'user_management.edit' }
```

3. **Use meaningful names for redirect URL**
```typescript
query: { redirect: to.fullPath }
```

4. **Handle session restoration in App.vue**
- User can refresh page and session is restored from localStorage
- Routes remain protected even after page reload

5. **Check permission in templates too**
- Don't rely on guards alone
- Hide UI elements based on permissions

```vue
<button v-if="authStore.hasPermission('user_management.add')">
  Add User
</button>
```

## Testing

### Test Case 1: Access Protected Route Without Auth
```
1. Clear localStorage auth_token
2. Navigate to /dashboard
3. Expected: Redirect to /login?redirect=/dashboard
```

### Test Case 2: Access Protected Route With Wrong Permission
```
1. Login with user who has different permission
2. Navigate to /users/edit
3. Expected: Redirect to /dashboard
```

### Test Case 3: Access Login After Auth
```
1. Login successfully
2. Navigate to /login
3. Expected: Redirect to /dashboard
```

### Test Case 4: Session Restoration
```
1. Login and close browser
2. Reopen browser
3. Expected: Session restored, can access protected routes
```

## Meta Properties

```typescript
interface RouteMeta {
  requiresAuth?: boolean        // Route requires authentication
  requiredPermission?: string   // Required permission to access
  requiredRole?: string         // Required role to access
  title?: string                // Page title
}
```

## Flow Diagram

```
User navigates to route
    ↓
beforeEach() guard executes
    ↓
Is route public? → YES → Allow navigation
    ↓ NO
Is user authenticated? → NO → Redirect to /login?redirect=...
    ↓ YES
Has required permission? → NO → Redirect to /dashboard
    ↓ YES
Has required role? → NO → Redirect to /dashboard
    ↓ YES
Allow navigation ✓
```

## Logging

All guard decisions are logged to help with debugging:

```typescript
// Console output examples
"User does not have permission: user_management.add"
"User does not have role: super_admin"
"Redirecting to login"
```

Enable/disable in production by checking `import.meta.env.DEV`.

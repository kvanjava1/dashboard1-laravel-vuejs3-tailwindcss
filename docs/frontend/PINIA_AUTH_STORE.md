# Pinia Auth Store Documentation

## Overview
The auth store manages user authentication state, permissions, and provides methods for login/logout operations.

## File Location
```
resources/js/vue3_dashboard_admin/stores/auth.ts
```

## State

```typescript
interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  permissions: string[];
}

// State Properties
token: string | null          // JWT token from API
user: User | null             // Current logged-in user
isLoading: boolean            // Loading state for async operations
error: string | null          // Error message if any
```

## Getters

### `isAuthenticated`
```typescript
const isAuth = authStore.isAuthenticated // boolean
```
Returns true if user is logged in (has token and user data).

### `currentUser`
```typescript
const user = authStore.currentUser // User | null
```
Returns the currently logged-in user object.

### `userRole`
```typescript
const role = authStore.userRole // string | null
```
Returns the current user's role (e.g., 'super_admin').

## Methods

### `login(email: string, password: string)`
Authenticates user and retrieves token with user data.

```typescript
try {
  await authStore.login('super@admin.com', '12345678')
  // User logged in, token stored
} catch (error) {
  // Error message available in authStore.error
  console.error(error)
}
```

**Features:**
- ✅ Validates credentials with API
- ✅ Stores JWT token in localStorage
- ✅ Stores user data in localStorage
- ✅ Sets error if login fails
- ✅ Handles rate limiting (429 errors)

### `logout()`
Clears user session and token.

```typescript
await authStore.logout()
// Redirects to login page
```

### `fetchUser()`
Retrieves current user data from API (requires valid token).

```typescript
await authStore.fetchUser()
// Updates user state with fresh data
```

### `restoreSession()`
Restores user session from localStorage on app startup.

```typescript
// Call in App.vue onMounted()
await authStore.restoreSession()
```

### `hasPermission(permission: string)`
Checks if user has a specific permission.

```typescript
if (authStore.hasPermission('user_management.add')) {
  // Show add user button
}
```

### `hasRole(role: string)`
Checks if user has a specific role.

```typescript
if (authStore.hasRole('super_admin')) {
  // Show admin features
}
```

### `hasAnyPermission(permissions: string[])`
Checks if user has ANY of the specified permissions.

```typescript
if (authStore.hasAnyPermission(['user_management.add', 'user_management.edit'])) {
  // Show action buttons
}
```

### `hasAllPermissions(permissions: string[])`
Checks if user has ALL specified permissions.

```typescript
if (authStore.hasAllPermissions(['dashboard.view', 'user_management.search'])) {
  // Show restricted feature
}
```

### `clearError()`
Clears the error message.

```typescript
authStore.clearError()
```

## Usage Examples

### In Components

```vue
<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>

<template>
  <!-- Check if authenticated -->
  <div v-if="authStore.isAuthenticated">
    <p>Welcome {{ authStore.currentUser?.name }}</p>
  </div>

  <!-- Show error -->
  <div v-if="authStore.error" class="error">
    {{ authStore.error }}
  </div>

  <!-- Check permissions -->
  <button v-if="authStore.hasPermission('user_management.add')">
    Add User
  </button>

  <!-- Loading state -->
  <div v-if="authStore.isLoading">Loading...</div>
</template>
```

### Login Form (Login.vue)

```typescript
const handleLogin = async () => {
  try {
    authStore.clearError()
    await authStore.login(email, password)
    router.push({ name: 'dashboard.index' })
  } catch (error) {
    // Error already in authStore.error
  }
}
```

### App Initialization (App.vue)

```typescript
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

onMounted(async () => {
  // Restore session on app startup
  await authStore.restoreSession()
})
```

## Storage

### localStorage Keys

| Key | Purpose | Example |
|-----|---------|---------|
| `auth_token` | JWT token for API requests | `1\|abc123xyz...` |
| `user` | User object (JSON stringified) | `{id: 1, name: "Admin", ...}` |

### Persistence

- ✅ Token stored after successful login
- ✅ User data stored and synced
- ✅ Both cleared on logout
- ✅ Session restored on app startup

## API Integration

The store automatically handles:
- ✅ Adding Authorization header to all API requests
- ✅ Clearing auth on 401 Unauthorized responses
- ✅ Rate limit errors (429)
- ✅ Network errors

## Error Handling

```typescript
authStore.error // Contains error message if any operation fails
authStore.isLoading // True during API calls
```

## Best Practices

1. **Call `restoreSession()` in App.vue** - Restores user session on page reload
2. **Use `hasPermission()` for UI logic** - Hide/show features based on permissions
3. **Redirect on 401** - Auth store automatically logs out on unauthorized
4. **Clear error after showing** - Use `clearError()` to reset error state
5. **Check `isLoading` for buttons** - Disable submit buttons during API calls

## Available Permissions

```
dashboard.view                    // View dashboard
user_management.add               // Add new user
user_management.edit              // Edit user
user_management.delete            // Delete user
user_management.search            // Search users
report.view                       // View reports
report.export                     // Export reports
```

## Type Definitions

```typescript
interface User {
  id: number
  name: string
  email: string
  role: string
  permissions: string[]
}

interface AuthState {
  token: string | null
  user: User | null
  isLoading: boolean
  error: string | null
}
```

import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import route modules
import authRoutes from './auth'
import dashboardRoutes from './dashboard'
import userRoutes from './user'
import roleRoutes from './role'

// Combine all routes
const routes: RouteRecordRaw[] = [
    ...authRoutes,
    ...dashboardRoutes,
    ...userRoutes,
    ...roleRoutes,
    {
      path: '/example-management/index',
      name: 'admin-example-management',
      component: () => import('@/views/admin/example_management/Index.vue'),
      meta: {
        requiresAuth: true,
        layout: 'admin',
        title: 'Example Management'
      }
    },
    {
      path: '/admin/example-management/add',
      name: 'admin-example-management-add',
      component: () => import('@/views/admin/example_management/Add.vue'),
      meta: {
        requiresAuth: true,
        layout: 'admin',
        title: 'Add Example'
      }
    }
]

const router = createRouter({
  history: createWebHistory('/management/'),
  routes
})

// Route guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Routes that don't require authentication
  const publicRoutes = ['login']

  // Check if route requires authentication
  const requiresAuth = !publicRoutes.includes(to.name as string)

  // Check if user is authenticated
  const isAuthenticated = authStore.isAuthenticated

  // If route requires auth and user is not authenticated
  if (requiresAuth && !isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  // If user is authenticated and trying to access login page
  if (isAuthenticated && to.name === 'login') {
    next({ name: 'dashboard.index' })
    return
  }

  // Check permissions if route requires specific permission
  if (to.meta.requiredPermission) {
    const hasPermission = authStore.hasPermission(to.meta.requiredPermission as string)

    if (!hasPermission) {
      console.warn(`User does not have permission: ${to.meta.requiredPermission}`)
      next({
        path: '/no-permissions',
        query: {
          redirect: to.fullPath
        }
      })
      return
    }
  }

  // Check role if route requires specific role
  if (to.meta.requiredRole) {
    const hasRole = authStore.hasRole(to.meta.requiredRole as string)

    if (!hasRole) {
      console.warn(`User does not have role: ${to.meta.requiredRole}`)
      next({
        path: '/no-permissions',
        query: {
          requiredRole: to.meta.requiredRole as string,
          redirect: to.fullPath
        }
      })
      return
    }
  }

  next()
})

export default router
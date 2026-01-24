/**
 * API Routes Configuration
 *
 * Central configuration for all API endpoints used in Vue.js
 * Provides type-safe access to Laravel API routes
 */

interface RouteParams {
  page?: number
  per_page?: number
  search?: string
  permissions?: string[]
}

interface UserRouteParams extends RouteParams {
  search?: string
  role?: string
  status?: string
}

export const apiRoutes = {
  // Authentication routes
  auth: {
    login: '/api/v1/login',
    logout: '/api/v1/logout',
    me: '/api/v1/me'
  },

  // Role management routes
  roles: {
    index: (params?: RouteParams) => {
      const searchParams = new URLSearchParams()
      if (params?.page) searchParams.set('page', params.page.toString())
      if (params?.per_page) searchParams.set('per_page', params.per_page.toString())
      if (params?.search) searchParams.set('search', params.search)
      if (params?.permissions && params.permissions.length > 0) {
        params.permissions.forEach(permission => {
          searchParams.append('permissions[]', permission)
        })
      }
      return `/api/v1/roles${searchParams.toString() ? '?' + searchParams.toString() : ''}`
    },
    options: '/api/v1/roles/options',
    show: (id: string | number) => `/api/v1/roles/${id}`,
    store: '/api/v1/roles',
    update: (id: string | number) => `/api/v1/roles/${id}`,
    destroy: (id: string | number) => `/api/v1/roles/${id}`
  },

  // User management routes
  users: {
    index: (params?: UserRouteParams) => {
      const searchParams = new URLSearchParams()
      if (params?.page) searchParams.set('page', params.page.toString())
      if (params?.per_page) searchParams.set('per_page', params.per_page.toString())
      if (params?.search) searchParams.set('search', params.search)
      if (params?.role) searchParams.set('role', params.role)
      if (params?.status) searchParams.set('status', params.status)
      return `/api/v1/users${searchParams.toString() ? '?' + searchParams.toString() : ''}`
    },
    show: (id: string | number) => `/api/v1/users/${id}`,
    store: '/api/v1/users',
    update: (id: string | number) => `/api/v1/users/${id}`,
    destroy: (id: string | number) => `/api/v1/users/${id}`
  },

  // Permission routes
  permissions: {
    index: '/api/v1/permissions',
    grouped: '/api/v1/permissions/grouped'
  }
} as const

export type ApiRoutes = typeof apiRoutes
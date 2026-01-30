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
  name?: string
  email?: string
  phone?: string
  username?: string
  location?: string
  bio?: string
  is_banned?: string
  date_of_birth_from?: string
  date_of_birth_to?: string
  date_from?: string
  date_to?: string
  timezone?: string
  sort_by?: string
  sort_order?: string
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
      if (params?.name) searchParams.set('name', params.name)
      if (params?.email) searchParams.set('email', params.email)
      if (params?.phone) searchParams.set('phone', params.phone)
      if (params?.username) searchParams.set('username', params.username)
      if (params?.location) searchParams.set('location', params.location)
      if (params?.bio) searchParams.set('bio', params.bio)
      if (params?.is_banned) searchParams.set('is_banned', params.is_banned)
      if (params?.date_of_birth_from) searchParams.set('date_of_birth_from', params.date_of_birth_from)
      if (params?.date_of_birth_to) searchParams.set('date_of_birth_to', params.date_of_birth_to)
      if (params?.date_from) searchParams.set('date_from', params.date_from)
      if (params?.date_to) searchParams.set('date_to', params.date_to)
      if (params?.timezone) searchParams.set('timezone', params.timezone)
      if (params?.sort_by) searchParams.set('sort_by', params.sort_by)
      if (params?.sort_order) searchParams.set('sort_order', params.sort_order)
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
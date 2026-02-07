import { ref } from 'vue'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'

export interface User {
  id: number
  name: string
  email: string
  status: string
  profile_image?: string
  role?: string
  role_display_name?: string
  is_banned: boolean
  is_active?: boolean
  protection?: {
    can_delete: boolean
    can_edit: boolean
    can_ban: boolean
    can_change_role: boolean
    reason: string
  }
  created_at: string
  updated_at: string
  joined_date: string
  avatar?: string
}

export const useUserData = () => {
  const { get } = useApi()

  // State
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Fetch user details by ID
  const fetchUser = async (userId: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await get(apiRoutes.users.show(userId))

      if (!response.ok) {
        throw new Error(`Failed to fetch user: ${response.status}`)
      }

      const data = await response.json()
      return data.user
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch user'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch users (list) with pagination and filters
  const fetchUsers = async (params: any) => {
    loading.value = true
    error.value = null

    try {
      const url = apiRoutes.users.index(params)
      const response = await get(url)

      if (!response.ok) {
        throw new Error(`Failed to fetch users: ${response.status} ${response.statusText}`)
      }

      const data = await response.json()

      // Map API data to frontend User interface
      const users = data.data.map((user: any) => ({
        id: user.id,
        name: user.name,
        email: user.email,
        status: user.status || '',
        profile_image: user.profile_image || undefined,
        role: user.role || undefined,
        role_display_name: user.role_display_name || undefined,
        is_banned: user.is_banned,
        is_active: user.is_active,
        protection: user.protection || undefined,
        created_at: user.created_at,
        updated_at: user.updated_at,
        joined_date: user.joined_date,
        avatar: user.profile_image || undefined
      }))

      return {
        users,
        meta: data.meta,
        filters: data.filters
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to load users'
      console.error('Error fetching users:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch available roles
  const fetchRoles = async () => {
    try {
      const response = await get(apiRoutes.roles.options)

      if (!response.ok) {
        throw new Error('Failed to fetch roles')
      }

      const data = await response.json()
      return data.roles || []
    } catch (err: any) {
      console.error('Error fetching roles:', err)
      throw err
    }
  }

  // Fetch available statuses
  const fetchStatuses = async () => {
    try {
      const response = await get(apiRoutes.users.index({ page: 1, per_page: 1 }))

      if (!response.ok) {
        throw new Error('Failed to fetch statuses')
      }

      const data = await response.json()
      return data.filters?.status_options || []
    } catch (err: any) {
      console.error('Error fetching statuses:', err)
      throw err
    }
  }

  return {
    loading,
    error,
    fetchUser,
    fetchUsers,
    fetchRoles,
    fetchStatuses
  }
}
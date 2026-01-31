import { ref } from 'vue'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'

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
      // Since we don't have a dedicated status endpoint, we'll fetch from users endpoint
      // The users index returns status_options in filters
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
    fetchRoles,
    fetchStatuses
  }
}
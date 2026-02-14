import { ref } from 'vue'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'

export interface Role {
    id: number
    name: string
    display_name?: string
    permissions: string[]
    users_count?: number
    protection?: {
        can_delete: boolean
        can_modify: boolean
        can_ban_users: boolean
        reason?: string
    }
}

export const useRoleData = () => {
    const { get, del, post } = useApi()

    // State
    const loading = ref(false)
    const error = ref<string | null>(null)

    // Fetch role details by ID
    const fetchRole = async (roleId: number) => {
        loading.value = true
        error.value = null

        try {
            const response = await get(apiRoutes.roles.show(roleId))

            if (!response.ok) {
                throw new Error(`Failed to fetch role: ${response.status}`)
            }

            const data = await response.json()
            return data.role
        } catch (err: any) {
            error.value = err.message || 'Failed to fetch role'
            throw err
        } finally {
            loading.value = false
        }
    }

    // Fetch roles (list) with pagination and filters
    const fetchRoles = async (params: any) => {
        loading.value = true
        error.value = null

        try {
            const url = apiRoutes.roles.index(params)
            const response = await get(url)

            if (!response.ok) {
                throw new Error(`Failed to fetch roles: ${response.status} ${response.statusText}`)
            }

            const data = await response.json()

            return {
                roles: data.roles || [],
                total: data.total || 0,
                total_pages: data.total_pages || 0,
                current_page: data.current_page || params.page,
                per_page: data.per_page || params.per_page,
                from: data.from || 0,
                to: data.to || 0,
                available_permissions: data.available_permissions || {}
            }
        } catch (err: any) {
            error.value = err.message || 'Failed to load roles'
            console.error('Error fetching roles:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    // Delete role
    const deleteRole = async (roleId: number) => {
        loading.value = true
        error.value = null

        try {
            const response = await del(apiRoutes.roles.destroy(roleId))

            if (!response.ok) {
                throw new Error(`Failed to delete role: ${response.status}`)
            }

            return true
        } catch (err: any) {
            error.value = err.message || 'Failed to delete role'
            throw err
        } finally {
            loading.value = false
        }
    }

    // Clear role cache
    const clearRoleCache = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await post(apiRoutes.roles.clearCache, {})

            if (!response.ok) {
                throw new Error(`Failed to clear cache: ${response.status}`)
            }

            return true
        } catch (err: any) {
            error.value = err.message || 'Failed to clear cache'
            throw err
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        error,
        fetchRole,
        fetchRoles,
        deleteRole,
        clearRoleCache
    }
}

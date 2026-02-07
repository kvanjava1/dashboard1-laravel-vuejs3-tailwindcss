import { ref } from 'vue'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'

export const useCategoryData = () => {
    const { get } = useApi()

    // State
    const loading = ref(false)
    const error = ref<string | null>(null)

    // Fetch category details by ID
    const fetchCategory = async (id: number) => {
        loading.value = true
        error.value = null

        try {
            const response = await get(apiRoutes.categories.show(id))

            if (!response.ok) {
                throw new Error(`Failed to fetch category: ${response.status}`)
            }

            const data = await response.json()
            return data.data // Backend returns { data: category }
        } catch (err: any) {
            error.value = err.message || 'Failed to fetch category'
            throw err
        } finally {
            loading.value = false
        }
    }

    // Fetch all categories (for parent dropdown/tree)
    const fetchAllCategories = async () => {
        loading.value = true
        error.value = null

        try {
            // Fetch all, no pagination or default pagination which returns all
            const response = await get(apiRoutes.categories.index())

            if (!response.ok) {
                throw new Error('Failed to fetch categories')
            }

            const data = await response.json()
            // Backend returns { data: [...] } for index
            return data.data || []
        } catch (err: any) {
            error.value = err.message || 'Failed to fetch categories'
            console.error('Error fetching categories:', err)
            return []
            // throw err? Or return empty? UserData throws.
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        error,
        fetchCategory,
        fetchAllCategories
    }
}

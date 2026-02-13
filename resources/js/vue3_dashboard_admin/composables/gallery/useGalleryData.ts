import { ref } from 'vue'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'

export interface GalleryListMeta {
  total: number
  per_page: number
  current_page: number
  total_pages: number
}

export const useGalleryData = () => {
  const { get, post, put, del } = useApi()

  const loading = ref(false)
  const error = ref<string | null>(null)

  // Fetch single gallery by id
  const fetchGallery = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await get(apiRoutes.galleries.show(id))
      if (!response.ok) throw new Error(`Failed to fetch gallery: ${response.status}`)
      const body = await response.json()
      // controller returns { message, gallery }
      return body.gallery
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch gallery'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch paginated galleries (returns { galleries: [...], meta })
  const fetchGalleries = async (params?: Record<string, any>) => {
    loading.value = true
    error.value = null

    try {
      const url = apiRoutes.galleries.index(params)
      const response = await get(url)
      if (!response.ok) throw new Error(`Failed to fetch galleries: ${response.status}`)
      const body = await response.json()
      // controller wraps the payload under `data` => { galleries, total, ... }
      return body.data
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch galleries'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Create gallery (accepts FormData)
  const createGallery = async (formData: FormData) => {
    loading.value = true
    error.value = null

    try {
      const response = await post(apiRoutes.galleries.store, formData)
      if (!response.ok) {
        const err = await response.json().catch(() => ({} as any))
        throw new Error(err.message || 'Failed to create gallery')
      }
      const body = await response.json()
      return body.gallery || body.data || null
    } catch (err: any) {
      error.value = err.message || 'Failed to create gallery'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update gallery (FormData supported)
  const updateGallery = async (id: number, formData: FormData) => {
    loading.value = true
    error.value = null

    try {
      const response = await put(apiRoutes.galleries.update(id), formData)
      if (!response.ok) {
        const err = await response.json().catch(() => ({} as any))
        throw new Error(err.message || 'Failed to update gallery')
      }
      const body = await response.json()
      return body.gallery || body.data || null
    } catch (err: any) {
      error.value = err.message || 'Failed to update gallery'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteGallery = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await del(apiRoutes.galleries.destroy(id))
      if (!response.ok) {
        const err = await response.json().catch(() => ({} as any))
        throw new Error(err.message || 'Failed to delete gallery')
      }
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to delete gallery'
      throw err
    } finally {
      loading.value = false
    }
  }


  // Fetch tag suggestions (autocomplete) — returns array of { id, name }
  const fetchTagOptions = async (q: string, limit = 10) => {
    try {
      const url = `${apiRoutes.tags.options}?q=${encodeURIComponent(q)}&limit=${limit}`
      const response = await get(url)
      if (!response.ok) return []
      const body = await response.json().catch(() => null)
      return body?.data || []
    } catch (err) {
      return []
    }
  }

  return {
    loading,
    error,
    fetchGallery,
    fetchGalleries,
    createGallery,
    updateGallery,
    deleteGallery,
    fetchTagOptions
  }
}

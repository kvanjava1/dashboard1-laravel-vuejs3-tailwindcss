import { ref } from 'vue'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'

export interface MediaListMeta {
  total: number
  per_page: number
  current_page: number
  total_pages: number
}

export const useMediaData = () => {
  const { get, post, put, del } = useApi()

  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchMedia = async (params?: Record<string, any>) => {
    loading.value = true
    error.value = null

    try {
      const url = apiRoutes.media.index(params)
      const response = await get(url)
      if (!response.ok) throw new Error(`Failed to fetch media: ${response.status}`)
      const body = await response.json()
      return body.data
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch media'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchMediaById = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await get(apiRoutes.media.show(id))
      if (!response.ok) throw new Error(`Failed to fetch media: ${response.status}`)
      const body = await response.json()
      return body.media
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch media'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createMedia = async (formData: FormData) => {
    loading.value = true
    error.value = null

    try {
      const response = await post(apiRoutes.media.store, formData)
      if (!response.ok) {
        const err = await response.json().catch(() => ({} as any))
        throw new Error(err.message || 'Failed to create media')
      }
      const body = await response.json()
      return body.media || body.data || null
    } catch (err: any) {
      error.value = err.message || 'Failed to create media'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateMedia = async (id: number, formData: FormData) => {
    loading.value = true
    error.value = null

    try {
      const response = await put(apiRoutes.media.update(id), formData)
      if (!response.ok) {
        const err = await response.json().catch(() => ({} as any))
        throw new Error(err.message || 'Failed to update media')
      }
      const body = await response.json()
      return body.media || body.data || null
    } catch (err: any) {
      error.value = err.message || 'Failed to update media'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteMedia = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await del(apiRoutes.media.destroy(id))
      if (!response.ok) {
        const err = await response.json().catch(() => ({} as any))
        throw new Error(err.message || 'Failed to delete media')
      }
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to delete media'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    fetchMedia,
    fetchMediaById,
    createMedia,
    updateMedia,
    deleteMedia,
  }
}

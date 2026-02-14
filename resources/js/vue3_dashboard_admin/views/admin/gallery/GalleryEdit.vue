<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle :title="`Edit Gallery: ${gallery?.title || 'Loading...'}`" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="secondary" icon="arrow_back" @click="goBack">
                        Back to Galleries
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Loading State -->
        <LoadingState v-if="loading" message="Loading gallery data..." />

        <!-- Gallery Form -->
        <GalleryForm v-else ref="galleryFormRef" :mode="'edit'" :gallery="gallery" :gallery-categories="allCategories" @cancel="goBack" @submit="handleUpdate"
            @success="handleSuccess" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { showToast } from '@/composables/useSweetAlert'
import { useCategoryData } from '@/composables/category/useCategoryData'
import { useGalleryData } from '@/composables/gallery/useGalleryData'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import GalleryForm from '../../../components/gallery/GalleryForm.vue'

const router = useRouter()
const route = useRoute()
const { fetchCategoryOptions } = useCategoryData()
const { fetchGallery, updateGallery } = useGalleryData()

// State
const loading = ref(true)
const gallery = ref<any>(null)
const allCategories = ref([])
const galleryFormRef = ref<any>(null)

// Methods
const fetchData = async () => {
    loading.value = true

    try {
        const galleryId = parseInt(route.params.id as string)

        // Fetch gallery and categories in parallel
        const [fetched, cats] = await Promise.all([
            fetchGallery(galleryId),
            fetchCategoryOptions({ type: 'gallery' })
        ])

        if (!fetched) {
            await showToast({ icon: 'error', title: 'Gallery not found', text: 'The requested gallery could not be found.' })
            goBack()
            return
        }

        // Normalize to the shape GalleryForm expects
        const media = Array.isArray(fetched.media) ? fetched.media : []
        const primary = media.find((m: any) => m.is_cover) || (fetched.cover ? fetched.cover : null)

        gallery.value = {
            id: fetched.id,
            title: fetched.title,
            description: fetched.description || '',
            category_id: fetched.category_id || null,
            status: fetched.is_active ? 'active' : 'inactive',
            visibility: fetched.is_public ? 'public' : 'private',
            itemCount: fetched.item_count || 0,
            tags: Array.isArray(fetched.tags) ? fetched.tags.map((t: any) => (t.name || t)) : [],
            coverImage: primary ? (primary.url || primary) : '',
            media: media,
        }

        allCategories.value = cats as any
    } catch (error) {
        console.error('Error fetching gallery:', error)
        await showToast({ icon: 'error', title: 'Error', text: 'Failed to load gallery data.' })
        goBack()
    } finally {
        loading.value = false
    }
}

const goBack = () => {
    router.push({ name: 'gallery_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'gallery_management.index' })
}

const handleUpdate = async (formData: FormData) => {
    try {
        const galleryId = parseInt(route.params.id as string)
        await updateGallery(galleryId, formData)
        await showToast({ icon: 'success', title: 'Saved', text: 'Gallery updated successfully.', timer: 1200 })
        handleSuccess()
    } catch (err: any) {
        // reset child loading state
        galleryFormRef.value?.resetLoading?.()
        await showToast({ icon: 'error', title: 'Error', text: err.message || 'Failed to update gallery' })
    }
}

onMounted(() => {
    fetchData()
})
</script>

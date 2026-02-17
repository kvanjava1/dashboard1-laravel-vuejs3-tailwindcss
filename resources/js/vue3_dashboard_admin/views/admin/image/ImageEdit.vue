<template>
    <AdminLayout>
        <PageHeader>
            <template #title>
                <PageHeaderTitle :title="`Edit Image: ${image?.name || 'Loading...'}`" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="secondary" icon="arrow_back" @click="goBack">
                        Back to Images
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <LoadingState v-if="loading" message="Loading image data..." />

        <ImageForm v-else ref="imageFormRef" mode="edit" :image="image" :galleries="allGalleries" @cancel="goBack" @submit="handleUpdate" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { showToast } from '@/composables/useSweetAlert'
import { useGalleryData } from '@/composables/gallery/useGalleryData'
import { useMediaData } from '@/composables/image/useMediaData'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import ImageForm from '../../../components/image/ImageForm.vue'

const router = useRouter()
const route = useRoute()
const { fetchGalleries } = useGalleryData()
const { fetchMediaById, updateMedia } = useMediaData()

const loading = ref(true)
const image = ref<any>(null)
const allGalleries = ref<any[]>([])
const imageFormRef = ref<any>(null)

const goBack = () => {
    router.push({ name: 'image_management.index' })
}

const handleUpdate = async (formData: FormData) => {
    try {
        const imageId = parseInt(route.params.id as string)
        await updateMedia(imageId, formData)
        await showToast({ icon: 'success', title: 'Saved', text: 'Image updated successfully.', timer: 1200 })
        goBack()
    } catch (err: any) {
        imageFormRef.value?.resetLoading?.()
        await showToast({ icon: 'error', title: 'Error', text: err.message || 'Failed to update image' })
    }
}

const fetchData = async () => {
    loading.value = true
    try {
        const imageId = parseInt(route.params.id as string)
        const [fetched, galleries] = await Promise.all([
            fetchMediaById(imageId),
            fetchGalleries({ per_page: 200 })
        ])

        image.value = fetched
        allGalleries.value = (galleries?.galleries || []).map((g: any) => ({ id: g.id, title: g.title }))
    } catch (error) {
        await showToast({ icon: 'error', title: 'Error', text: 'Failed to load image data.' })
        goBack()
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchData()
})
</script>

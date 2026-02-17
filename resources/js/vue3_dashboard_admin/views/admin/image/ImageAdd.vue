<template>
    <AdminLayout>
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Add Image" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="secondary" icon="arrow_back" @click="goBack">
                        Back to Images
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <ImageForm ref="imageFormRef" mode="create" :galleries="allGalleries" @cancel="goBack" @submit="handleCreate" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGalleryData } from '@/composables/gallery/useGalleryData'
import { useMediaData } from '@/composables/image/useMediaData'
import { showToast } from '@/composables/useSweetAlert'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import ImageForm from '../../../components/image/ImageForm.vue'

const router = useRouter()
const { fetchGalleries } = useGalleryData()
const { createMedia } = useMediaData()

const imageFormRef = ref<any>(null)
const allGalleries = ref<any[]>([])

const goBack = () => {
    router.push({ name: 'image_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'image_management.index' })
}

const handleCreate = async (formData: FormData) => {
    try {
        await createMedia(formData)
        await showToast({ icon: 'success', title: 'Uploaded!', text: 'Image uploaded successfully.', timer: 1200 })
        handleSuccess()
    } catch (err: any) {
        imageFormRef.value?.resetLoading?.()
        await showToast({ icon: 'error', title: 'Error', text: err.message || 'Failed to upload image' })
    }
}

onMounted(async () => {
    const result = await fetchGalleries({ per_page: 200 })
    allGalleries.value = (result?.galleries || []).map((g: any) => ({ id: g.id, title: g.title }))
})
</script>

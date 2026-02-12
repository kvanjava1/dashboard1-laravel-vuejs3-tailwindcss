<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Create Gallery" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="secondary" icon="arrow_back" @click="goBack">
                        Back to Galleries
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Gallery Form -->
        <GalleryForm ref="galleryFormRef" mode="create" :gallery-categories="allCategories" @cancel="goBack" @submit="handleCreate" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCategoryData } from '@/composables/category/useCategoryData'
import { useGalleryData } from '@/composables/gallery/useGalleryData'
import { showToast } from '@/composables/useSweetAlert'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import GalleryForm from '../../../components/gallery/GalleryForm.vue'

const router = useRouter()
const { fetchCategoryOptions } = useCategoryData()
const { createGallery } = useGalleryData()

const galleryFormRef = ref<any>(null)
const allCategories = ref([])

const goBack = () => {
    router.push({ name: 'gallery_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'gallery_management.index' })
}

const handleCreate = async (formData: FormData) => {
    try {
        await createGallery(formData)
        await showToast({ icon: 'success', title: 'Created!', text: 'Gallery created successfully.', timer: 1200 })
        handleSuccess()
    } catch (err: any) {
        // reset child loading state and show error
        galleryFormRef.value?.resetLoading?.()
        await showToast({ icon: 'error', title: 'Error', text: err.message || 'Failed to create gallery' })
    }
}

onMounted(async () => {
    allCategories.value = await fetchCategoryOptions({ type: 'gallery' })
})
</script>

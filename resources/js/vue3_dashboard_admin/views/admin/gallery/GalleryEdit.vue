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
        <GalleryForm v-else :mode="'edit'" :gallery="gallery" :gallery-categories="allCategories" @cancel="goBack"
            @success="handleSuccess" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { showToast } from '@/composables/useSweetAlert'
import { useCategoryData } from '@/composables/category/useCategoryData'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import GalleryForm from '../../../components/gallery/GalleryForm.vue'
import { galleryMocks } from '@/mocks/gallery/galleryMocks'

const router = useRouter()
const route = useRoute()
const { fetchCategoryOptions } = useCategoryData()

// State
const loading = ref(true)
const gallery = ref<any>(null)
const allCategories = ref([])

// Methods
const fetchData = async () => {
    loading.value = true

    try {
        const galleryId = parseInt(route.params.id as string)

        // Fetch gallery and categories in parallel
        const [foundGallery, cats] = await Promise.all([
            new Promise(resolve => {
                setTimeout(() => {
                    resolve(galleryMocks.find(g => g.id === galleryId))
                }, 800)
            }),
            fetchCategoryOptions({ type: 'gallery' })
        ])

        if (foundGallery) {
            gallery.value = foundGallery
            allCategories.value = cats as any
        } else {
            await showToast({
                icon: 'error',
                title: 'Gallery not found',
                text: 'The requested gallery could not be found.'
            })
            goBack()
        }
    } catch (error) {
        console.error('Error fetching gallery:', error)
        await showToast({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load gallery data.'
        })
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

onMounted(() => {
    fetchData()
})
</script>

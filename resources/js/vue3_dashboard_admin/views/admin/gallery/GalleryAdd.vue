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
        <GalleryForm mode="create" :gallery-categories="allCategories" @cancel="goBack" @success="handleSuccess" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCategoryData } from '@/composables/category/useCategoryData'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import GalleryForm from '../../../components/gallery/GalleryForm.vue'

const router = useRouter()
const { fetchCategoryOptions } = useCategoryData()

const allCategories = ref([])

const goBack = () => {
    router.push({ name: 'gallery_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'gallery_management.index' })
}

onMounted(async () => {
    allCategories.value = await fetchCategoryOptions({ type: 'gallery' })
})
</script>

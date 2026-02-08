<template>
    <AdminLayout>
        <PageHeader>
            <template #title>
                <PageHeaderTitle :title="`Edit Category: ${category?.name || 'Loading...'}`" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="arrow_back" @click="goBack">
                        Back
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <LoadingState v-if="loading" message="Loading category..." />

        <ErrorState v-else-if="error" :message="error" @retry="fetchData" />

        <CategoryForm v-else mode="edit" :category="category" :all-categories="allCategories" @cancel="goBack"
            @success="handleSuccess" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCategoryData } from '@/composables/category/useCategoryData'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import CategoryForm from '../../../components/category/CategoryForm.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import ErrorState from '../../../components/ui/ErrorState.vue'

const router = useRouter()
const route = useRoute()
const { fetchCategory, fetchCategoryOptions, loading, error } = useCategoryData()

const category = ref<any>(null)
const allCategories = ref([])

const goBack = () => {
    router.push({ name: 'category_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'category_management.index' })
}

const fetchData = async () => {
    const id = Number(route.params.id)
    if (!id) {
        error.value = 'Invalid Category ID'
        return
    }

    try {
        // Fetch in parallel
        const [cat, cats] = await Promise.all([
            fetchCategory(id),
            fetchCategoryOptions()
        ])
        category.value = cat
        allCategories.value = cats
    } catch (err) {
        // error handled by composable or caught here
        console.error(err)
    }
}

onMounted(() => {
    fetchData()
})
</script>

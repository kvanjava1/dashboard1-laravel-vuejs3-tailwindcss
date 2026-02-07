<template>
    <AdminLayout>
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Add New Category" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="arrow_back" @click="goBack">
                        Back
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <CategoryForm mode="create" :all-categories="allCategories" @cancel="goBack" @success="handleSuccess" />
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
import CategoryForm from '../../../components/category/CategoryForm.vue'

const router = useRouter()
const { fetchAllCategories } = useCategoryData()

const allCategories = ref([])

const goBack = () => {
    router.push({ name: 'category_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'category_management.index' })
}

onMounted(async () => {
    allCategories.value = await fetchAllCategories()
})
</script>

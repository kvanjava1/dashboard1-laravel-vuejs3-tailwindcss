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

        <CategoryForm
            mode="create"
            :all-categories="allCategories"
            @cancel="goBack"
            @success="handleSuccess"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import CategoryForm from '../../../components/category/CategoryForm.vue'
import { makeDummyCategories } from '@/mocks/categories'

const router = useRouter()
const allCategories = makeDummyCategories()

const goBack = () => {
    router.push({ name: 'category_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'category_management.index' })
}
</script>

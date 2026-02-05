<template>
    <AdminLayout>
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Edit Category" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="arrow_back" @click="goBack">
                        Back
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <ErrorState
            v-if="!category"
            message="Category not found"
            @retry="() => {}"
        />

        <CategoryForm
            v-else
            mode="edit"
            :category="category"
            :all-categories="allCategories"
            @cancel="goBack"
            @success="handleSuccess"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import ErrorState from '../../../components/ui/ErrorState.vue'
import CategoryForm from '../../../components/category/CategoryForm.vue'
import { makeDummyCategories } from '@/mocks/categories'
import type { Category } from '@/mocks/categories'

const router = useRouter()
const route = useRoute()

const categoryId = computed(() => Number(route.params.id))

const allCategories = makeDummyCategories()

const category = computed(() => {
    if (!Number.isFinite(categoryId.value) || categoryId.value <= 0) return null
    return allCategories.find((c) => c.id === categoryId.value) || null
})

const goBack = () => {
    router.push({ name: 'category_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'category_management.index' })
}
</script>

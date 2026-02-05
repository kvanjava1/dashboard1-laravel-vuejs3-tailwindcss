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

const router = useRouter()
const route = useRoute()

const categoryId = computed(() => Number(route.params.id))

type Category = {
    id: number
    name: string
    slug: string
    description?: string
    is_active: boolean
    created_at: string
    updated_at: string
}

const nowIso = () => new Date().toISOString()

const dummyById = (id: number): Category | null => {
    const now = nowIso()
    const map: Record<number, Omit<Category, 'created_at' | 'updated_at'>> = {
        1: { id: 1, name: 'Announcements', slug: 'announcements', description: 'Important updates for all users.', is_active: true },
        2: { id: 2, name: 'Tutorials', slug: 'tutorials', description: 'Guides and tutorials for onboarding.', is_active: true },
        3: { id: 3, name: 'Events', slug: 'events', description: 'Company and community events.', is_active: false },
        4: { id: 4, name: 'News', slug: 'news', description: 'Latest news and product updates.', is_active: true }
    }

    const base = map[id]
    if (!base) return null
    return { ...base, created_at: now, updated_at: now }
}

const category = computed(() => {
    if (!Number.isFinite(categoryId.value) || categoryId.value <= 0) return null
    return dummyById(categoryId.value)
})

const goBack = () => {
    router.push({ name: 'category_management.index' })
}

const handleSuccess = () => {
    router.push({ name: 'category_management.index' })
}
</script>

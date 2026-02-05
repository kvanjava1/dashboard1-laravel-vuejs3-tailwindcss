<template>
    <AdminLayout>
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Categories" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="add" @click="goToAddCategory">
                        Add New
                    </ActionButton>
                    <ActionDropdown>
                        <ActionDropdownItem icon="filter_list" @click="showAdvancedFilter = true">
                            Advanced Filter
                        </ActionDropdownItem>
                    </ActionDropdown>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <ContentBox>
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle
                        title="Category Management"
                        subtitle="Review categories and test UI before backend integration"
                    />
                </template>
            </ContentBoxHeader>

            <ContentBoxBody>
                <ActiveFiltersIndicator
                    :has-active-filters="hasActiveFilters"
                    @reset="handleResetAdvancedFilters"
                />

                <!-- Empty State -->
                <EmptyState
                    v-if="paginatedCategories.length === 0"
                    icon="category"
                    message="No categories found"
                    subtitle="Try adjusting filters or add a new category"
                />

                <!-- Data Table -->
                <div v-else>
                    <SimpleUserTable>
                        <SimpleUserTableHead>
                            <SimpleUserTableHeadRow>
                                <SimpleUserTableHeadCol>Category</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Slug</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Status</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Updated</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Actions</SimpleUserTableHeadCol>
                            </SimpleUserTableHeadRow>
                        </SimpleUserTableHead>

                        <SimpleUserTableBody>
                            <SimpleUserTableBodyRow v-for="category in paginatedCategories" :key="category.id">
                                <SimpleUserTableBodyCol>
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-800">{{ category.name }}</span>
                                        <span v-if="category.description" class="text-xs text-slate-500 mt-1 truncate max-w-[420px]">
                                            {{ category.description }}
                                        </span>
                                    </div>
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <span class="text-sm text-slate-700 font-mono">
                                        {{ category.slug }}
                                    </span>
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <StatusBadge :status="category.is_active" />
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <span class="text-sm text-slate-700">{{ formatDate(category.updated_at) }}</span>
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <div class="flex items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            left-icon="edit"
                                            title="Edit category"
                                            @click="goToEditCategory(category.id)"
                                        >
                                            Edit
                                        </Button>
                                        <Button
                                            variant="danger"
                                            size="sm"
                                            left-icon="delete"
                                            title="Delete category"
                                            @click="confirmDelete(category)"
                                        >
                                            Delete
                                        </Button>
                                    </div>
                                </SimpleUserTableBodyCol>
                            </SimpleUserTableBodyRow>
                        </SimpleUserTableBody>
                    </SimpleUserTable>
                </div>

                <!-- Pagination -->
                <Pagination
                    :current-start="currentStart"
                    :current-end="currentEnd"
                    :total="filteredCategories.length"
                    :current-page="pagination.current_page"
                    :total-pages="pagination.total_pages"
                    :rows-per-page="pagination.per_page"
                    @prev="prevPage"
                    @next="nextPage"
                    @goto="goToPage"
                />
            </ContentBoxBody>
        </ContentBox>

        <CategoryAdvancedFilterModal
            v-model="showAdvancedFilter"
            :initial-filters="{ search: filters.search, slug: filters.slug, status: filters.status }"
            @apply="handleApplyAdvancedFilters"
            @reset="handleResetAdvancedFilters"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import ActionDropdown from '../../../components/ui/ActionDropdown.vue'
import ActionDropdownItem from '../../../components/ui/ActionDropdownItem.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import SimpleUserTable from '../../../components/ui/SimpleUserTable.vue'
import SimpleUserTableHead from '../../../components/ui/SimpleUserTableHead.vue'
import SimpleUserTableHeadRow from '../../../components/ui/SimpleUserTableHeadRow.vue'
import SimpleUserTableHeadCol from '../../../components/ui/SimpleUserTableHeadCol.vue'
import SimpleUserTableBody from '../../../components/ui/SimpleUserTableBody.vue'
import SimpleUserTableBodyRow from '../../../components/ui/SimpleUserTableBodyRow.vue'
import SimpleUserTableBodyCol from '../../../components/ui/SimpleUserTableBodyCol.vue'
import Pagination from '../../../components/ui/Pagination.vue'
import EmptyState from '../../../components/ui/EmptyState.vue'
import ActiveFiltersIndicator from '../../../components/ui/ActiveFiltersIndicator.vue'
import StatusBadge from '../../../components/ui/StatusBadge.vue'
import Button from '../../../components/ui/Button.vue'
import { showConfirm, showToast } from '@/composables/useSweetAlert'
import CategoryAdvancedFilterModal from '../../../components/category/CategoryAdvancedFilterModal.vue'

const router = useRouter()

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

const categories = ref<Category[]>([
    {
        id: 1,
        name: 'Announcements',
        slug: 'announcements',
        description: 'Important updates for all users.',
        is_active: true,
        created_at: nowIso(),
        updated_at: nowIso()
    },
    {
        id: 2,
        name: 'Tutorials',
        slug: 'tutorials',
        description: 'Guides and tutorials for onboarding.',
        is_active: true,
        created_at: nowIso(),
        updated_at: nowIso()
    },
    {
        id: 3,
        name: 'Events',
        slug: 'events',
        description: 'Company and community events.',
        is_active: false,
        created_at: nowIso(),
        updated_at: nowIso()
    },
    {
        id: 4,
        name: 'News',
        slug: 'news',
        description: 'Latest news and product updates.',
        is_active: true,
        created_at: nowIso(),
        updated_at: nowIso()
    }
])

const filters = reactive({
    search: '',
    slug: '',
    status: '' as '' | 'active' | 'inactive'
})

const pagination = reactive({
    current_page: 1,
    per_page: 5,
    total_pages: 1
})

const filteredCategories = computed(() => {
    const search = filters.search.trim().toLowerCase()
    const slug = filters.slug.trim().toLowerCase()
    const status = filters.status

    return categories.value.filter((c) => {
        if (status === 'active' && !c.is_active) return false
        if (status === 'inactive' && c.is_active) return false

        if (slug && c.slug.toLowerCase() !== slug) return false

        if (search) {
            const haystack = `${c.name} ${c.slug}`.toLowerCase()
            return haystack.includes(search)
        }

        return true
    })
})

const hasActiveFilters = computed(() => {
    return filters.search.trim() !== '' || filters.slug.trim() !== '' || filters.status !== ''
})

const paginatedCategories = computed(() => {
    const start = (pagination.current_page - 1) * pagination.per_page
    const end = start + pagination.per_page
    return filteredCategories.value.slice(start, end)
})

const recalcPagination = () => {
    const total = filteredCategories.value.length
    pagination.total_pages = Math.max(1, Math.ceil(total / pagination.per_page))
    pagination.current_page = Math.min(pagination.current_page, pagination.total_pages)
}

watch(filteredCategories, () => {
    pagination.current_page = 1
    recalcPagination()
}, { immediate: true })

const currentStart = computed(() => {
    if (filteredCategories.value.length === 0) return 0
    return (pagination.current_page - 1) * pagination.per_page + 1
})

const currentEnd = computed(() => {
    const end = pagination.current_page * pagination.per_page
    return Math.min(end, filteredCategories.value.length)
})

const prevPage = () => {
    if (pagination.current_page > 1) pagination.current_page -= 1
}

const nextPage = () => {
    if (pagination.current_page < pagination.total_pages) pagination.current_page += 1
}

const goToPage = (page: number) => {
    const next = Math.max(1, Math.min(page, pagination.total_pages))
    pagination.current_page = next
}

const goToAddCategory = () => {
    router.push({ name: 'category_management.add' })
}

const goToEditCategory = (id: number) => {
    router.push({ name: 'category_management.edit', params: { id } })
}

const resetFilters = () => {
    filters.search = ''
    filters.slug = ''
    filters.status = ''
}

const showAdvancedFilter = ref(false)

const handleApplyAdvancedFilters = (next: { search: string; slug: string; status: '' | 'active' | 'inactive' }) => {
    filters.search = next.search
    filters.slug = next.slug
    filters.status = next.status
}

const handleResetAdvancedFilters = () => {
    resetFilters()
}

const confirmDelete = async (category: Category) => {
    const ok = await showConfirm({
        title: 'Delete category?',
        text: `This will remove "${category.name}" from the dummy list.`,
        icon: 'warning',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    })

    if (!ok) return

    categories.value = categories.value.filter((c) => c.id !== category.id)
    recalcPagination()
    await showToast({ icon: 'success', title: 'Deleted (dummy)', timer: 1200 })
}

const formatDate = (iso: string) => {
    try {
        return new Date(iso).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: '2-digit'
        })
    } catch {
        return iso
    }
}
</script>

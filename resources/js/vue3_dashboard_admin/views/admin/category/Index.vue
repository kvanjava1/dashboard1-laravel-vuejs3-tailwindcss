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
                        <ActionDropdownItem icon="cleaning_services" @click="handleClearCache">
                            Clear Cache
                        </ActionDropdownItem>
                    </ActionDropdown>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <ContentBox>
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="Category Management" subtitle="Manage article and gallery categories" />
                </template>
            </ContentBoxHeader>

            <ContentBoxBody>
                <div class="flex items-center space-x-3 mb-4">
                    <label class="text-sm font-medium text-slate-700">Show:</label>
                    <select v-model="filters.type"
                        class="pl-3 pr-8 py-2 text-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-40">
                        <option value="">All Types</option>
                        <option value="article">Article</option>
                        <option value="gallery">Gallery</option>
                    </select>
                </div>

                <ActiveFiltersIndicator :has-active-filters="hasActiveFilters" @reset="handleResetAdvancedFilters" />

                <!-- Loading State -->
                <LoadingState v-if="loading" message="Loading categories..." />

                <!-- Error State -->
                <ErrorState v-else-if="error" :message="error" @retry="fetchCategories" />

                <!-- Empty State -->
                <EmptyState v-else-if="paginatedCategories.length === 0" icon="category" message="No categories found"
                    subtitle="Try adjusting filters or add a new category" />

                <!-- Data Table -->
                <div v-else>
                    <SimpleUserTable>
                        <SimpleUserTableHead>
                            <SimpleUserTableHeadRow>
                                <SimpleUserTableHeadCol>Category</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Slug</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Type</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Status</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Updated</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Actions</SimpleUserTableHeadCol>
                            </SimpleUserTableHeadRow>
                        </SimpleUserTableHead>

                        <SimpleUserTableBody>
                            <SimpleUserTableBodyRow v-for="row in paginatedCategories" :key="row.category.id">
                                <SimpleUserTableBodyCol>
                                    <div class="flex items-start gap-2" :style="{ paddingLeft: `${row.depth * 32}px` }">
                                        <span v-if="row.depth > 0" class="text-slate-400 mt-0.5 select-none">↳</span>
                                        <div class="flex flex-col">
                                            <span :class="[
                                                'transition-colors',
                                                row.depth === 0 ? 'font-bold text-slate-900' : 'font-medium text-slate-700'
                                            ]">
                                                {{ row.category.name }}
                                            </span>
                                            <span v-if="row.category.description"
                                                class="text-xs text-slate-500 mt-1 truncate max-w-[420px]">
                                                {{ row.category.description }}
                                            </span>
                                        </div>
                                    </div>
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <span class="text-sm text-slate-700 font-mono">
                                        {{ row.category.slug }}
                                    </span>
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize"
                                        :class="row.category.type === 'gallery' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                                        {{ row.category.type }}
                                    </span>
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <StatusBadge :status="row.category.is_active" />
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <span class="text-sm text-slate-700">{{ formatDate(row.category.updated_at)
                                    }}</span>
                                </SimpleUserTableBodyCol>

                                <SimpleUserTableBodyCol>
                                    <div class="flex items-center gap-2">
                                        <Button variant="outline" size="sm" left-icon="edit" title="Edit category"
                                            @click="goToEditCategory(row.category.id)">
                                            Edit
                                        </Button>
                                        <Button variant="danger" size="sm" left-icon="delete" title="Delete category"
                                            @click="confirmDelete(row.category)">
                                            Delete
                                        </Button>
                                    </div>
                                </SimpleUserTableBodyCol>
                            </SimpleUserTableBodyRow>
                        </SimpleUserTableBody>
                    </SimpleUserTable>
                </div>
            </ContentBoxBody>
        </ContentBox>

        <CategoryAdvancedFilterModal v-model="showAdvancedFilter"
            :initial-filters="{ search: filters.search, slug: filters.slug, status: filters.status }"
            @apply="handleApplyAdvancedFilters" @reset="handleResetAdvancedFilters" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCategoryData } from '@/composables/category/useCategoryData'
import { apiRoutes } from '@/config/apiRoutes'
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
import EmptyState from '../../../components/ui/EmptyState.vue'
import ActiveFiltersIndicator from '../../../components/ui/ActiveFiltersIndicator.vue'
import StatusBadge from '../../../components/ui/StatusBadge.vue'
import Button from '../../../components/ui/Button.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import ErrorState from '../../../components/ui/ErrorState.vue'
import { showConfirm, showToast } from '@/composables/useSweetAlert'
import CategoryAdvancedFilterModal from '../../../components/category/CategoryAdvancedFilterModal.vue'

// Define local interface
interface Category {
    id: number
    name: string
    slug: string
    description?: string
    parent_id: number | null
    is_active: boolean
    type: string | { slug: string }
    created_at?: string
    updated_at?: string
}

const router = useRouter()
const { fetchAllCategories, deleteCategory, clearCategoryCache, loading, error } = useCategoryData()

const categories = ref<Category[]>([])

const fetchCategories = async () => {
    // We fetch ALL categories to build the tree structure client-side
    // This allows searching/filtering without breaking hierarchy in the view if preferred,
    // or we can just filter the flat list.
    const data = await fetchAllCategories()
    // Normalize type if it comes as object
    categories.value = data.map((c: any) => ({
        ...c,
        type: typeof c.type === 'object' && c.type ? c.type.slug : c.type
    }))
}

const filters = reactive({
    search: '',
    slug: '',
    status: '' as '' | 'active' | 'inactive',
    type: '' as '' | 'article' | 'gallery'
})

const filteredCategories = computed(() => {
    const search = filters.search.trim().toLowerCase()
    const slug = filters.slug.trim().toLowerCase()
    const status = filters.status
    const type = filters.type

    return categories.value.filter((c) => {
        if (status === 'active' && !c.is_active) return false
        if (status === 'inactive' && c.is_active) return false

        // c.type is normalized to string above
        if (type && (c.type as string) !== type) return false

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

type FlattenedRow = { category: Category; depth: number }

const flattenedCategories = computed<FlattenedRow[]>(() => {
    const list = filteredCategories.value
    const byId = new Map<number, Category>()
    const childrenByParent = new Map<number | null, Category[]>()

    for (const c of list) {
        byId.set(c.id, c)
    }

    for (const c of list) {
        const parentKey = c.parent_id !== null && byId.has(c.parent_id) ? c.parent_id : null
        const bucket = childrenByParent.get(parentKey) || []
        bucket.push(c)
        childrenByParent.set(parentKey, bucket)
    }

    const result: FlattenedRow[] = []

    const walk = (parentId: number | null, depth: number) => {
        const children = childrenByParent.get(parentId) || []
        // Sort by name for display
        children.sort((a, b) => a.name.localeCompare(b.name))

        for (const child of children) {
            result.push({ category: child, depth })
            walk(child.id, depth + 1)
        }
    }

    walk(null, 0)
    return result
})

const paginatedCategories = computed(() => {
    // Current implementation does not page client-side, shows all tree
    return flattenedCategories.value
})

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
    const childrenCount = categories.value.filter((c) => c.parent_id === category.id).length

    const ok = await showConfirm({
        title: 'Delete category?',
        text: childrenCount > 0
            ? `This category has ${childrenCount === 1 ? 'a child' : 'children'}. Deleting "${category.name}" will move ${childrenCount === 1 ? 'its child' : 'its children'} to the root.`
            : `Are you sure you want to delete "${category.name}"?`,
        icon: 'warning',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    })

    if (!ok) return

    try {
        await deleteCategory(category.id)
        await showToast({ icon: 'success', title: 'Category deleted', timer: 1200 })
        await fetchCategories()
    } catch (e: any) {
        await showToast({ icon: 'error', title: 'Failed to delete', text: e.message })
    }
}

const handleClearCache = async () => {
    const ok = await showConfirm({
        title: 'Clear Categories Cache?',
        text: 'This will force the system to reload all category data from the database.',
        icon: 'info',
        confirmButtonText: 'Yes, clear it'
    })

    if (!ok) return

    try {
        await clearCategoryCache()
        await showToast({ icon: 'success', title: 'Cache cleared', timer: 1200 })
        await fetchCategories()
    } catch (e: any) {
        await showToast({ icon: 'error', title: 'Error', text: e.message })
    }
}

const formatDate = (iso: string | undefined) => {
    if (!iso) return '-'
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

onMounted(() => {
    fetchCategories()
})
</script>

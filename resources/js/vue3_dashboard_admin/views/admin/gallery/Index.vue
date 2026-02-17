<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Gallery Management" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="primary" icon="add" @click="goToCreateGallery">
                        Add Gallery
                    </ActionButton>
                    <ActionDropdown icon="more_vert" align="right">
                        <ActionDropdownItem icon="filter_list" @click="showAdvancedFilter = true">
                            Advanced Filter
                        </ActionDropdownItem>
                        <ActionDropdownItem icon="refresh" @click="refreshGalleries">
                            Refresh List
                        </ActionDropdownItem>
                    </ActionDropdown>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Content Box -->
        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="Galleries"
                        :subtitle="`Manage your image galleries and collections (${filteredGalleries.length} ${filteredGalleries.length === 1 ? 'gallery' : 'galleries'})`" />
                </template>
            </ContentBoxHeader>

            <!-- Gallery Grid -->
            <ContentBoxBody>
                <!-- Filters Bar -->
                <div class="mb-6">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-400 text-[20px]">search</span>
                        </div>
                        <input v-model="searchInput" type="text"
                            placeholder="Search galleries by title, description, or category..."
                            class="block w-full pl-11 pr-4 py-3 bg-white border border-border-light text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary/50 transition-all shadow-inner-light"
                            :style="{ borderRadius: 'var(--radius-input)' }"
                            @keydown.enter="handleSearch" />
                    </div>

                    <!-- Active Filters Indicator -->
                    <div class="mt-4">
                        <ActiveFiltersIndicator :has-active-filters="hasActiveFilters" @reset="handleResetFilters" />
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="flex justify-between items-center mb-4">
                    <div v-if="showingText" class="text-sm text-gray-600">
                        {{ showingText }}
                    </div>
                </div>

                <!-- Grid View -->
                <div v-if="paginatedGalleries.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="gallery in paginatedGalleries" :key="gallery.id"
                        class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-100 group">
                        <!-- Gallery Image -->
                        <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                            <img :src="gallery.coverImage" :alt="gallery.title"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
                            <div class="absolute top-3 right-3">
                                <StatusBadge :status="gallery.status" />
                            </div>
                            <div
                                class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <Button variant="primary" size="sm" left-icon="visibility" @click="viewGallery(gallery)"
                                    class="transform scale-90 group-hover:scale-100 transition-transform">
                                    Quick View
                                </Button>
                            </div>
                        </div>

                        <!-- Gallery Info -->
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 mb-2 text-lg truncate" :title="gallery.title">
                                {{ gallery.title }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2 min-h-[40px]">
                                {{ gallery.description }}
                            </p>

                            <div
                                class="flex items-center justify-between text-xs text-gray-500 mb-4 pb-3 border-b border-gray-100">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">category</span>
                                    {{ gallery.category }}
                                </span>
                                <span class="flex items-center gap-1 font-semibold">
                                    <span class="material-symbols-outlined text-sm">photo_library</span>
                                    {{ gallery.itemCount }} items
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" left-icon="edit" @click="editGallery(gallery)"
                                    class="flex-1">
                                    Edit
                                </Button>
                                <Button variant="danger" size="sm" left-icon="delete" @click="confirmDelete(gallery)">
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <Pagination v-if="totalPages > 1" :current-start="currentStart" :current-end="currentEnd" :total="filteredGalleries.length"
                    :current-page="currentPage" :total-pages="totalPages"
                    :rows-per-page="itemsPerPage" @prev="prevPage" @next="nextPage" @goto="goToPage" />

                <!-- Empty State -->
                <EmptyState v-if="filteredGalleries.length === 0" icon="photo_library"
                    :message="hasActiveFilters ? 'No galleries match your filters' : 'No Galleries Found'"
                    :subtitle="hasActiveFilters ? 'Try adjusting your search criteria or filters' : 'Get started by creating your first gallery to showcase your images and media.'">
                    <template #action>
                        <Button v-if="!hasActiveFilters" variant="primary" left-icon="add" @click="goToCreateGallery">
                            Create Your First Gallery
                        </Button>
                        <Button v-else variant="outline" left-icon="clear" @click="handleResetFilters">
                            Clear Filters
                        </Button>
                    </template>
                </EmptyState>
            </ContentBoxBody>
        </ContentBox>

        <!-- Advanced Filter Modal -->
        <GalleryAdvancedFilterModal v-model="showAdvancedFilter" :initial-filters="currentFilters"
            :categories="categories" @apply="handleApplyFilters" @reset="handleResetFilters" />

        <!-- Gallery Detail Modal -->
        <GalleryDetailModal v-model="showDetailModal" :gallery="selectedGallery" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { showConfirm, showToast } from '@/composables/useSweetAlert'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import ActionDropdown from '../../../components/ui/ActionDropdown.vue'
import ActionDropdownItem from '../../../components/ui/ActionDropdownItem.vue'
import Button from '../../../components/ui/Button.vue'
import EmptyState from '../../../components/ui/EmptyState.vue'
import ActiveFiltersIndicator from '../../../components/ui/ActiveFiltersIndicator.vue'
import GalleryAdvancedFilterModal from '../../../components/gallery/GalleryAdvancedFilterModal.vue'
import GalleryDetailModal from '../../../components/gallery/GalleryDetailModal.vue'
import StatusBadge from '../../../components/ui/StatusBadge.vue'
import Pagination from '../../../components/ui/Pagination.vue'
import { useCategoryData } from '@/composables/category/useCategoryData'
import { useGalleryData } from '@/composables/gallery/useGalleryData'
import { debounce } from '@/utils/debounce'
// Router
const router = useRouter()
const { fetchCategoryOptions } = useCategoryData()

// Reactive state
const showAdvancedFilter = ref(false)
const showDetailModal = ref(false)
const selectedGallery = ref<any>(null)
const searchInput = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(5)

// Current filters
const currentFilters = reactive({
    search: '',
    category: '',
    status: '',
    min_items: '',
    max_items: '',
    date_from: '',
    date_to: ''
})

// Categories data
const categories = ref<any[]>([])

// Galleries state (server-driven if available, otherwise fall back to mocks)
const galleries = ref<any[]>([])
const useServer = ref(false)
const loading = ref(false)

const { fetchGalleries, deleteGallery } = useGalleryData()

onMounted(async () => {
    categories.value = await fetchCategoryOptions({ type: 'gallery' })
    await loadGalleries()
})

// Computed
const hasActiveFilters = computed(() => {
    return currentFilters.search.trim() !== '' ||
        currentFilters.category !== '' ||
        currentFilters.status !== '' ||
        currentFilters.min_items !== '' ||
        currentFilters.max_items !== ''
})

const filteredGalleries = computed(() => {
    // If server-driven, server already applied filtering — return what's loaded
    if (useServer.value) return galleries.value

    // Client-side filtering (fallback / mocks)
    let filtered = galleries.value

    // Filter by search term
    if (currentFilters.search.trim()) {
        const searchTerm = currentFilters.search.toLowerCase()
        filtered = filtered.filter(gallery =>
            gallery.title.toLowerCase().includes(searchTerm) ||
            gallery.description.toLowerCase().includes(searchTerm) ||
            gallery.category.toLowerCase().includes(searchTerm)
        )
    }

    // Filter by category
    if (currentFilters.category) {
        const selectedCat = categories.value.find(c => String(c.id) === currentFilters.category)
        const matchValue = selectedCat ? selectedCat.name : currentFilters.category
        filtered = filtered.filter(gallery => gallery.category === matchValue)
    }

    // Filter by status
    if (currentFilters.status) {
        filtered = filtered.filter(gallery => gallery.status === currentFilters.status)
    }

    // Filter by minimum items
    if (currentFilters.min_items) {
        const minItems = parseInt(currentFilters.min_items)
        filtered = filtered.filter(gallery => gallery.itemCount >= minItems)
    }

    // Filter by maximum items
    if (currentFilters.max_items) {
        const maxItems = parseInt(currentFilters.max_items)
        filtered = filtered.filter(gallery => gallery.itemCount <= maxItems)
    }

    return filtered
})

// Pagination helpers — use server-provided pagination when available
const serverTotal = ref(0)
const serverPerPage = ref(itemsPerPage.value)
const serverCurrentPage = ref(1)
const serverTotalPages = ref(1)

const paginatedGalleries = computed(() => {
    // When server-driven, `galleries` already contains current page
    if (useServer.value) return galleries.value

    // Client-side pagination
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredGalleries.value.slice(start, end)
})

const totalPages = computed(() => {
    return useServer.value ? serverTotalPages.value : Math.ceil(filteredGalleries.value.length / itemsPerPage.value)
})

const showingText = computed(() => {
    if (useServer.value) {
        const total = serverTotal.value
        if (total === 0) return ''
        const start = (serverCurrentPage.value - 1) * serverPerPage.value + 1
        const end = Math.min(serverCurrentPage.value * serverPerPage.value, total)
        return `Showing ${start}-${end} of ${total} galleries`
    }

    const total = filteredGalleries.value.length
    if (total === 0) return ''
    const start = (currentPage.value - 1) * itemsPerPage.value + 1
    const end = Math.min(currentPage.value * itemsPerPage.value, total)
    return `Showing ${start}-${end} of ${total} galleries`
})

const currentStart = computed(() => {
    if (useServer.value) return serverTotal.value === 0 ? 0 : (serverCurrentPage.value - 1) * serverPerPage.value + 1
    if (filteredGalleries.value.length === 0) return 0
    return (currentPage.value - 1) * itemsPerPage.value + 1
})

const currentEnd = computed(() => {
    if (useServer.value) return Math.min(serverCurrentPage.value * serverPerPage.value, serverTotal.value)
    const end = currentPage.value * itemsPerPage.value
    return Math.min(end, filteredGalleries.value.length)
})

// Filter methods
const handleSearch = async () => {
    currentFilters.search = searchInput.value
    // If using server, request page 1 with search applied
    if (useServer.value) {
        serverCurrentPage.value = 1
        await loadGalleries()
        return
    }

    currentPage.value = 1
}

const handleApplyFilters = (filters: typeof currentFilters) => {
    Object.assign(currentFilters, filters)
    currentPage.value = 1
    showAdvancedFilter.value = false
}

const handleResetFilters = () => {
    searchInput.value = ''
    Object.assign(currentFilters, {
        search: '',
        category: '',
        status: '',
        min_items: '',
        max_items: '',
        date_from: '',
        date_to: ''
    })
    currentPage.value = 1
}

const refreshGalleries = async () => {
    if (useServer.value) {
        await loadGalleries()
        await showToast({ icon: 'success', title: 'Refreshed', text: 'Gallery list has been refreshed', timer: 1200 })
        return
    }

    await showToast({ icon: 'success', title: 'Refreshed', text: 'Gallery list has been refreshed', timer: 1500 })
}

// Methods
const goToCreateGallery = () => {
    router.push({ name: 'gallery_management.create' })
}

const viewGallery = (gallery: any) => {
    selectedGallery.value = gallery
    showDetailModal.value = true
}

const editGallery = (gallery: any) => {
    router.push({ name: 'gallery_management.edit', params: { id: gallery.id } })
}

// Load galleries from server (or fallback to mocks)
async function loadGalleries() {
    loading.value = true
    try {
        const params: Record<string, any> = {
            page: useServer.value ? serverCurrentPage.value : currentPage.value,
            per_page: itemsPerPage.value
        }
        if (currentFilters.search) params.search = currentFilters.search
        if (currentFilters.category) params.category_id = currentFilters.category

        const result = await fetchGalleries(params).catch(() => null)
        if (!result) {
            useServer.value = false
            galleries.value = []
            serverTotal.value = 0
            await showToast({ icon: 'error', title: 'Error', text: 'Failed to load galleries from server' })
            return
        }

        useServer.value = true

        // result contains { galleries: [...], total, per_page, current_page, total_pages }
        serverTotal.value = result.total || 0
        serverPerPage.value = result.per_page || itemsPerPage.value
        serverCurrentPage.value = result.current_page || 1
        serverTotalPages.value = result.total_pages || 1

        // map API gallery model to UI shape (include tags, slug, updatedAt so Quick View can reuse list object)
        galleries.value = (result.galleries || []).map((g: any) => {
            const media = Array.isArray(g.media) ? g.media : []
            // Prefer the explicit `is_used_as_cover` flag when present, otherwise fall back to `is_cover` for compatibility
            const primary = media.find((m: any) => (m.is_used_as_cover || m.is_cover)) || (g.cover ? g.cover : null)
            return {
                id: g.id,
                title: g.title,
                description: g.description || '',
                category: (categories.value.find((c: any) => c.id === g.category_id)?.name) || '',
                itemCount: g.item_count || 0,
                coverImage: primary ? (primary.url || primary) : '',
                media: media,
                status: g.is_active ? 'active' : 'inactive',
                createdAt: g.created_at,
                updatedAt: g.updated_at || null,
                slug: g.slug || '',
                tags: Array.isArray(g.tags) ? g.tags.map((t: any) => (t.name || t)) : []
            }
        })
    } catch (err: any) {
        useServer.value = false
        galleries.value = []
        console.error('Failed to load galleries:', err)
        await showToast({ icon: 'error', title: 'Error', text: err?.message || 'Failed to load galleries' })
    } finally {
        loading.value = false
    }
}
const confirmDelete = async (gallery: any) => {
    const confirmed = await showConfirm({
        title: 'Delete Gallery?',
        text: `Are you sure you want to delete "${gallery.title}"? This action cannot be undone and will remove all ${gallery.itemCount} items in this gallery.`,
        icon: 'warning',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    })

    if (!confirmed) return

    if (useServer.value) {
        try {
            await deleteGallery(gallery.id)
            await loadGalleries()
            await showToast({ icon: 'success', title: 'Deleted!', text: `Gallery "${gallery.title}" has been deleted.`, timer: 2000 })
        } catch (err: any) {
            await showToast({ icon: 'error', title: 'Error', text: err.message || 'Failed to delete gallery' })
        }
        return
    }

    // Client-side (mock) delete
    const index = galleries.value.findIndex(g => g.id === gallery.id)
    if (index !== -1) {
        galleries.value.splice(index, 1)
        await showToast({ icon: 'success', title: 'Deleted!', text: `Gallery "${gallery.title}" has been deleted.`, timer: 2000 })
    }
}

// Pagination methods
const prevPage = async () => {
    if (useServer.value) {
        if (serverCurrentPage.value > 1) {
            serverCurrentPage.value--
            await loadGalleries()
        }
        return
    }

    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = async () => {
    if (useServer.value) {
        if (serverCurrentPage.value < serverTotalPages.value) {
            serverCurrentPage.value++
            await loadGalleries()
        }
        return
    }

    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const goToPage = async (page: number) => {
    if (useServer.value) {
        if (page >= 1 && page <= serverTotalPages.value) {
            serverCurrentPage.value = page
            await loadGalleries()
        }
        return
    }

    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 2;
}
</style>

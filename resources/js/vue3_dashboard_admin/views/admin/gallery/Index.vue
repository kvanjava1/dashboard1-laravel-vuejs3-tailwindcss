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
                        Add New Gallery
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
                <div class="mb-6 space-y-4">
                    <!-- Search and Quick Filters -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Search Bar -->
                        <div class="flex-1">
                            <div class="relative">
                                <input v-model="currentFilters.search" type="text"
                                    placeholder="Search galleries by title, description, or category..."
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                    @input="handleSearch" />
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">
                                    search
                                </span>
                            </div>
                        </div>

                        <!-- Quick Filters -->
                        <div class="flex gap-3">
                            <select v-model="currentFilters.category"
                                class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.name">
                                    {{ cat.name }}
                                </option>
                            </select>

                            <select v-model="currentFilters.status"
                                class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Active Filters Indicator -->
                    <ActiveFiltersIndicator :has-active-filters="hasActiveFilters" @reset="handleResetFilters" />
                </div>

                <!-- View Toggle -->
                <div class="flex justify-between items-center mb-4">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-semibold">{{ filteredGalleries.length }}</span> of <span
                            class="font-semibold">{{ galleries.length }}</span> galleries
                    </div>
                    <div class="flex gap-2">
                        <button @click="viewMode = 'grid'" :class="[
                            'p-2 rounded-lg transition-colors',
                            viewMode === 'grid' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        ]" title="Grid View">
                            <span class="material-symbols-outlined text-xl">grid_view</span>
                        </button>
                        <button @click="viewMode = 'list'" :class="[
                            'p-2 rounded-lg transition-colors',
                            viewMode === 'list' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        ]" title="List View">
                            <span class="material-symbols-outlined text-xl">view_list</span>
                        </button>
                    </div>
                </div>

                <!-- Grid View -->
                <div v-if="viewMode === 'grid' && filteredGalleries.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="gallery in filteredGalleries" :key="gallery.id"
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

                <!-- List View -->
                <div v-if="viewMode === 'list' && filteredGalleries.length > 0" class="space-y-3">
                    <div v-for="gallery in filteredGalleries" :key="gallery.id"
                        class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200 border border-gray-100">
                        <div class="flex gap-4 p-4">
                            <!-- Thumbnail -->
                            <div class="w-32 h-24 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                <img :src="gallery.coverImage" :alt="gallery.title"
                                    class="w-full h-full object-cover" />
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-lg mb-1">{{ gallery.title }}</h3>
                                        <p class="text-sm text-gray-600 line-clamp-2">{{ gallery.description }}</p>
                                    </div>
                                    <StatusBadge :status="gallery.status" class="ml-4" />
                                </div>

                                <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">category</span>
                                        {{ gallery.category }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">photo_library</span>
                                        {{ gallery.itemCount }} items
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">schedule</span>
                                        {{ gallery.createdAt }}
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <Button variant="outline" size="sm" left-icon="visibility"
                                        @click="viewGallery(gallery)">
                                        View
                                    </Button>
                                    <Button variant="outline" size="sm" left-icon="edit" @click="editGallery(gallery)">
                                        Edit
                                    </Button>
                                    <Button variant="danger" size="sm" left-icon="delete"
                                        @click="confirmDelete(gallery)">
                                        Delete
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
import { ref, computed, reactive } from 'vue'
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

// Router
const router = useRouter()

// Reactive state
const showAdvancedFilter = ref(false)
const showDetailModal = ref(false)
const selectedGallery = ref<any>(null)
const viewMode = ref<'grid' | 'list'>('grid')

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
const categories = ref([
    { id: 1, name: 'Photography' },
    { id: 2, name: 'Architecture' },
    { id: 3, name: 'Food' },
    { id: 4, name: 'Travel' },
    { id: 5, name: 'Art' },
    { id: 6, name: 'Wildlife' },
    { id: 7, name: 'Events' },
    { id: 8, name: 'Products' }
])

// Dummy data - Enhanced with more galleries
const galleries = ref([
    {
        id: 1,
        title: 'Nature Photography Collection',
        description: 'Beautiful landscapes and natural scenes captured around the world. From majestic mountains to serene beaches.',
        coverImage: 'https://picsum.photos/seed/nature1/400/300',
        category: 'Photography',
        itemCount: 24,
        status: 'active',
        createdAt: '2024-01-15'
    },
    {
        id: 2,
        title: 'Urban Architecture',
        description: 'Modern and classic architectural designs from city landscapes. Showcasing the beauty of urban structures.',
        coverImage: 'https://picsum.photos/seed/arch1/400/300',
        category: 'Architecture',
        itemCount: 18,
        status: 'active',
        createdAt: '2024-01-20'
    },
    {
        id: 3,
        title: 'Food & Cuisine Masterpieces',
        description: 'Delicious food photography showcasing culinary arts from around the globe.',
        coverImage: 'https://picsum.photos/seed/food1/400/300',
        category: 'Food',
        itemCount: 32,
        status: 'active',
        createdAt: '2024-02-01'
    },
    {
        id: 4,
        title: 'Travel Adventures 2024',
        description: 'Memorable moments from travel destinations worldwide. Capturing the essence of different cultures.',
        coverImage: 'https://picsum.photos/seed/travel1/400/300',
        category: 'Travel',
        itemCount: 45,
        status: 'active',
        createdAt: '2024-02-10'
    },
    {
        id: 5,
        title: 'Contemporary Art Collection',
        description: 'Contemporary and traditional art pieces from various artists. A curated selection of modern masterpieces.',
        coverImage: 'https://picsum.photos/seed/art1/400/300',
        category: 'Art',
        itemCount: 16,
        status: 'inactive',
        createdAt: '2024-01-05'
    },
    {
        id: 6,
        title: 'Wildlife in Natural Habitat',
        description: 'Amazing wildlife photography capturing animals in their natural habitat. Rare and beautiful moments.',
        coverImage: 'https://picsum.photos/seed/wild1/400/300',
        category: 'Wildlife',
        itemCount: 28,
        status: 'active',
        createdAt: '2024-02-15'
    },
    {
        id: 7,
        title: 'Corporate Events 2024',
        description: 'Professional coverage of corporate events, conferences, and business gatherings.',
        coverImage: 'https://picsum.photos/seed/event1/400/300',
        category: 'Events',
        itemCount: 52,
        status: 'active',
        createdAt: '2024-01-25'
    },
    {
        id: 8,
        title: 'Product Photography Studio',
        description: 'High-quality product shots for e-commerce and marketing purposes.',
        coverImage: 'https://picsum.photos/seed/product1/400/300',
        category: 'Products',
        itemCount: 38,
        status: 'active',
        createdAt: '2024-02-05'
    },
    {
        id: 9,
        title: 'Black & White Classics',
        description: 'Timeless black and white photography showcasing emotion and contrast.',
        coverImage: 'https://picsum.photos/seed/bw1/400/300',
        category: 'Photography',
        itemCount: 21,
        status: 'active',
        createdAt: '2024-01-30'
    },
    {
        id: 10,
        title: 'Street Food Adventures',
        description: 'Vibrant street food photography from markets around the world.',
        coverImage: 'https://picsum.photos/seed/street1/400/300',
        category: 'Food',
        itemCount: 29,
        status: 'inactive',
        createdAt: '2024-02-08'
    }
])

// Computed
const hasActiveFilters = computed(() => {
    return currentFilters.search.trim() !== '' ||
        currentFilters.category !== '' ||
        currentFilters.status !== '' ||
        currentFilters.min_items !== '' ||
        currentFilters.max_items !== ''
})

const filteredGalleries = computed(() => {
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
        filtered = filtered.filter(gallery => gallery.category === currentFilters.category)
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

// Filter methods
const handleSearch = () => {
    // Debounce could be added here if needed
}

const handleApplyFilters = (filters: typeof currentFilters) => {
    Object.assign(currentFilters, filters)
    showAdvancedFilter.value = false
}

const handleResetFilters = () => {
    Object.assign(currentFilters, {
        search: '',
        category: '',
        status: '',
        min_items: '',
        max_items: '',
        date_from: '',
        date_to: ''
    })
}

const refreshGalleries = async () => {
    await showToast({
        icon: 'success',
        title: 'Refreshed',
        text: 'Gallery list has been refreshed',
        timer: 1500
    })
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

const confirmDelete = async (gallery: any) => {
    const confirmed = await showConfirm({
        title: 'Delete Gallery?',
        text: `Are you sure you want to delete "${gallery.title}"? This action cannot be undone and will remove all ${gallery.itemCount} items in this gallery.`,
        icon: 'warning',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    })

    if (confirmed) {
        // Simulate delete
        const index = galleries.value.findIndex(g => g.id === gallery.id)
        if (index !== -1) {
            galleries.value.splice(index, 1)
            await showToast({
                icon: 'success',
                title: 'Deleted!',
                text: `Gallery "${gallery.title}" has been deleted.`,
                timer: 2000
            })
        }
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

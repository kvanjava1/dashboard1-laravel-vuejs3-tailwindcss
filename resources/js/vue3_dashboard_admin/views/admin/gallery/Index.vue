<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Gallery" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="primary" icon="add" @click="goToCreateGallery">
                        Add Gallery
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Content Box -->
        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="Galleries" subtitle="Manage your image galleries and collections" />
                </template>
            </ContentBoxHeader>

            <!-- Gallery Grid -->
            <ContentBoxBody>
                <!-- Filters -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <!-- Search Bar -->
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <input
                                    v-model="currentFilters.search"
                                    type="text"
                                    placeholder="Search galleries..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                                <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">
                                    search
                                </span>
                            </div>
                        </div>

                        <!-- Filter Toggle Button -->
                        <div class="w-32">
                            <ActionButton
                                variant="secondary"
                                icon="filter_list"
                                @click="showAdvancedFilter = true"
                                class="w-full"
                            >
                                Filters
                            </ActionButton>
                        </div>
                    </div>
                </div>

                <!-- Gallery Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="gallery in filteredGalleries"
                        :key="gallery.id"
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 border border-gray-200"
                    >
                        <!-- Gallery Image -->
                        <div class="aspect-video bg-gray-200 relative">
                            <img
                                :src="gallery.coverImage"
                                :alt="gallery.title"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute top-2 right-2">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        gallery.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ gallery.status }}
                                </span>
                            </div>
                        </div>

                        <!-- Gallery Info -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ gallery.title }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ gallery.description }}</p>

                            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span>{{ gallery.category }}</span>
                                <span>{{ gallery.itemCount }} items</span>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <ActionButton
                                    variant="secondary"
                                    size="sm"
                                    icon="visibility"
                                    @click="viewGallery(gallery)"
                                >
                                    View
                                </ActionButton>
                                <ActionButton
                                    variant="secondary"
                                    size="sm"
                                    icon="edit"
                                    @click="editGallery(gallery)"
                                >
                                    Edit
                                </ActionButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="filteredGalleries.length === 0"
                    icon="photo_library"
                    message="No Galleries Found"
                    subtitle="Get started by creating your first gallery to showcase your images and media."
                />
            </ContentBoxBody>
        </ContentBox>

        <!-- Advanced Filter Modal -->
        <GalleryAdvancedFilterModal
            v-model="showAdvancedFilter"
            :initial-filters="currentFilters"
            :categories="categories"
            @apply="handleApplyFilters"
            @reset="handleResetFilters"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import EmptyState from '../../../components/ui/EmptyState.vue'
import GalleryAdvancedFilterModal from '../../../components/gallery/GalleryAdvancedFilterModal.vue'

// Router
const router = useRouter()

// Reactive state
const showAdvancedFilter = ref(false)

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
    { id: 6, name: 'Wildlife' }
])

// Dummy data
const galleries = ref([
    {
        id: 1,
        title: 'Nature Photography',
        description: 'Beautiful landscapes and natural scenes captured around the world.',
        coverImage: 'https://picsum.photos/400/300?random=1',
        category: 'Photography',
        itemCount: 24,
        status: 'active'
    },
    {
        id: 2,
        title: 'Urban Architecture',
        description: 'Modern and classic architectural designs from city landscapes.',
        coverImage: 'https://picsum.photos/400/300?random=2',
        category: 'Architecture',
        itemCount: 18,
        status: 'active'
    },
    {
        id: 3,
        title: 'Food & Cuisine',
        description: 'Delicious food photography showcasing culinary arts.',
        coverImage: 'https://picsum.photos/400/300?random=3',
        category: 'Food',
        itemCount: 32,
        status: 'active'
    },
    {
        id: 4,
        title: 'Travel Adventures',
        description: 'Memorable moments from travel destinations worldwide.',
        coverImage: 'https://picsum.photos/400/300?random=4',
        category: 'Travel',
        itemCount: 45,
        status: 'active'
    },
    {
        id: 5,
        title: 'Art Collection',
        description: 'Contemporary and traditional art pieces from various artists.',
        coverImage: 'https://picsum.photos/400/300?random=5',
        category: 'Art',
        itemCount: 16,
        status: 'inactive'
    },
    {
        id: 6,
        title: 'Wildlife',
        description: 'Amazing wildlife photography capturing animals in their natural habitat.',
        coverImage: 'https://picsum.photos/400/300?random=6',
        category: 'Wildlife',
        itemCount: 28,
        status: 'active'
    }
])

// Computed
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
const handleApplyFilters = (filters: typeof currentFilters) => {
    Object.assign(currentFilters, filters)
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

// Methods
const goToCreateGallery = () => {
    router.push('/gallery_management/create')
}

const viewGallery = (gallery: any) => {
    console.log('View gallery:', gallery)
    // TODO: Implement view gallery functionality
}

const editGallery = (gallery: any) => {
    console.log('Edit gallery:', gallery)
    // TODO: Implement edit gallery functionality
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

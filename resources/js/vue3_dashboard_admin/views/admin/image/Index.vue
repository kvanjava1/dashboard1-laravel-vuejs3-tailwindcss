<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Image" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="primary" icon="add" @click="showUploadModal = true">
                        Upload Image
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Content Box -->
        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="Images" subtitle="Manage and organize your image files" />
                </template>
            </ContentBoxHeader>

            <!-- Image Grid -->
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
                                    placeholder="Search images..."
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

                <!-- Image Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="image in filteredImages"
                        :key="image.id"
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 border border-gray-200 group cursor-pointer"
                        @click="viewImage(image)"
                    >
                        <!-- Image -->
                        <div class="aspect-video bg-gray-200 relative">
                            <img
                                :src="image.url"
                                :alt="image.name"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-black bg-opacity-50 text-white">
                                    {{ image.size }}
                                </span>
                            </div>
                        </div>

                        <!-- Image Info -->
                        <div class="p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">{{ image.name }}</h4>
                            <p class="text-sm text-gray-600 mb-2">{{ image.gallery }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ image.uploadDate }}</span>
                                <div class="flex gap-2">
                                    <button
                                        @click.stop="editImage(image)"
                                        class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click.stop="deleteImage(image)"
                                        class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="filteredImages.length === 0"
                    icon="image"
                    message="No Images Found"
                    subtitle="Upload your first image to get started."
                />
            </ContentBoxBody>
        </ContentBox>

        <!-- Upload Image Modal -->
        <BaseModal v-model="showUploadModal" title="Upload Image" size="lg">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Gallery</label>
                    <select
                        v-model="uploadForm.galleryId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        <option value="">Choose a gallery...</option>
                        <option v-for="gallery in galleries" :key="gallery.id" :value="gallery.id">
                            {{ gallery.title }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image File</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                        <input
                            type="file"
                            ref="fileInput"
                            @change="handleFileSelect"
                            accept="image/*"
                            multiple
                            class="hidden"
                        />
                        <div v-if="selectedFiles.length === 0" @click="() => fileInput?.click()" class="cursor-pointer">
                            <span class="material-symbols-outlined text-gray-400 text-3xl">cloud_upload</span>
                            <p class="mt-2 text-sm text-gray-600">Click to upload images</p>
                            <p class="text-xs text-gray-400">PNG, JPG, GIF up to 10MB each</p>
                        </div>
                        <div v-else class="space-y-2">
                            <p class="text-sm font-medium">{{ selectedFiles.length }} file(s) selected</p>
                            <div class="flex flex-wrap gap-2 justify-center">
                                <span
                                    v-for="(file, index) in selectedFiles"
                                    :key="index"
                                    class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"
                                >
                                    {{ file.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <ActionButton variant="secondary" @click="cancelUpload">
                        Cancel
                    </ActionButton>
                    <ActionButton variant="primary" @click="uploadImages" :disabled="selectedFiles.length === 0">
                        Upload
                    </ActionButton>
                </div>
            </template>
        </BaseModal>

        <!-- Image View Modal -->
        <BaseModal v-model="showViewModal" title="View Image" size="xl">
            <div v-if="selectedImage" class="text-center">
                <img
                    :src="selectedImage.url"
                    :alt="selectedImage.name"
                    class="max-w-full max-h-96 mx-auto rounded-lg shadow-lg"
                />
                <div class="mt-4 space-y-2">
                    <h3 class="text-lg font-semibold">{{ selectedImage.name }}</h3>
                    <p class="text-sm text-gray-600">Gallery: {{ selectedImage.gallery }}</p>
                    <p class="text-sm text-gray-500">Uploaded: {{ selectedImage.uploadDate }}</p>
                    <p class="text-sm text-gray-500">Size: {{ selectedImage.size }}</p>
                </div>
            </div>
        </BaseModal>

        <!-- Advanced Filter Modal -->
        <ImageAdvancedFilterModal
            v-model="showAdvancedFilter"
            :initial-filters="currentFilters"
            :categories="categories"
            :galleries="galleries"
            @apply="handleApplyFilters"
            @reset="handleResetFilters"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
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
import BaseModal from '../../../components/ui/BaseModal.vue'
import ImageAdvancedFilterModal from '../../../components/image/ImageAdvancedFilterModal.vue'

// Reactive data
const showUploadModal = ref(false)
const showViewModal = ref(false)
const showAdvancedFilter = ref(false)
const selectedCategory = ref('')
const selectedGallery = ref('')
const selectedImage = ref<any>(null)
const selectedFiles = ref<File[]>([])
const fileInput = ref<HTMLInputElement>()

// Current filters
const currentFilters = reactive({
    search: '',
    category: '',
    gallery: '',
    date_from: '',
    date_to: '',
    file_type: '',
    size_range: ''
})

// Upload form
const uploadForm = reactive({
    galleryId: ''
})

// Categories data
const categories = ref([
    { id: 1, name: 'Holiday' },
    { id: 2, name: 'Graduation' },
    { id: 3, name: 'Wedding' },
    { id: 4, name: 'Family' },
    { id: 5, name: 'Travel' },
    { id: 6, name: 'Events' }
])

// Galleries data (with category relationships)
const galleries = ref([
    { id: 1, title: 'Paris Trip 2024', categoryId: 1 },
    { id: 2, title: 'New York Vacation', categoryId: 1 },
    { id: 3, title: 'Bachelor\'s Degree Ceremony', categoryId: 2 },
    { id: 4, title: 'Doctoral Graduation', categoryId: 2 },
    { id: 5, title: 'Summer Wedding', categoryId: 3 },
    { id: 6, title: 'Family Reunion', categoryId: 4 },
    { id: 7, title: 'Japan Adventure', categoryId: 5 },
    { id: 8, title: 'Corporate Event', categoryId: 6 }
])

// Dummy images data
const images = ref([
    {
        id: 1,
        name: 'eiffel-tower.jpg',
        url: 'https://picsum.photos/300/300?random=1',
        gallery: 'Paris Trip 2024',
        galleryId: 1,
        uploadDate: '2024-01-15',
        size: '2.4 MB'
    },
    {
        id: 2,
        name: 'times-square.png',
        url: 'https://picsum.photos/300/300?random=2',
        gallery: 'New York Vacation',
        galleryId: 2,
        uploadDate: '2024-01-14',
        size: '1.8 MB'
    },
    {
        id: 3,
        name: 'graduation-cap.jpg',
        url: 'https://picsum.photos/300/300?random=3',
        gallery: 'Bachelor\'s Degree Ceremony',
        galleryId: 3,
        uploadDate: '2024-01-13',
        size: '3.1 MB'
    },
    {
        id: 4,
        name: 'doctoral-gown.jpg',
        url: 'https://picsum.photos/300/300?random=4',
        gallery: 'Doctoral Graduation',
        galleryId: 4,
        uploadDate: '2024-01-12',
        size: '2.9 MB'
    },
    {
        id: 5,
        name: 'wedding-ceremony.gif',
        url: 'https://picsum.photos/300/300?random=5',
        gallery: 'Summer Wedding',
        galleryId: 5,
        uploadDate: '2024-01-11',
        size: '4.2 MB'
    },
    {
        id: 6,
        name: 'family-gathering.jpg',
        url: 'https://picsum.photos/300/300?random=6',
        gallery: 'Family Reunion',
        galleryId: 6,
        uploadDate: '2024-01-10',
        size: '1.5 MB'
    },
    {
        id: 7,
        name: 'tokyo-street.jpg',
        url: 'https://picsum.photos/300/300?random=7',
        gallery: 'Japan Adventure',
        galleryId: 7,
        uploadDate: '2024-01-09',
        size: '3.5 MB'
    },
    {
        id: 8,
        name: 'corporate-event.jpg',
        url: 'https://picsum.photos/300/300?random=8',
        gallery: 'Corporate Event',
        galleryId: 8,
        uploadDate: '2024-01-08',
        size: '2.1 MB'
    }
])

// Computed
const filteredGalleries = computed(() => {
    if (!currentFilters.category) return galleries.value
    return galleries.value.filter(gallery => gallery.categoryId === parseInt(currentFilters.category))
})

const filteredImages = computed(() => {
    let filtered = images.value

    // Filter by search term
    if (currentFilters.search.trim()) {
        const searchTerm = currentFilters.search.toLowerCase()
        filtered = filtered.filter(image =>
            image.name.toLowerCase().includes(searchTerm) ||
            image.gallery.toLowerCase().includes(searchTerm)
        )
    }

    // Filter by category if selected
    if (currentFilters.category) {
        const categoryGalleries = galleries.value.filter(g => g.categoryId === parseInt(currentFilters.category))
        const categoryGalleryIds = categoryGalleries.map(g => g.id)
        filtered = filtered.filter(image => categoryGalleryIds.includes(image.galleryId))
    }

    // Filter by gallery if selected
    if (currentFilters.gallery) {
        filtered = filtered.filter(image => image.galleryId === parseInt(currentFilters.gallery))
    }

    // Filter by date range
    if (currentFilters.date_from) {
        filtered = filtered.filter(image => image.uploadDate >= currentFilters.date_from)
    }
    if (currentFilters.date_to) {
        filtered = filtered.filter(image => image.uploadDate <= currentFilters.date_to)
    }

    // Filter by file type
    if (currentFilters.file_type) {
        const fileExtension = currentFilters.file_type
        filtered = filtered.filter(image => image.name.toLowerCase().endsWith(`.${fileExtension}`))
    }

    // Filter by size range
    if (currentFilters.size_range) {
        filtered = filtered.filter(image => {
            const sizeInMB = parseFloat(image.size.replace(' MB', ''))
            switch (currentFilters.size_range) {
                case 'small': return sizeInMB < 1
                case 'medium': return sizeInMB >= 1 && sizeInMB <= 5
                case 'large': return sizeInMB > 5
                default: return true
            }
        })
    }

    return filtered
})

// Methods
const handleApplyFilters = (filters: typeof currentFilters) => {
    Object.assign(currentFilters, filters)
}

const handleResetFilters = () => {
    Object.assign(currentFilters, {
        search: '',
        category: '',
        gallery: '',
        date_from: '',
        date_to: '',
        file_type: '',
        size_range: ''
    })
}

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files) {
        selectedFiles.value = Array.from(target.files)
    }
}

const cancelUpload = () => {
    showUploadModal.value = false
    selectedFiles.value = []
    uploadForm.galleryId = ''
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const uploadImages = () => {
    console.log('Uploading images:', selectedFiles.value)
    console.log('To gallery:', uploadForm.galleryId)
    // TODO: Implement upload functionality
    cancelUpload()
}

const viewImage = (image: any) => {
    selectedImage.value = image
    showViewModal.value = true
}

const editImage = (image: any) => {
    console.log('Edit image:', image)
    // TODO: Implement edit functionality
}

const deleteImage = (image: any) => {
    console.log('Delete image:', image)
    // TODO: Implement delete functionality
}
</script>

<style scoped>
</style>
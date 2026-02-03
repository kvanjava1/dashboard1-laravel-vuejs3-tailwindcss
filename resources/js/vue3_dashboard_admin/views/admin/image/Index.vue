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
                <!-- Gallery Filter -->
                <div class="mb-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <select
                                v-model="selectedGallery"
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                                <option value="">All Galleries</option>
                                <option v-for="gallery in galleries" :key="gallery.id" :value="gallery.id">
                                    {{ gallery.title }}
                                </option>
                            </select>
                        </div>
                        <ActionButton variant="secondary" icon="filter_list" @click="showFilters = !showFilters">
                            Filters
                        </ActionButton>
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
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
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

// Reactive data
const showUploadModal = ref(false)
const showViewModal = ref(false)
const showFilters = ref(false)
const selectedGallery = ref('')
const selectedImage = ref<any>(null)
const selectedFiles = ref<File[]>([])
const fileInput = ref<HTMLInputElement>()

// Upload form
const uploadForm = ref({
    galleryId: ''
})

// Galleries data (same as gallery management)
const galleries = ref([
    { id: 1, title: 'Nature Photography' },
    { id: 2, title: 'Urban Architecture' },
    { id: 3, title: 'Food & Cuisine' },
    { id: 4, title: 'Travel Adventures' },
    { id: 5, title: 'Art Collection' },
    { id: 6, title: 'Wildlife' }
])

// Dummy images data
const images = ref([
    {
        id: 1,
        name: 'sunset-landscape.jpg',
        url: 'https://picsum.photos/300/300?random=1',
        gallery: 'Nature Photography',
        galleryId: 1,
        uploadDate: '2024-01-15',
        size: '2.4 MB'
    },
    {
        id: 2,
        name: 'city-skyline.png',
        url: 'https://picsum.photos/300/300?random=2',
        gallery: 'Urban Architecture',
        galleryId: 2,
        uploadDate: '2024-01-14',
        size: '1.8 MB'
    },
    {
        id: 3,
        name: 'delicious-pasta.jpg',
        url: 'https://picsum.photos/300/300?random=3',
        gallery: 'Food & Cuisine',
        galleryId: 3,
        uploadDate: '2024-01-13',
        size: '3.1 MB'
    },
    {
        id: 4,
        name: 'mountain-view.jpg',
        url: 'https://picsum.photos/300/300?random=4',
        gallery: 'Nature Photography',
        galleryId: 1,
        uploadDate: '2024-01-12',
        size: '2.9 MB'
    },
    {
        id: 5,
        name: 'modern-building.gif',
        url: 'https://picsum.photos/300/300?random=5',
        gallery: 'Urban Architecture',
        galleryId: 2,
        uploadDate: '2024-01-11',
        size: '4.2 MB'
    },
    {
        id: 6,
        name: 'coffee-art.jpg',
        url: 'https://picsum.photos/300/300?random=6',
        gallery: 'Food & Cuisine',
        galleryId: 3,
        uploadDate: '2024-01-10',
        size: '1.5 MB'
    }
])

// Computed
const filteredImages = computed(() => {
    if (!selectedGallery.value) return images.value
    return images.value.filter(image => image.galleryId === parseInt(selectedGallery.value))
})

// Methods
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files) {
        selectedFiles.value = Array.from(target.files)
    }
}

const cancelUpload = () => {
    showUploadModal.value = false
    selectedFiles.value = []
    uploadForm.value.galleryId = ''
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const uploadImages = () => {
    console.log('Uploading images:', selectedFiles.value)
    console.log('To gallery:', uploadForm.value.galleryId)
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
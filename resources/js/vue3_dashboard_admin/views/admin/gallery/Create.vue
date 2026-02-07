<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Create Gallery" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="secondary" icon="arrow_back" @click="goBack">
                        Back to Galleries
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Content Box -->
        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="Gallery Information"
                        subtitle="Fill in the details to create a new gallery" />
                </template>
            </ContentBoxHeader>

            <!-- Form -->
            <ContentBoxBody>
                <form @submit.prevent="createGallery" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Gallery Title *</label>
                            <input v-model="form.title" type="text"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                placeholder="Enter gallery title" required />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                            <select v-model="form.category"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer">
                                <option value="">Select category</option>
                                <option value="Photography">Photography</option>
                                <option value="Architecture">Architecture</option>
                                <option value="Food">Food</option>
                                <option value="Travel">Travel</option>
                                <option value="Art">Art</option>
                                <option value="Wildlife">Wildlife</option>
                                <option value="Events">Events</option>
                                <option value="Products">Products</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                        <textarea v-model="form.description" rows="4"
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            placeholder="Describe your gallery..."></textarea>
                    </div>

                    <!-- Cover Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image *</label>

                        <!-- Cropper Modal -->
                        <div v-if="showCropper"
                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                            <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
                                <h3 class="text-lg font-semibold mb-4 text-center">Crop Gallery Cover Image</h3>

                                <!-- Vue Advanced Cropper -->
                                <div class="mb-4">
                                    <Cropper ref="cropperRef" :src="cropperImage" :stencil-props="{
                                        aspectRatio: 4 / 3
                                    }" :canvas="{
                                        height: 300,
                                        width: 400
                                    }" />
                                </div>

                                <!-- Crop Controls -->
                                <div class="flex justify-center gap-3">
                                    <ActionButton variant="secondary" @click="cancelCrop">
                                        Cancel
                                    </ActionButton>
                                    <ActionButton variant="primary" @click="applyCrop">
                                        <span class="material-symbols-outlined mr-2">crop</span>
                                        Apply Crop
                                    </ActionButton>
                                </div>
                            </div>
                        </div>

                        <div
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                            <input type="file" ref="coverImageInput" @change="handleCoverImageSelect"
                                accept="image/jpeg,image/png,image/webp" class="hidden" />
                            <div v-if="!coverImagePreview" @click="() => coverImageInput?.click()"
                                class="cursor-pointer">
                                <span class="material-symbols-outlined text-gray-400 text-3xl">cloud_upload</span>
                                <p class="mt-2 text-sm text-gray-600">Click to upload cover image</p>
                                <p class="text-xs text-gray-400">JPG, PNG, WebP up to 5MB</p>
                                <p class="text-xs text-gray-400">Will be cropped to 4:3 ratio</p>
                            </div>
                            <div v-else class="space-y-4">
                                <img :src="coverImagePreview" alt="Cover preview"
                                    class="max-w-full max-h-48 mx-auto rounded-lg shadow-md" />
                                <div class="flex justify-center gap-2">
                                    <ActionButton variant="secondary" size="sm" @click="editImage">
                                        <span class="material-symbols-outlined mr-1">crop</span>
                                        Crop
                                    </ActionButton>
                                    <ActionButton variant="secondary" size="sm" @click="() => coverImageInput?.click()">
                                        Change Image
                                    </ActionButton>
                                    <ActionButton variant="secondary" size="sm" @click="removeCoverImage">
                                        Remove
                                    </ActionButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                            <select v-model="form.status"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Visibility</label>
                            <select v-model="form.visibility"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer">
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tags</label>
                        <div class="relative">
                            <input v-model="tagInput" @input="filterTags" @keydown="handleTagKeydown"
                                @focus="showSuggestions = true" @blur="hideSuggestions" type="text"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                placeholder="Type to search or create tags..." />
                            <!-- Suggestions Dropdown -->
                            <div v-if="showSuggestions && (filteredTags.length > 0 || (tagInput.trim() && !existingTags.some(t => t.toLowerCase() === tagInput.trim().toLowerCase())))"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                <!-- Existing tags -->
                                <div v-if="filteredTags.length > 0" class="border-b border-gray-200">
                                    <div class="px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Existing Tags
                                    </div>
                                    <div v-for="(tag, index) in filteredTags" :key="tag"
                                        @mousedown.prevent="selectTag(tag)"
                                        class="px-3 py-2 hover:bg-gray-50 cursor-pointer flex items-center justify-between transition-colors"
                                        :class="{ 'bg-primary text-white': highlightedIndex === index }">
                                        <span>{{ tag }}</span>
                                        <span class="material-symbols-outlined text-xs"
                                            :class="highlightedIndex === index ? 'text-white' : 'text-gray-400'">add</span>
                                    </div>
                                </div>
                                <!-- Create new tag -->
                                <div v-if="tagInput.trim() && !existingTags.some(t => t.toLowerCase() === tagInput.trim().toLowerCase())"
                                    @mousedown.prevent="createNewTag"
                                    class="px-3 py-2 cursor-pointer flex items-center justify-between transition-colors"
                                    :class="{ 'bg-primary text-white': highlightedIndex === filteredTags.length }">
                                    <span
                                        :class="highlightedIndex === filteredTags.length ? 'text-white' : 'text-gray-700'">Create
                                        "{{ tagInput.trim() }}"</span>
                                    <span class="material-symbols-outlined text-xs"
                                        :class="highlightedIndex === filteredTags.length ? 'text-white' : 'text-primary'">add_circle</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2 mt-2">
                            <span v-for="(tag, index) in form.tags" :key="index"
                                class="px-3 py-1.5 rounded-full text-sm font-bold border border-border-light text-slate-700 hover:bg-white hover:border-slate-300 hover:shadow-medium transition-all duration-200 flex items-center gap-2">
                                {{ tag }}
                                <Button type="button" variant="ghost" size="xs"
                                    class="!p-0 hover:!bg-transparent hover:!text-red-500" @click="removeTag(index)"
                                    title="Remove tag">
                                    <span class="material-symbols-outlined text-xs">close</span>
                                </Button>
                            </span>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                        <ActionButton variant="secondary" @click="goBack">
                            Cancel
                        </ActionButton>
                        <ActionButton variant="primary" type="submit" :disabled="loading">
                            <span v-if="loading"
                                class="material-symbols-outlined animate-spin text-sm mr-2">refresh</span>
                            Create Gallery
                        </ActionButton>
                    </div>
                </form>
            </ContentBoxBody>
        </ContentBox>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import Button from '../../../components/ui/Button.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

const router = useRouter()

// Reactive data
const loading = ref(false)
const coverImagePreview = ref('')
const coverImageFile = ref<File | null>(null)
const coverImageInput = ref<HTMLInputElement>()
const tagInput = ref('')
const showCropper = ref(false)
const cropperImage = ref('')
const cropperRef = ref()
const showSuggestions = ref(false)
const highlightedIndex = ref(-1)

// Existing tags (dummy data - simulating real database data)
const existingTags = ref([
    'Photography',
    'Landscape',
    'Portrait',
    'Nature',
    'Architecture',
    'Street Photography',
    'Black and White',
    'Color Photography',
    'Vintage',
    'Modern',
    'Artistic',
    'Travel',
    'Food Photography',
    'Wedding',
    'Corporate Events',
    'Fashion',
    'Sports',
    'Wildlife',
    'Macro Photography',
    'Documentary',
    'Fine Art',
    'Commercial',
    'Editorial',
    'Product Photography',
    'Real Estate',
    'Aerial Photography',
    'Underwater',
    'Night Photography',
    'Portrait Session',
    'Family Photography',
    'Graduation',
    'Baby Photography',
    'Maternity',
    'Boudoir',
    'Headshots',
    'Business Portraits',
    'Event Coverage',
    'Concert Photography',
    'Festival',
    'Party Photography',
    'Catering',
    'Restaurant',
    'Food Styling',
    'Beverage',
    'Culinary Arts',
    'Street Food',
    'Desserts',
    'Baking',
    'Cooking',
    'Recipe Development'
])

const filteredTags = ref<string[]>([])

// Form data
const form = reactive({
    title: '',
    description: '',
    category: '',
    status: 'active',
    visibility: 'public',
    tags: [] as string[]
})

// Methods
const goBack = () => {
    router.push('/gallery_management/index')
}

const handleCoverImageSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        const file = target.files[0]

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB')
            return
        }

        // Validate file type
        if (!file.type.match('image/(jpeg|png|webp)')) {
            alert('Please select a valid image file (JPG, PNG, WebP)')
            return
        }

        const reader = new FileReader()
        reader.onload = (e) => {
            cropperImage.value = e.target?.result as string
            showCropper.value = true
        }
        reader.readAsDataURL(file)
    }
}

const removeCoverImage = () => {
    coverImagePreview.value = ''
    coverImageFile.value = null
    if (coverImageInput.value) {
        coverImageInput.value.value = ''
    }
}

const editImage = () => {
    if (coverImagePreview.value) {
        cropperImage.value = coverImagePreview.value
        showCropper.value = true
    }
}

const applyCrop = () => {
    if (cropperRef.value) {
        const result = cropperRef.value.getResult()
        if (result && result.canvas) {
            coverImagePreview.value = result.canvas.toDataURL()
            // Convert canvas to blob for file upload
            result.canvas.toBlob((blob: Blob) => {
                if (blob) {
                    coverImageFile.value = new File([blob], 'cropped-image.jpg', { type: 'image/jpeg' })
                }
            })
        }
    }
    showCropper.value = false
}

const cancelCrop = () => {
    showCropper.value = false
    cropperImage.value = ''
}

const filterTags = () => {
    const query = tagInput.value?.trim().toLowerCase() || ''
    if (query) {
        filteredTags.value = existingTags.value.filter(tag =>
            tag.toLowerCase().includes(query) && !form.tags.includes(tag)
        )
    } else {
        filteredTags.value = []
    }
    highlightedIndex.value = -1
}

const hideSuggestions = () => {
    // Delay hiding to allow click events
    setTimeout(() => {
        showSuggestions.value = false
        highlightedIndex.value = -1
    }, 150)
}

const selectTag = (tag: string) => {
    if (!form.tags.some(t => t.toLowerCase() === tag.toLowerCase())) {
        form.tags.push(tag)
    }
    tagInput.value = ''
    filteredTags.value = []
    showSuggestions.value = false
    highlightedIndex.value = -1
}

const createNewTag = () => {
    const newTag = tagInput.value?.trim() || ''
    if (newTag && !form.tags.some(t => t.toLowerCase() === newTag.toLowerCase())) {
        form.tags.push(newTag)
        // Add to existing tags for future suggestions
        if (!existingTags.value.some(t => t.toLowerCase() === newTag.toLowerCase())) {
            existingTags.value.push(newTag)
        }
    }
    tagInput.value = ''
    filteredTags.value = []
    showSuggestions.value = false
    highlightedIndex.value = -1
}

const handleTagKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' || event.key === ',' || (event.key === ' ' && tagInput.value.trim() !== '')) {
        if (event.key === 'Enter' || event.key === ',' || event.key === ' ') {
            event.preventDefault()
        }

        const inputValue = tagInput.value?.trim() || ''
        if (!inputValue) return

        // If something is highlighted in the suggestion list
        if (highlightedIndex.value >= 0) {
            const tag = filteredTags.value[highlightedIndex.value]
            if (tag) {
                selectTag(tag)
            } else if (highlightedIndex.value === filteredTags.value.length) {
                createNewTag()
            }
        }
        // If nothing is highlighted but the input exactly matches an existing (suggested) tag
        else if (filteredTags.value.some(t => t.toLowerCase() === inputValue.toLowerCase())) {
            const match = filteredTags.value.find(t => t.toLowerCase() === inputValue.toLowerCase())
            if (match) selectTag(match)
        }
        // Otherwise create it as a new tag
        else {
            createNewTag()
        }
    } else if (event.key === 'ArrowDown') {
        event.preventDefault()
        if (!showSuggestions.value) {
            showSuggestions.value = true
            return
        }
        const inputValue = tagInput.value?.trim() || ''
        const canCreate = inputValue && !existingTags.value.some(t => t.toLowerCase() === inputValue.toLowerCase())
        const totalItems = filteredTags.value.length + (canCreate ? 1 : 0)

        if (totalItems > 0) {
            highlightedIndex.value = (highlightedIndex.value + 1) % totalItems
        }
    } else if (event.key === 'ArrowUp') {
        event.preventDefault()
        if (highlightedIndex.value === -1) return

        const inputValue = tagInput.value?.trim() || ''
        const canCreate = inputValue && !existingTags.value.some(t => t.toLowerCase() === inputValue.toLowerCase())
        const totalItems = filteredTags.value.length + (canCreate ? 1 : 0)

        highlightedIndex.value = (highlightedIndex.value - 1 + totalItems) % totalItems
    } else if (event.key === 'Escape') {
        showSuggestions.value = false
        highlightedIndex.value = -1
    }
}

const removeTag = (index: number) => {
    form.tags.splice(index, 1)
}

const createGallery = async () => {
    if (!form.title.trim()) {
        alert('Gallery title is required')
        return
    }

    loading.value = true

    try {
        // TODO: Implement API call to create gallery
        console.log('Creating gallery:', {
            ...form,
            coverImage: coverImageFile.value
        })

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 2000))

        // Success - redirect back to galleries
        router.push('/gallery_management/index')

    } catch (error) {
        console.error('Error creating gallery:', error)
        alert('Failed to create gallery. Please try again.')
    } finally {
        loading.value = false
    }
}
</script>

<style scoped></style>

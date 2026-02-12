<template>
    <ContentBox>
        <ContentBoxHeader>
            <template #title>
                <ContentBoxTitle :title="mode === 'edit' ? 'Edit Gallery' : 'Create Gallery'"
                    :subtitle="mode === 'edit' ? 'Update the details of your gallery collection' : 'Fill in the details to create a new gallery'" />
            </template>
        </ContentBoxHeader>

        <ContentBoxBody>
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Gallery Title *</label>
                        <input v-model="form.title" type="text"
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            placeholder="Enter gallery title" required />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Category *</label>
                        <select v-model="form.category_id"
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                            required>
                            <option value="" disabled selected>Select Category</option>
                            <option v-for="cat in galleryCategories" :key="cat.id" :value="String(cat.id)">
                                {{ cat.name }}
                            </option>
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
                                <ActionButton type="button" variant="secondary" @click="cancelCrop">
                                    Cancel
                                </ActionButton>
                                <ActionButton type="button" variant="primary" @click="applyCrop">
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

                        <!-- Preview Case -->
                        <div v-if="coverImagePreview || gallery?.coverImage" class="space-y-4">
                            <img :src="coverImagePreview || gallery?.coverImage" alt="Cover preview"
                                class="max-w-full max-h-48 mx-auto rounded-lg shadow-md" />
                            <div class="flex justify-center gap-2">
                                <ActionButton type="button" variant="secondary" size="sm" @click="editImage">
                                    <span class="material-symbols-outlined mr-1">crop</span>
                                    Crop
                                </ActionButton>
                                <ActionButton type="button" variant="secondary" size="sm"
                                    @click="() => coverImageInput?.click()">
                                    Change Image
                                </ActionButton>
                                <Button v-if="coverImagePreview" type="button" variant="danger" size="sm"
                                    left-icon="close" @click="removeNewImage">
                                    {{ mode === 'edit' ? 'Reset to Original' : 'Remove' }}
                                </Button>
                            </div>
                        </div>

                        <!-- Empty Case -->
                        <div v-else @click="() => coverImageInput?.click()" class="cursor-pointer">
                            <span class="material-symbols-outlined text-gray-400 text-3xl">cloud_upload</span>
                            <p class="mt-2 text-sm text-gray-600">Click to upload cover image</p>
                            <p class="text-xs text-gray-400">JPG, PNG, WebP up to 5MB</p>
                            <p class="text-xs text-gray-400">Will be cropped to 4:3 ratio</p>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

                    <div v-if="mode === 'edit'">
                        <label class="block text-sm font-semibold text-slate-700 mb-2 text-slate-400">Item Count
                            (Read-only)</label>
                        <input v-model.number="form.itemCount" type="number"
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-100 text-slate-500 cursor-not-allowed"
                            readonly />
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
                        <div v-if="showSuggestions && (filteredTags.length > 0 || (tagInput.trim() && !filteredTags.some(t => t.name.toLowerCase() === tagInput.trim().toLowerCase())))"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                            <!-- Existing tags -->
                            <div v-if="filteredTags.length > 0" class="border-b border-gray-200">
                                <div class="px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wide">
                                    Existing Tags
                                </div>
                                <div v-for="(tag, index) in filteredTags" :key="tag.id" @mousedown.prevent="selectTag(tag)"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer flex items-center justify-between transition-colors"
                                    :class="{ 'bg-primary text-white': highlightedIndex === index }">
                                    <span>{{ tag.name }}</span>
                                    <span class="material-symbols-outlined text-xs"
                                        :class="highlightedIndex === index ? 'text-white' : 'text-gray-400'">add</span>
                                </div>
                            </div>
                            <!-- Create new tag -->
                            <div v-if="tagInput.trim() && !filteredTags.some(t => t.name.toLowerCase() === tagInput.trim().toLowerCase())"
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
                    <ActionButton variant="secondary" @click="$emit('cancel')">
                        Cancel
                    </ActionButton>
                    <ActionButton variant="primary" type="submit" :loading="isSaving">
                        {{ mode === 'edit' ? 'Save Changes' : 'Create Gallery' }}
                    </ActionButton>
                </div>
            </form>
        </ContentBoxBody>
    </ContentBox>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import ContentBox from '../ui/ContentBox.vue'
import ContentBoxHeader from '../ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../ui/ContentBoxTitle.vue'
import ContentBoxBody from '../ui/ContentBoxBody.vue'
import Button from '../ui/Button.vue'
import ActionButton from '../ui/ActionButton.vue'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import { showToast } from '@/composables/useSweetAlert'
import { useGalleryData } from '@/composables/gallery/useGalleryData'
import { debounce } from '@/utils/debounce'
import { galleryCategoryMocks } from '@/mocks/gallery/categoryMocks'

interface Props {
    mode: 'create' | 'edit'
    gallery?: any
    galleryCategories: any[]
}

const props = defineProps<Props>()
const emit = defineEmits(['cancel', 'success', 'submit'])

// Allow parent to reset/loading state on error
const resetLoading = () => { isSaving.value = false }

defineExpose({ resetLoading })

// State
const isSaving = ref(false)
const coverImagePreview = ref('')
const coverImageFile = ref<File | null>(null)
const coverImageInput = ref<HTMLInputElement>()
const tagInput = ref('')
const showCropper = ref(false)
const cropperImage = ref('')
const cropperRef = ref()
const showSuggestions = ref(false)
const highlightedIndex = ref(-1)

const { fetchTagOptions } = useGalleryData()

const existingTags = ref<string[]>([])
const filteredTags = ref<Array<{id: number, name: string}>>([])

// Debounced fetch for tag suggestions (delegates to composable)
const fetchTagSuggestions = debounce(async (q: string) => {
    if (!q) {
        filteredTags.value = []
        return
    }

    try {
        const opts = await fetchTagOptions(q, 10)
        filteredTags.value = Array.isArray(opts) ? opts : []
    } catch (e) {
        filteredTags.value = []
    }
}, 300)

// Form data
const form = reactive({
    title: '',
    description: '',
    category_id: '',
    status: 'active',
    visibility: 'public',
    itemCount: 0,
    tags: [] as string[]
})

// Initialize form in edit mode
watch(() => props.gallery, (newVal) => {
    if (props.mode === 'edit' && newVal) {
        form.title = newVal.title || ''
        form.description = newVal.description || ''
        form.category_id = newVal.category_id ? String(newVal.category_id) : (newVal.category ? String(newVal.category.id) : '')
        form.status = newVal.status || 'active'
        form.itemCount = newVal.itemCount || 0
        form.visibility = newVal.visibility || 'public'
        form.tags = newVal.tags ? [...newVal.tags] : []
    }
}, { immediate: true })

// Methods
const handleCoverImageSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        const file = target.files[0]
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB')
            return
        }
        if (!file.type.match('image/(jpeg|png|webp)')) {
            alert('Please select a valid image file (JPG, PNG, WebP)')
            return
        }

        // Immediately set preview + file so submission is allowed without cropping (hybrid UX)
        try {
            // Use object URL for quicker preview and to avoid base64 memory when possible
            coverImagePreview.value = URL.createObjectURL(file)
        } catch (e) {
            // Fallback to FileReader data URL
            const fr = new FileReader()
            fr.onload = (ev) => {
                coverImagePreview.value = ev.target?.result as string
            }
            fr.readAsDataURL(file)
        }
        coverImageFile.value = file

        // Still load data URL into cropper so user may optionally crop
        const reader = new FileReader()
        reader.onload = (e) => {
            cropperImage.value = e.target?.result as string
            showCropper.value = true
        }
        reader.readAsDataURL(file)
    }
}

const removeNewImage = () => {
    // Revoke object URL if we created one earlier
    if (coverImagePreview.value && coverImagePreview.value.startsWith && coverImagePreview.value.startsWith('blob:')) {
        try { URL.revokeObjectURL(coverImagePreview.value) } catch (e) { /* ignore */ }
    }

    coverImagePreview.value = ''
    coverImageFile.value = null
    if (coverImageInput.value) {
        coverImageInput.value.value = ''
    }
}

const editImage = () => {
    const src = coverImagePreview.value || props.gallery?.coverImage
    if (src) {
        cropperImage.value = src
        showCropper.value = true
    }
}

const applyCrop = () => {
    if (cropperRef.value) {
        const result = cropperRef.value.getResult()
        if (result && result.canvas) {
            coverImagePreview.value = result.canvas.toDataURL()
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
    const query = tagInput.value?.trim() || ''
    if (query) {
        fetchTagSuggestions(query)
    } else {
        filteredTags.value = []
    }
    highlightedIndex.value = -1
}

const hideSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false
        highlightedIndex.value = -1
    }, 150)
}

// Select tag from suggestions (accepts either name string or object)
const selectTag = (tag: string | { id: number, name: string }) => {
    const name = typeof tag === 'string' ? tag : tag.name
    if (!form.tags.some(t => t.toLowerCase() === name.toLowerCase())) {
        form.tags.push(name)
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
        event.preventDefault()
        const inputValue = tagInput.value?.trim() || ''
        if (!inputValue) return

        if (highlightedIndex.value >= 0) {
            const selectedTagValue = filteredTags.value[highlightedIndex.value]
            if (selectedTagValue) {
                selectTag(selectedTagValue)
            } else if (highlightedIndex.value === filteredTags.value.length) {
                createNewTag()
            }
        } else if (filteredTags.value.some(t => t.name.toLowerCase() === inputValue.toLowerCase())) {
            const match = filteredTags.value.find(t => t.name.toLowerCase() === inputValue.toLowerCase())
            if (match) selectTag(match)
        } else {
            createNewTag()
        }
    } else if (event.key === 'ArrowDown') {
        event.preventDefault()
        if (!showSuggestions.value) {
            showSuggestions.value = true
            return
        }
        const inputValue = tagInput.value?.trim() || ''
        const canCreate = inputValue && !filteredTags.value.some(t => t.name.toLowerCase() === inputValue.toLowerCase())
        const totalItems = filteredTags.value.length + (canCreate ? 1 : 0)
        if (totalItems > 0) highlightedIndex.value = (highlightedIndex.value + 1) % totalItems
    } else if (event.key === 'ArrowUp') {
        event.preventDefault()
        const inputValue = tagInput.value?.trim() || ''
        const canCreate = inputValue && !filteredTags.value.some(t => t.name.toLowerCase() === inputValue.toLowerCase())
        const totalItems = filteredTags.value.length + (canCreate ? 1 : 0)
        if (totalItems > 0) highlightedIndex.value = (highlightedIndex.value - 1 + totalItems) % totalItems
    } else if (event.key === 'Escape') {
        showSuggestions.value = false
    }
}

const removeTag = (index: number) => {
    form.tags.splice(index, 1)
}


const handleSubmit = async () => {
    if (!form.title.trim()) {
        await showToast({ icon: 'warning', title: 'Validation Error', text: 'Title is required.' })
        return
    }

    if (!form.category_id) {
        await showToast({ icon: 'warning', title: 'Validation Error', text: 'Category is required.' })
        return
    }

    if (!coverImageFile.value) {
        await showToast({ icon: 'warning', title: 'Validation Error', text: 'Cover image is required.' })
        return
    }

    isSaving.value = true

    try {
        // Use a differently named payload to avoid accidental collisions with any global/formData variables
        const payload = typeof globalThis !== 'undefined' && globalThis.FormData ? new globalThis.FormData() : new FormData()

        if (!(payload instanceof FormData)) {
            throw new Error('Cannot construct FormData in this environment')
        }

        // Prepare final FormData to send (ensure we append fields whether payload is native FormData or fallback)
        let formDataToSend: FormData | null = null

        if (payload instanceof FormData && typeof (payload as any).append === 'function') {
            formDataToSend = payload as FormData

            // Append form fields to the (possibly-empty) FormData instance
            formDataToSend.append('title', form.title)
            formDataToSend.append('category_id', form.category_id)
            if (form.description) formDataToSend.append('description', form.description)
            formDataToSend.append('status', form.status)
            formDataToSend.append('visibility', form.visibility)
            ;(form.tags || []).forEach((t: string) => formDataToSend!.append('tags[]', t))
            if (coverImageFile.value) formDataToSend.append('cover', coverImageFile.value)
        } else {
            const fallback = new FormData()
            fallback.append('title', form.title)
            fallback.append('category_id', form.category_id)
            if (form.description) fallback.append('description', form.description)
            fallback.append('status', form.status)
            fallback.append('visibility', form.visibility)
            ;(form.tags || []).forEach((t: string) => fallback.append('tags[]', t))
            if (coverImageFile.value) fallback.append('cover', coverImageFile.value)
            formDataToSend = fallback
        }

        if (!formDataToSend) throw new Error('Failed to prepare form data for upload')

        // Debugging: log form content in development to help trace issues where server reports missing fields


        // Emit FormData to parent — parent will call the API using `useGalleryData`
        isSaving.value = true
        emit('submit', formDataToSend)
        // parent is responsible for calling `emit('success')` on success (or navigation)
    } catch (error: unknown) {
        // Safely extract message from unknown error
        let message = 'Failed to process gallery.'
        if (typeof error === 'string') {
            message = error
        } else if (error && typeof error === 'object') {
            const errObj = error as any
            if (typeof errObj.message === 'string') {
                message = errObj.message
            } else if (typeof errObj.error === 'string') {
                message = errObj.error
            }
        }

        await showToast({ icon: 'error', title: 'Error', text: message })
    } finally {
        isSaving.value = false
    }
}
</script>

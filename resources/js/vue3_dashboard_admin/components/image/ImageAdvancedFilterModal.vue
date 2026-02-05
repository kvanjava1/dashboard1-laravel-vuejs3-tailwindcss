<template>
    <BaseModal
        v-model="isOpen"
        size="lg"
    >
        <!-- Modal Header -->
        <template #header>
            <span class="material-symbols-outlined text-primary text-2xl">filter_list</span>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Advanced Image Filter</h2>
                <p class="text-sm text-slate-500">Search and filter images by multiple criteria</p>
            </div>
        </template>

        <!-- Modal Body -->
        <template #body>
            <div class="space-y-8">
                <!-- Basic Search -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">search</span>
                        Search
                    </h4>
                    <div class="grid grid-cols-1 gap-6">
                        <FormField
                            v-model="filters.search"
                            label="Search Images"
                            type="text"
                            placeholder="Search by image name, gallery, or description"
                        />
                    </div>
                </div>

                <!-- Category & Gallery -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">folder</span>
                        Organization
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField v-model="filters.category" label="Category" type="select">
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </FormField>

                        <FormField v-model="filters.gallery" label="Gallery" type="select">
                            <option value="">All Galleries</option>
                            <option v-for="gallery in filteredGalleries" :key="gallery.id" :value="gallery.id">
                                {{ gallery.title }}
                            </option>
                        </FormField>
                    </div>
                </div>

                <!-- Date Range -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">calendar_today</span>
                        Upload Date
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField
                            v-model="filters.date_from"
                            label="From Date"
                            type="date"
                        />

                        <FormField
                            v-model="filters.date_to"
                            label="To Date"
                            type="date"
                        />
                    </div>
                </div>

                <!-- File Information -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">description</span>
                        File Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField v-model="filters.file_type" label="File Type" type="select">
                            <option value="">All Types</option>
                            <option value="jpg">JPG</option>
                            <option value="png">PNG</option>
                            <option value="gif">GIF</option>
                            <option value="webp">WebP</option>
                        </FormField>

                        <FormField v-model="filters.size_range" label="Size Range" type="select">
                            <option value="">All Sizes</option>
                            <option value="small">Small (&lt; 1MB)</option>
                            <option value="medium">Medium (1-5MB)</option>
                            <option value="large">Large (&gt; 5MB)</option>
                        </FormField>
                    </div>
                </div>
            </div>
        </template>

        <!-- Modal Footer -->
        <template #footer>
            <div class="flex justify-end gap-3">
                <ActionButton variant="secondary" @click="handleReset">
                    Reset Filters
                </ActionButton>
                <ActionButton variant="primary" @click="handleApply">
                    Apply Filters
                </ActionButton>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import BaseModal from '../ui/BaseModal.vue'
import FormField from '../ui/FormField.vue'
import ActionButton from '../ui/ActionButton.vue'

// Props
interface Props {
    modelValue: boolean
    initialFilters: {
        search: string
        category: string
        gallery: string
        date_from: string
        date_to: string
        file_type: string
        size_range: string
    }
    categories: Array<{ id: number, name: string }>
    galleries: Array<{ id: number, title: string, categoryId: number }>
}

const props = defineProps<Props>()

// Emits
const emit = defineEmits<{
    'update:modelValue': [value: boolean]
    'apply': [filters: Props['initialFilters']]
    'reset': []
}>()

// Reactive state
const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const filters = ref<Props['initialFilters']>({ ...props.initialFilters })

// Computed
const filteredGalleries = computed(() => {
    if (!filters.value.category) return props.galleries
    return props.galleries.filter(gallery => gallery.categoryId === parseInt(filters.value.category))
})

// Watch for category changes to reset gallery if it's not in the filtered list
watch(() => filters.value.category, (newCategory) => {
    if (newCategory && filters.value.gallery) {
        const galleryExists = filteredGalleries.value.some(g => g.id === parseInt(filters.value.gallery))
        if (!galleryExists) {
            filters.value.gallery = ''
        }
    }
})

// Methods
const handleApply = () => {
    emit('apply', { ...filters.value })
    isOpen.value = false
}

const handleReset = () => {
    filters.value = {
        search: '',
        category: '',
        gallery: '',
        date_from: '',
        date_to: '',
        file_type: '',
        size_range: ''
    }
    emit('reset')
}

// Reset filters when modal opens with initial values
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        filters.value = { ...props.initialFilters }
    }
})
</script>
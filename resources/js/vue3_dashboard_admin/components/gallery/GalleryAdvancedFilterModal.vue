<template>
    <BaseModal v-model="isOpen" size="lg">
        <!-- Modal Header -->
        <template #header>
            <span class="material-symbols-outlined text-primary text-2xl">filter_list</span>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Advanced Gallery Filter</h2>
                <p class="text-sm text-slate-500">Search and filter galleries by multiple criteria</p>
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
                        <FormField v-model="filters.search" label="Search Galleries" type="text"
                            placeholder="Search by gallery title or description" />
                    </div>
                </div>

                <!-- Category & Status -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">folder</span>
                        Organization & Status
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField v-model="filters.category" label="Category" type="select">
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                {{ category.name }}
                            </option>
                        </FormField>

                        <FormField v-model="filters.status" label="Status" type="select">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </FormField>
                    </div>
                </div>

                <!-- Content Information -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">description</span>
                        Content Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField v-model="filters.min_items" label="Minimum Items" type="number" placeholder="0">
                        </FormField>

                        <FormField v-model="filters.max_items" label="Maximum Items" type="number"
                            placeholder="No limit">
                        </FormField>
                    </div>
                </div>

                <!-- Date Range -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">calendar_today</span>
                        Creation Date
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField v-model="filters.date_from" label="From Date" type="date" />

                        <FormField v-model="filters.date_to" label="To Date" type="date" />
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
        status: string
        min_items: string
        max_items: string
        date_from: string
        date_to: string
    }
    categories: Array<{ id: number, name: string }>
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

// Methods
const handleApply = () => {
    emit('apply', { ...filters.value })
    isOpen.value = false
}

const handleReset = () => {
    filters.value = {
        search: '',
        category: '',
        status: '',
        min_items: '',
        max_items: '',
        date_from: '',
        date_to: ''
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
<template>
    <BaseModal
        v-model="isOpen"
        size="lg"
    >
        <template #header>
            <span class="material-symbols-outlined text-primary text-2xl">filter_list</span>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Advanced Category Filter</h2>
                <p class="text-sm text-slate-500">Search and filter categories (dummy data)</p>
            </div>
        </template>

        <template #body>
            <div class="space-y-8">
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">search</span>
                        Search
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField
                            v-model="filters.search"
                            label="Search Categories"
                            type="text"
                            placeholder="Search by name or slug"
                        />
                        <FormField
                            v-model="filters.slug"
                            label="Slug"
                            type="text"
                            placeholder="e.g. announcements"
                        />
                    </div>
                </div>

                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">toggle_on</span>
                        Status
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField v-model="filters.status" label="Category Status" type="select">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </FormField>
                    </div>
                </div>
            </div>
        </template>

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
import { computed, ref, watch } from 'vue'
import BaseModal from '../ui/BaseModal.vue'
import FormField from '../ui/FormField.vue'
import ActionButton from '../ui/ActionButton.vue'

type FilterOptions = {
    search: string
    slug: string
    status: '' | 'active' | 'inactive'
}

interface Props {
    modelValue: boolean
    initialFilters: FilterOptions
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
    'apply': [filters: FilterOptions]
    'reset': []
}>()

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const filters = ref<FilterOptions>({ ...props.initialFilters })

const handleApply = () => {
    emit('apply', { ...filters.value })
    isOpen.value = false
}

const handleReset = () => {
    filters.value = {
        search: '',
        slug: '',
        status: ''
    }
    emit('reset')
}

watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        filters.value = { ...props.initialFilters }
    }
})
</script>

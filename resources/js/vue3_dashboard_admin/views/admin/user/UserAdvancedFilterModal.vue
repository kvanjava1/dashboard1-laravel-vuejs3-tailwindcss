<template>
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @click="closeModal"
        >
            <!-- Modal Content -->
            <div
                class="w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-border-light shadow-hard"
                @click.stop
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-border-light">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-2xl">filter_list</span>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Advanced User Filter</h2>
                            <p class="text-sm text-slate-500">Search and filter users by multiple criteria</p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors"
                    >
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6">
                    <!-- Filter Fields Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Full Name
                            </label>
                            <input
                                v-model="filters.name"
                                type="text"
                                placeholder="Enter full name"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Email Address
                            </label>
                            <input
                                v-model="filters.email"
                                type="email"
                                placeholder="Enter email address"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            />
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                User Role
                            </label>
                            <select
                                v-model="filters.role"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                            >
                                <option value="">All Roles</option>
                                <option value="administrator">Administrator</option>
                                <option value="editor">Editor</option>
                                <option value="viewer">Viewer</option>
                                <option value="moderator">Moderator</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Account Status
                            </label>
                            <select
                                v-model="filters.status"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                            >
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">
                            Registration Date Range
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">From Date</label>
                                <input
                                    v-model="filters.date_from"
                                    type="date"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">To Date</label>
                                <input
                                    v-model="filters.date_to"
                                    type="date"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">
                            Sort By
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <select
                                    v-model="filters.sort_by"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                                >
                                    <option value="name">Name</option>
                                    <option value="email">Email</option>
                                    <option value="role">Role</option>
                                    <option value="status">Status</option>
                                    <option value="created_at">Registration Date</option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="filters.sort_order"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                                >
                                    <option value="asc">Ascending</option>
                                    <option value="desc">Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 p-6 border-t border-border-light bg-slate-50/50">
                    <button
                        @click="resetFilters"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-full border border-border-light text-slate-700 text-sm font-semibold hover:bg-slate-100 transition-all duration-200"
                    >
                        Reset Filters
                    </button>
                    <button
                        @click="applyFilters"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-bold hover:shadow-hard hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-outlined text-[18px]">filter_list</span>
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'

interface FilterOptions {
    name: string
    email: string
    role: string
    status: string
    date_from: string
    date_to: string
    sort_by: string
    sort_order: string
}

interface Props {
    modelValue: boolean
    initialFilters?: Partial<FilterOptions>
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'apply', filters: FilterOptions): void
    (e: 'reset'): void
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    initialFilters: () => ({})
})

const emit = defineEmits<Emits>()

const isOpen = ref(false)

const filters = reactive<FilterOptions>({
    name: '',
    email: '',
    role: '',
    status: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    ...props.initialFilters
})

// Watch for modal value changes
watch(() => props.modelValue, (newValue) => {
    isOpen.value = newValue
})

watch(isOpen, (newValue) => {
    emit('update:modelValue', newValue)
})

const closeModal = () => {
    isOpen.value = false
}

const applyFilters = () => {
    emit('apply', { ...filters })
    closeModal()
}

const resetFilters = () => {
    Object.assign(filters, {
        search: '',
        name: '',
        email: '',
        role: '',
        status: '',
        date_from: '',
        date_to: '',
        sort_by: 'created_at',
        sort_order: 'desc'
    })
    emit('reset')
}

// Handle escape key
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && isOpen.value) {
        closeModal()
    }
}

document.addEventListener('keydown', handleKeydown)

// Cleanup
import { onUnmounted } from 'vue'
onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})
</script>
<template>
    <BaseModal
        v-model="isOpen"
        size="lg"
    >
        <!-- Modal Header -->
        <template #header>
            <span class="material-symbols-outlined text-primary text-2xl">filter_list</span>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Advanced User Filter</h2>
                <p class="text-sm text-slate-500">Search and filter users by multiple criteria</p>
            </div>
        </template>

        <!-- Modal Body -->
        <template #body>
            <div class="space-y-8">
                <!-- Basic Information -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">person</span>
                        Basic Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField
                            v-model="filters.name"
                            label="Full Name"
                            type="text"
                            placeholder="Enter full name"
                        />

                        <FormField
                            v-model="filters.email"
                            label="Email Address"
                            type="email"
                            placeholder="Enter email address"
                        />

                        <FormField
                            v-model="filters.phone"
                            label="Phone Number"
                            type="text"
                            placeholder="Enter phone number"
                        />

                        <FormField
                            v-model="filters.username"
                            label="Username"
                            type="text"
                            placeholder="Enter username"
                        />
                    </div>
                </div>

                <!-- Profile Information -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">info</span>
                        Profile Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField
                            v-model="filters.location"
                            label="Location"
                            type="text"
                            placeholder="Enter location"
                        />

                        <FormField
                            v-model="filters.timezone"
                            label="Timezone"
                            type="text"
                            placeholder="e.g., UTC, America/New_York"
                        />

                        <FormField
                            v-model="filters.bio"
                            label="Bio"
                            type="textarea"
                            placeholder="Enter bio content"
                            class="md:col-span-2"
                        />
                    </div>
                </div>

                <!-- Account Settings -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">settings</span>
                        Account Settings
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField v-model="filters.role" label="User Role" type="select">
                            <option value="">All Roles</option>
                            <option v-for="role in availableRoles" :key="role.name" :value="role.name">{{ role.display_name }}</option>
                        </FormField>

                        <FormField v-model="filters.status" label="Account Status" type="select">
                            <option value="">All Statuses</option>
                            <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}</option>
                        </FormField>

                        <FormField v-model="filters.is_banned" label="Ban Status" type="select">
                            <option value="">All Users</option>
                            <option value="1">Banned Only</option>
                            <option value="0">Not Banned</option>
                        </FormField>
                    </div>
                </div>

                <!-- Date Filters -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">calendar_today</span>
                        Date Filters
                    </h4>
                    <div class="space-y-6">
                        <!-- Registration Date -->
                        <div>
                            <h5 class="text-sm font-medium text-slate-700 mb-3">Registration Date</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <FormField
                                    v-model="filters.date_from"
                                    label="From Date"
                                    type="date"
                                    help="Registration start date"
                                />

                                <FormField
                                    v-model="filters.date_to"
                                    label="To Date"
                                    type="date"
                                    help="Registration end date"
                                />
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <h5 class="text-sm font-medium text-slate-700 mb-3">Date of Birth</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <FormField
                                    v-model="filters.date_of_birth_from"
                                    label="Birth From"
                                    type="date"
                                    help="Birth date from"
                                />

                                <FormField
                                    v-model="filters.date_of_birth_to"
                                    label="Birth To"
                                    type="date"
                                    help="Birth date to"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sort Options -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">sort</span>
                        Sorting
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField v-model="filters.sort_by" label="Sort By" type="select">
                            <option value="name">Name</option>
                            <option value="email">Email</option>
                            <option value="username">Username</option>
                            <option value="phone">Phone</option>
                            <option value="location">Location</option>
                            <option value="date_of_birth">Date of Birth</option>
                            <option value="created_at">Registration Date</option>
                            <option value="updated_at">Last Updated</option>
                        </FormField>

                        <FormField v-model="filters.sort_order" label="Order" type="select">
                            <option value="asc">Ascending</option>
                            <option value="desc">Descending</option>
                        </FormField>
                    </div>
                </div>
            </div>
        </template>

        <!-- Modal Footer -->
        <template #footer>
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
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import BaseModal from '../../../components/ui/BaseModal.vue'
import FormField from '../../../components/ui/FormField.vue'

interface FilterOptions {
    name: string
    email: string
    phone: string
    username: string
    location: string
    bio: string
    role: string
    status: string
    is_banned: string
    date_of_birth_from: string
    date_of_birth_to: string
    date_from: string
    date_to: string
    timezone: string
    sort_by: string
    sort_order: string
}

interface Props {
    modelValue: boolean
    initialFilters?: Partial<FilterOptions>
    availableRoles?: Array<{ id: number; name: string; display_name: string }>
    statusOptions?: Array<{ value: string; label: string }>
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'apply', filters: FilterOptions): void
    (e: 'reset'): void
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    initialFilters: () => ({}),
    availableRoles: () => [],
    statusOptions: () => [
        { value: 'active', label: 'Active' },
        { value: 'inactive', label: 'Inactive' },
        { value: 'pending', label: 'Pending' },
        { value: 'suspended', label: 'Suspended' }
    ]
})

const emit = defineEmits<Emits>()

const isOpen = ref(false)

const filters = reactive<FilterOptions>({
    name: props.initialFilters?.name || '',
    email: props.initialFilters?.email || '',
    phone: props.initialFilters?.phone || '',
    username: props.initialFilters?.username || '',
    location: props.initialFilters?.location || '',
    bio: props.initialFilters?.bio || '',
    role: props.initialFilters?.role || '',
    status: props.initialFilters?.status || '',
    is_banned: props.initialFilters?.is_banned || '',
    date_of_birth_from: props.initialFilters?.date_of_birth_from || '',
    date_of_birth_to: props.initialFilters?.date_of_birth_to || '',
    date_from: props.initialFilters?.date_from || '',
    date_to: props.initialFilters?.date_to || '',
    timezone: props.initialFilters?.timezone || '',
    sort_by: props.initialFilters?.sort_by || 'created_at',
    sort_order: props.initialFilters?.sort_order || 'desc'
})

// Watch for modal value changes
watch(() => props.modelValue, (newValue) => {
    isOpen.value = newValue
    if (newValue) {
        // When modal opens, sync with current initialFilters
        filters.name = props.initialFilters?.name || ''
        filters.email = props.initialFilters?.email || ''
        filters.phone = props.initialFilters?.phone || ''
        filters.username = props.initialFilters?.username || ''
        filters.location = props.initialFilters?.location || ''
        filters.bio = props.initialFilters?.bio || ''
        filters.role = props.initialFilters?.role || ''
        filters.status = props.initialFilters?.status || ''
        filters.is_banned = props.initialFilters?.is_banned || ''
        filters.date_of_birth_from = props.initialFilters?.date_of_birth_from || ''
        filters.date_of_birth_to = props.initialFilters?.date_of_birth_to || ''
        filters.date_from = props.initialFilters?.date_from || ''
        filters.date_to = props.initialFilters?.date_to || ''
        filters.timezone = props.initialFilters?.timezone || ''
        filters.sort_by = props.initialFilters?.sort_by || 'created_at'
        filters.sort_order = props.initialFilters?.sort_order || 'desc'
    }
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
    filters.name = ''
    filters.email = ''
    filters.phone = ''
    filters.username = ''
    filters.location = ''
    filters.bio = ''
    filters.role = ''
    filters.status = ''
    filters.is_banned = ''
    filters.date_of_birth_from = ''
    filters.date_of_birth_to = ''
    filters.date_from = ''
    filters.date_to = ''
    filters.timezone = ''
    filters.sort_by = 'created_at'
    filters.sort_order = 'desc'
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
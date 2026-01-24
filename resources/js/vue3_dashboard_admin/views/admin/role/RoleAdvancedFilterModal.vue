<template>
    <BaseModal
        v-model="isOpen"
        size="lg"
    >
        <!-- Modal Header -->
        <template #header>
            <span class="material-symbols-outlined text-2xl text-primary">filter_list</span>
            <div>
                <h3 class="text-xl font-semibold text-slate-800">Advanced Filter</h3>
                <p class="text-sm text-slate-600">Filter roles by name and permissions</p>
            </div>
        </template>

        <!-- Modal Body -->
        <template #body>
            <div class="space-y-6">
                <!-- Role Name Search -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Role Name
                    </label>
                    <input
                        v-model="localSearch"
                        type="text"
                        placeholder="Search by role name..."
                        class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                    />
                    <p class="text-xs text-slate-500 mt-1">Click "Apply Filters" to search</p>
                    <p class="text-xs text-slate-500 mt-1">Partial matches allowed</p>
                </div>

                <!-- Permissions Filter -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Permissions
                    </label>
                    <p class="text-xs text-slate-500 mb-3">Select permissions to filter roles that have them:</p>
                    <div class="max-h-64 overflow-y-auto border rounded-lg p-4 bg-slate-50 space-y-4">
                        <div v-if="Object.keys(availablePermissions).length === 0" class="text-sm text-slate-500">
                            Loading permissions...
                        </div>

                        <!-- Dashboard Permissions -->
                        <div v-if="availablePermissions.dashboard && availablePermissions.dashboard.length > 0">
                            <h5 class="text-sm font-semibold text-slate-800 mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">dashboard</span>
                                Dashboard
                            </h5>
                            <div class="grid grid-cols-1 gap-2 ml-6">
                                <label
                                    v-for="permission in availablePermissions.dashboard"
                                    :key="permission.name"
                                    class="flex items-center gap-2 cursor-pointer hover:bg-white rounded px-2 py-1"
                                >
                                    <input
                                        v-model="localPermissions"
                                        :value="permission.name"
                                        type="checkbox"
                                        class="w-4 h-4 rounded border-border-light text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm text-slate-700">{{ permission.label }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- User Management Permissions -->
                        <div v-if="availablePermissions.user_management && availablePermissions.user_management.length > 0">
                            <h5 class="text-sm font-semibold text-slate-800 mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">group</span>
                                User Management
                            </h5>
                            <div class="grid grid-cols-1 gap-2 ml-6">
                                <label
                                    v-for="permission in availablePermissions.user_management"
                                    :key="permission.name"
                                    class="flex items-center gap-2 cursor-pointer hover:bg-white rounded px-2 py-1"
                                >
                                    <input
                                        v-model="localPermissions"
                                        :value="permission.name"
                                        type="checkbox"
                                        class="w-4 h-4 rounded border-border-light text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm text-slate-700">{{ permission.label }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Report Permissions -->
                        <div v-if="availablePermissions.report && availablePermissions.report.length > 0">
                            <h5 class="text-sm font-semibold text-slate-800 mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">analytics</span>
                                Reports
                            </h5>
                            <div class="grid grid-cols-1 gap-2 ml-6">
                                <label
                                    v-for="permission in availablePermissions.report"
                                    :key="permission.name"
                                    class="flex items-center gap-2 cursor-pointer hover:bg-white rounded px-2 py-1"
                                >
                                    <input
                                        v-model="localPermissions"
                                        :value="permission.name"
                                        type="checkbox"
                                        class="w-4 h-4 rounded border-border-light text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm text-slate-700">{{ permission.label }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Other Permissions -->
                        <div v-if="availablePermissions.other && availablePermissions.other.length > 0">
                            <h5 class="text-sm font-semibold text-slate-800 mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">settings</span>
                                Other
                            </h5>
                            <div class="grid grid-cols-1 gap-2 ml-6">
                                <label
                                    v-for="permission in availablePermissions.other"
                                    :key="permission.name"
                                    class="flex items-center gap-2 cursor-pointer hover:bg-white rounded px-2 py-1"
                                >
                                    <input
                                        v-model="localPermissions"
                                        :value="permission.name"
                                        type="checkbox"
                                        class="w-4 h-4 rounded border-border-light text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm text-slate-700">{{ permission.label }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Roles must have ALL selected permissions</p>
                </div>
            </div>
        </template>

        <!-- Modal Footer -->
        <template #footer>
                <div class="flex justify-between gap-3">
                    <Button
                        variant="ghost"
                        @click="handleReset"
                    >
                        Reset All
                    </Button>
                    <div class="flex gap-3">
                        <Button
                            variant="outline"
                            @click="$emit('cancel')"
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="primary"
                            @click="handleApply"
                        >
                            Apply Filters
                        </Button>
                    </div>
                </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import BaseModal from '../../../components/ui/BaseModal.vue'
import Button from '../../../components/ui/Button.vue'

// Props
interface Props {
    modelValue: boolean
    filters: {
        search: string
        permissions: string[]
    }
    availablePermissions: {
        dashboard?: any[]
        user_management?: any[]
        report?: any[]
        other?: any[]
    }
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    filters: () => ({ search: '', permissions: [] }),
    availablePermissions: () => ({})
})

// Emits
const emit = defineEmits<{
    'update:modelValue': [value: boolean]
    'apply-filters': [filters: { search: string; permissions: string[] }]
    'cancel': []
    'reset': []
}>()

// Computed for v-model
const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

// Local state for manual filter updates
const localSearch = ref('')
const localPermissions = ref<string[]>([])

// Initialize local state when modal opens
watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        // Reset local state to current filter values when modal opens
        localSearch.value = props.filters.search
        localPermissions.value = [...props.filters.permissions]
    }
})

// No automatic API calls - only manual apply
// (Debouncing removed to prevent automatic requests)

// Manual apply function - sends current filter values
const handleApply = () => {
    emit('apply-filters', {
        search: localSearch.value,
        permissions: [...localPermissions.value]
    })
}

const handleReset = () => {
    localSearch.value = ''
    localPermissions.value = []
    emit('reset')
}
</script>
<template>
    <BaseModal
        v-model="isOpen"
        size="lg"
    >
        <!-- Modal Header -->
        <template #header>
            <span class="material-symbols-outlined text-2xl text-primary">manage_accounts</span>
            <div>
                <h3 class="text-xl font-semibold text-slate-800">Role Permissions</h3>
                <p class="text-sm text-slate-600" v-if="role">
                    Viewing permissions for: <span class="font-medium">{{ role.display_name || role.name }}</span>
                </p>
            </div>
        </template>

        <!-- Modal Body -->
        <template #body>
            <div v-if="role" class="space-y-8">
                <!-- Role Overview -->
                <div>
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">info</span>
                        Role Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-lg border">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Role Name</label>
                            <p class="text-sm font-mono text-slate-800">{{ role.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Display Name</label>
                            <p class="text-sm text-slate-800">{{ role.display_name || role.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Total Permissions</label>
                            <p class="text-sm text-slate-800">{{ role.permissions?.length || 0 }} permissions</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="role.description" class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <label class="block text-sm font-medium text-blue-700 mb-2">Description</label>
                        <p class="text-sm text-blue-800">{{ role.description }}</p>
                    </div>
                </div>

                <!-- Permissions by Category -->
                <div v-if="role.permissions && role.permissions.length > 0">
                    <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">security</span>
                        Permissions by Category
                    </h4>

                    <div class="grid gap-4">
                        <!-- Dashboard Permissions -->
                        <div v-if="getPermissionsByGroup('dashboard').length > 0" class="border border-slate-200 rounded-lg p-5 bg-blue-50/30">
                            <h5 class="text-md font-semibold text-slate-700 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg text-blue-600">dashboard</span>
                                Dashboard Permissions
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-auto">
                                    {{ getPermissionsByGroup('dashboard').length }}
                                </span>
                            </h5>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="permission in getPermissionsByGroup('dashboard')"
                                    :key="permission"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    {{ permission }}
                                </span>
                            </div>
                        </div>

                        <!-- User Management Permissions -->
                        <div v-if="getPermissionsByGroup('user_management').length > 0" class="border border-slate-200 rounded-lg p-5 bg-green-50/30">
                            <h5 class="text-md font-semibold text-slate-700 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg text-green-600">group</span>
                                User Management
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full ml-auto">
                                    {{ getPermissionsByGroup('user_management').length }}
                                </span>
                            </h5>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="permission in getPermissionsByGroup('user_management')"
                                    :key="permission"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    {{ permission }}
                                </span>
                            </div>
                        </div>

                        <!-- Report Permissions -->
                        <div v-if="getPermissionsByGroup('report').length > 0" class="border border-slate-200 rounded-lg p-5 bg-purple-50/30">
                            <h5 class="text-md font-semibold text-slate-700 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg text-purple-600">analytics</span>
                                Reports
                                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full ml-auto">
                                    {{ getPermissionsByGroup('report').length }}
                                </span>
                            </h5>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="permission in getPermissionsByGroup('report')"
                                    :key="permission"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                >
                                    {{ permission }}
                                </span>
                            </div>
                        </div>

                        <!-- Other Permissions -->
                        <div v-if="getPermissionsByGroup('other').length > 0" class="border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                            <h5 class="text-md font-semibold text-slate-700 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg text-slate-600">settings</span>
                                Other Permissions
                                <span class="text-xs bg-slate-100 text-slate-800 px-2 py-1 rounded-full ml-auto">
                                    {{ getPermissionsByGroup('other').length }}
                                </span>
                            </h5>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="permission in getPermissionsByGroup('other')"
                                    :key="permission"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800"
                                >
                                    {{ permission }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Permissions Message -->
                <div v-else class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                        <span class="material-symbols-outlined text-3xl text-slate-400">security_off</span>
                    </div>
                    <h5 class="text-lg font-medium text-slate-800 mb-2">No Permissions Assigned</h5>
                    <p class="text-slate-600">This role currently has no permissions assigned to it.</p>
                </div>
                    </div>
        </template>

        <!-- Modal Footer -->
        <template #footer>
            <button
                @click="closeModal"
                class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
            >
                Close
            </button>
            <button
                @click="editRole"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-sm">edit</span>
                Edit Role
            </button>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseModal from '../../../components/ui/BaseModal.vue'

interface Role {
    id: number
    name: string
    display_name?: string
    description?: string
    permissions?: string[]
    users_count?: number
}

interface Props {
    modelValue: boolean
    role?: Role | null
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'edit-role', role: Role): void
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    role: null
})

const emit = defineEmits<Emits>()

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const closeModal = () => {
    isOpen.value = false
}

const editRole = () => {
    if (props.role) {
        emit('edit-role', props.role)
        closeModal()
    }
}

// Group permissions by category
const getPermissionsByGroup = (group: string) => {
    if (!props.role || !props.role.permissions) return []

    const groupMappings: { [key: string]: string[] } = {
        dashboard: ['dashboard.'],
        user_management: ['user_management.'],
        report: ['report.'],
        other: []
    }

    return props.role.permissions.filter((permission: string) => {
        if (group === 'other') {
            return !(groupMappings.dashboard || []).some(prefix => permission.startsWith(prefix)) &&
                   !(groupMappings.user_management || []).some(prefix => permission.startsWith(prefix)) &&
                   !(groupMappings.report || []).some(prefix => permission.startsWith(prefix))
        }
        return (groupMappings[group] || []).some(prefix => permission.startsWith(prefix))
    })
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
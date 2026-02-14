<template>
    <BaseModal v-model="isOpen" size="lg">
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
                        <!-- Dynamic Permission Categories -->
                        <div v-for="(permissions, category) in groupedPermissions" :key="category"
                            class="border border-slate-200 rounded-lg p-5" :class="getCategoryStyle(category)">
                            <h5 class="text-md font-semibold text-slate-700 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg" :class="getCategoryIconClass(category)">
                                    {{ getCategoryIcon(category) }}
                                </span>
                                {{ formatCategoryName(category) }}
                                <span class="text-xs px-2 py-1 ml-auto"
                                    :class="getCategoryBadgeClass(category)"
                                    :style="{ borderRadius: 'var(--radius-badge)' }">
                                    {{ permissions.length }}
                                </span>
                            </h5>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="permission in permissions" :key="permission"
                                    class="inline-flex items-center px-3 py-1 text-xs font-medium"
                                    :class="getPermissionBadgeClass(category)"
                                    :style="{ borderRadius: 'var(--radius-badge)' }">
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
            <Button variant="outline" @click="closeModal">
                Close
            </Button>
            <Button variant="primary" left-icon="edit" @click="editRole">
                Edit Role
            </Button>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseModal from '../ui/BaseModal.vue'
import Button from '../ui/Button.vue'

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

// Group permissions dynamically by category
const groupedPermissions = computed(() => {
    if (!props.role || !props.role.permissions) return {}

    const groups: { [key: string]: string[] } = {}

    props.role.permissions.forEach((permission: string) => {
        const category = permission.split('.')[0] || 'other'
        if (!groups[category]) {
            groups[category] = []
        }
        groups[category].push(permission)
    })

    return groups
})

// Get category display name
const formatCategoryName = (category: string): string => {
    const names: { [key: string]: string } = {
        dashboard: 'Dashboard',
        user_management: 'User Management',
        role_management: 'Role Management',
        profile: 'Profile Management',
        settings: 'Settings',
        report: 'Reports & Analytics'
    }
    return names[category] || category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Get category background style
const getCategoryStyle = (category: string): string => {
    const styles: { [key: string]: string } = {
        dashboard: 'bg-blue-50/30',
        user_management: 'bg-green-50/30',
        role_management: 'bg-orange-50/30',
        profile: 'bg-purple-50/30',
        settings: 'bg-slate-50/50',
        report: 'bg-indigo-50/30'
    }
    return styles[category] || 'bg-slate-50/50'
}

// Get category icon
const getCategoryIcon = (category: string): string => {
    const icons: { [key: string]: string } = {
        dashboard: 'dashboard',
        user_management: 'group',
        role_management: 'manage_accounts',
        profile: 'person',
        settings: 'settings',
        report: 'analytics'
    }
    return icons[category] || 'security'
}

// Get category icon class
const getCategoryIconClass = (category: string): string => {
    const classes: { [key: string]: string } = {
        dashboard: 'text-blue-600',
        user_management: 'text-green-600',
        role_management: 'text-orange-600',
        profile: 'text-purple-600',
        settings: 'text-slate-600',
        report: 'text-indigo-600'
    }
    return classes[category] || 'text-slate-600'
}

// Get category badge class
const getCategoryBadgeClass = (category: string): string => {
    const classes: { [key: string]: string } = {
        dashboard: 'bg-blue-100 text-blue-800',
        user_management: 'bg-green-100 text-green-800',
        role_management: 'bg-orange-100 text-orange-800',
        profile: 'bg-purple-100 text-purple-800',
        settings: 'bg-slate-100 text-slate-800',
        report: 'bg-indigo-100 text-indigo-800'
    }
    return classes[category] || 'bg-slate-100 text-slate-800'
}

// Get permission badge class
const getPermissionBadgeClass = (category: string): string => {
    const classes: { [key: string]: string } = {
        dashboard: 'bg-blue-100 text-blue-800',
        user_management: 'bg-green-100 text-green-800',
        role_management: 'bg-orange-100 text-orange-800',
        profile: 'bg-purple-100 text-purple-800',
        settings: 'bg-slate-100 text-slate-800',
        report: 'bg-indigo-100 text-indigo-800'
    }
    return classes[category] || 'bg-slate-100 text-slate-800'
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

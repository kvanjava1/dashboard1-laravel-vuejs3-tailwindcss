<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Roles" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="add" @click="goToAddRole">
                        Add New
                    </ActionButton>
                    <ActionDropdown>
                        <ActionDropdownItem icon="download">
                            Export CSV
                        </ActionDropdownItem>
                        <ActionDropdownItem icon="upload">
                            Import Data
                        </ActionDropdownItem>
                        <ActionDropdownItem icon="filter_list" @click="openAdvancedFilter">
                            Advanced Filter
                        </ActionDropdownItem>
                        <ActionDropdownItem icon="print">
                            Print Report
                        </ActionDropdownItem>
                    </ActionDropdown>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <ContentBox>
                <ContentBoxBody class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-3xl text-primary">group</span>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ roles.length }}</p>
                    <p class="text-sm text-slate-600">Total Roles</p>
                </ContentBoxBody>
            </ContentBox>
            <ContentBox>
                <ContentBoxBody class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-3xl text-success">admin_panel_settings</span>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ roles.filter(r => r.name === 'super_admin').length }}</p>
                    <p class="text-sm text-slate-600">Admin Roles</p>
                </ContentBoxBody>
            </ContentBox>
            <ContentBox>
                <ContentBoxBody class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-3xl text-warning">manage_accounts</span>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ roles.filter(r => r.name !== 'super_admin').length }}</p>
                    <p class="text-sm text-slate-600">Custom Roles</p>
                </ContentBoxBody>
            </ContentBox>
            <ContentBox>
                <ContentBoxBody class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-3xl text-info">security</span>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ totalPermissions }}</p>
                    <p class="text-sm text-slate-600">Total Permissions</p>
                </ContentBoxBody>
            </ContentBox>
        </div>

        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="Role Management" subtitle="Manage roles and their permissions" />
                </template>
            </ContentBoxHeader>

            <!-- Data Table -->
            <ContentBoxBody>
                <!-- Loading State -->
                <div v-if="isLoading" class="flex items-center justify-center py-12">
                    <div class="flex items-center gap-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        <span class="text-slate-600">Loading roles...</span>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-500 text-xl">error</span>
                        <div>
                            <p class="text-red-700 font-medium">Failed to load roles</p>
                            <p class="text-red-600 text-sm">{{ errorMessage }}</p>
                            <button
                                @click="fetchRoles"
                                class="mt-2 px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors"
                            >
                                Try Again
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div v-else>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Role Name
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Permissions
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Users Count
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                            <tr v-for="role in roles" :key="role.id" class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-primary/10 mr-3">
                                            <span class="material-symbols-outlined text-primary text-sm">
                                                {{ role.name === 'super_admin' ? 'admin_panel_settings' : 'manage_accounts' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900 capitalize">
                                                {{ role.display_name || role.name.replace('_', ' ') }}
                                            </div>
                                            <div class="text-xs text-slate-500">{{ role.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="permission in role.permissions.slice(0, 3)"
                                            :key="permission"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ permission.replace('_', ' ') }}
                                        </span>
                                        <span
                                            v-if="role.permissions.length > 3"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600"
                                        >
                                            +{{ role.permissions.length - 3 }} more
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ role.users_count || 0 }} users
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="editRole(role)"
                                            class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-50"
                                            title="Edit role"
                                        >
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </button>
                                        <button
                                            @click="viewPermissions(role)"
                                            class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                                            title="View permissions"
                                        >
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </button>
                                        <button
                                            v-if="role.name !== 'super_admin'"
                                            @click="deleteRole(role)"
                                            class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                                            title="Delete role"
                                        >
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination
                                :current-start="1"
                                :current-end="roles.length"
                                :total="roles.length"
                                :current-page="1"
                                :total-pages="1"
                                :rows-per-page="10"
                            />
                        </div>
                    </div>
            </ContentBoxBody>
        </ContentBox>

        <!-- Advanced Filter Modal -->
        <div v-if="showAdvancedFilter" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Advanced Filter</h3>
                <p class="text-slate-600 mb-4">Filter roles by various criteria</p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showAdvancedFilter = false"
                        class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded"
                    >
                        Cancel
                    </button>
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../../../composables/useApi'

const router = useRouter()
const { get } = useApi()
const showAdvancedFilter = ref(false)
const isLoading = ref(true)
const errorMessage = ref('')
const roles = ref<any[]>([])

const totalPermissions = computed(() => {
    const uniquePermissions = new Set()
    roles.value.forEach(role => {
        role.permissions.forEach((permission: string) => uniquePermissions.add(permission))
    })
    return uniquePermissions.size
})

// Fetch roles from API
const fetchRoles = async () => {
    try {
        isLoading.value = true
        errorMessage.value = ''

        const response = await get('/api/v1/roles')

        if (response.ok) {
            const data = await response.json()
            roles.value = data.roles || []
        } else {
            const errorData = await response.json()
            errorMessage.value = errorData.message || 'Failed to load roles'
        }
    } catch (error) {
        console.error('Error fetching roles:', error)
        errorMessage.value = 'An unexpected error occurred while loading roles'
    } finally {
        isLoading.value = false
    }
}

const goToAddRole = () => {
    router.push('/role_management/add')
}

const editRole = (role: any) => {
    console.log('Edit role:', role)
    // TODO: Navigate to edit page
}

const viewPermissions = (role: any) => {
    console.log('View permissions for role:', role)
    // TODO: Show permissions modal
}

const deleteRole = (role: any) => {
    if (confirm(`Are you sure you want to delete the "${role.display_name}" role?`)) {
        console.log('Delete role:', role)
        // TODO: Delete role
    }
}

const openAdvancedFilter = () => {
    showAdvancedFilter.value = true
}

const applyFilters = () => {
    showAdvancedFilter.value = false
    console.log('Applying filters...')
}

// Lifecycle hook
onMounted(() => {
    fetchRoles()
})

// Import components
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import ActionDropdown from '../../../components/ui/ActionDropdown.vue'
import ActionDropdownItem from '../../../components/ui/ActionDropdownItem.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import Pagination from '../../../components/ui/Pagination.vue'
</script>
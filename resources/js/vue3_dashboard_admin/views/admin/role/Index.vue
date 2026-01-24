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
                        <ActionDropdownItem icon="filter_list" @click="openAdvancedFilter">
                            Advanced Filter
                        </ActionDropdownItem>
                    </ActionDropdown>
                </PageHeaderActions>
            </template>
        </PageHeader>


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
                            <Button
                                variant="danger"
                                size="sm"
                                class="mt-2"
                                @click="() => fetchRoles(1)"
                            >
                                Try Again
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-green-600 text-lg">check_circle</span>
                        <span class="text-green-800 font-medium">{{ successMessage }}</span>
                    </div>
                </div>

                <!-- Active Filters Indicator -->
                <div v-if="hasActiveFilters" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-lg">filter_list</span>
                            <span class="text-blue-800 font-medium">Filters Active</span>
                            <span class="text-blue-600 text-sm">Showing filtered results</span>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="resetFilters"
                        >
                            Clear Filters
                        </Button>
                    </div>
                </div>

                <!-- Data Table -->
                <div>
                    <SimpleUserTable>
                        <SimpleUserTableHead>
                            <SimpleUserTableHeadRow>
                                <SimpleUserTableHeadCol>
                                    <div class="flex items-center gap-2">
                                        <span>Role Name</span>
                                        <span class="material-symbols-outlined text-slate-400 text-base">arrow_drop_down</span>
                                    </div>
                                </SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Permissions</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Users Count</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Actions</SimpleUserTableHeadCol>
                            </SimpleUserTableHeadRow>
                        </SimpleUserTableHead>

                        <SimpleUserTableBody>
                            <SimpleUserTableBodyRow v-for="role in roles" :key="role.id">
                                <SimpleUserTableBodyCol>
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
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
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
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ role.users_count || 0 }} users
                                    </span>
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
                                    <div class="flex items-center gap-2">
                                        <Button
                                            v-if="role.name !== 'super_admin'"
                                            variant="ghost"
                                            size="xs"
                                            left-icon="edit"
                                            title="Edit role"
                                            @click="editRole(role)"
                                        />
                                        <Button
                                            v-else
                                            variant="ghost"
                                            size="xs"
                                            left-icon="edit"
                                            title="Super admin role cannot be edited"
                                            disabled
                                        />
                                        <Button
                                            variant="ghost"
                                            size="xs"
                                            left-icon="visibility"
                                            title="View permissions"
                                            @click="viewPermissions(role)"
                                        />
                                        <Button
                                            v-if="role.name !== 'super_admin'"
                                            variant="ghost"
                                            size="xs"
                                            :disabled="isDeleting"
                                            :title="isDeleting ? 'Deleting...' : 'Delete role'"
                                            @click="deleteRole(role)"
                                        >
                                            <span class="material-symbols-outlined text-base" :class="{ 'animate-spin': isDeleting }">
                                                {{ isDeleting ? 'refresh' : 'delete' }}
                                            </span>
                                        </Button>
                                    </div>
                                </SimpleUserTableBodyCol>
                            </SimpleUserTableBodyRow>
                        </SimpleUserTableBody>
                    </SimpleUserTable>

                    <!-- Pagination -->
                    <Pagination
                        :current-start="currentStart"
                        :current-end="currentEnd"
                        :total="pagination.total"
                        :current-page="pagination.current_page"
                        :total-pages="pagination.total_pages"
                        :rows-per-page="pagination.per_page"
                        @prev="prevPage"
                        @next="nextPage"
                        @goto="goToPage"
                    />
                </div>
            </ContentBoxBody>
        </ContentBox>

        <!-- Advanced Filter Modal -->
        <RoleAdvancedFilterModal
            v-model="showAdvancedFilter"
            :filters="filters"
            :available-permissions="availablePermissions"
            @apply-filters="handleApplyFilters"
            @cancel="showAdvancedFilter = false"
            @reset="resetFilters"
        />

        <!-- Role Permissions Modal -->
        <RolePermissionsModal
            v-model="showPermissionsModal"
            :role="selectedRole"
            @edit-role="editRole"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../../../composables/useApi'
import { apiRoutes } from '../../../config/apiRoutes'

const router = useRouter()
const { get, del } = useApi()
const showAdvancedFilter = ref(false)
const isLoading = ref(true)
const isDeleting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const roles = ref<any[]>([])

// Permissions modal state
const showPermissionsModal = ref(false)
const selectedRole = ref<any>(null)

// Pagination state
const currentPage = ref(1)
const itemsPerPage = 5

// Pagination metadata from server
const pagination = ref({
    total: 0,
    total_pages: 0,
    current_page: 1,
    per_page: 5
})

// Advanced filter state
const filters = ref({
    search: '',
    permissions: [] as string[]
})

// Available permissions grouped by category
const availablePermissions = ref<{
    dashboard?: any[]
    user_management?: any[]
    report?: any[]
    other?: any[]
}>({})


// Check if any filters are active
const hasActiveFilters = computed(() => {
    return filters.value.search.trim() !== '' || filters.value.permissions.length > 0
})

// Pagination computed properties (using server data)
const totalPages = computed(() => pagination.value.total_pages)

const currentStart = computed(() => {
    if (roles.value.length === 0) return 0
    return (pagination.value.current_page - 1) * pagination.value.per_page + 1
})

const currentEnd = computed(() => {
    const end = pagination.value.current_page * pagination.value.per_page
    return Math.min(end, pagination.value.total)
})

// Fetch roles from API with pagination and filters
const fetchRoles = async (page = currentPage.value) => {
    try {
        isLoading.value = true
        errorMessage.value = ''

        const filterParams: any = { page, per_page: itemsPerPage }

        if (filters.value.search) {
            filterParams.search = filters.value.search
        }

        if (filters.value.permissions.length > 0) {
            filterParams.permissions = filters.value.permissions
        }

        const response = await get(apiRoutes.roles.index(filterParams))

        if (response.ok) {
            const data = await response.json()
            roles.value = data.roles || []
            pagination.value = {
                total: data.total || 0,
                total_pages: data.total_pages || 0,
                current_page: data.current_page || page,
                per_page: data.per_page || itemsPerPage
            }
            currentPage.value = data.current_page || page

            // Update available permissions if not already loaded
            if (Object.keys(availablePermissions.value).length === 0 && data.available_permissions) {
                availablePermissions.value = data.available_permissions
            }
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
    router.push({ name: 'role_management.add' })
}

const editRole = (role: any) => {
    router.push({ name: 'role_management.edit', params: { id: role.id } })
}

const viewPermissions = (role: any) => {
    selectedRole.value = role
    showPermissionsModal.value = true
}


const deleteRole = async (role: any) => {
    // Enhanced confirmation with more details
    const roleName = role.display_name || role.name
    const confirmed = confirm(
        `Are you sure you want to delete the "${roleName}" role?\n\n` +
        `⚠️ This action cannot be undone.\n\n` +
        `Users assigned: ${role.users_count || 0}`
    )

    if (!confirmed) return

    try {
        isDeleting.value = true
        errorMessage.value = ''

        // API call to delete role
        const response = await del(apiRoutes.roles.destroy(role.id))

        if (response.ok) {
            // Success: refresh roles list and show success message
            successMessage.value = `Role "${roleName}" deleted successfully`
            await fetchRoles(currentPage.value)

            // Clear success message after 3 seconds
            setTimeout(() => {
                successMessage.value = ''
            }, 3000)
        } else {
            // Handle specific error types
            const errorData = await response.json()

            if (response.status === 403) {
                errorMessage.value = 'Super admin role cannot be deleted'
            } else if (response.status === 409) {
                errorMessage.value = errorData.message || 'Cannot delete role that has assigned users'
            } else {
                errorMessage.value = errorData.message || 'Failed to delete role'
            }
        }
    } catch (error) {
        console.error('Delete role error:', error)
        errorMessage.value = 'An unexpected error occurred while deleting the role'
    } finally {
        isDeleting.value = false
    }
}

const openAdvancedFilter = () => {
    showAdvancedFilter.value = true
}

const applyFilters = () => {
    showAdvancedFilter.value = false
    fetchRoles(1) // Reset to first page when applying filters
}

const resetFilters = () => {
    filters.value = {
        search: '',
        permissions: []
    }
    fetchRoles(1) // Refresh data with cleared filters
}

// Manual apply filters handler
const handleApplyFilters = (filterData: { search: string; permissions: string[] }) => {
    filters.value.search = filterData.search
    filters.value.permissions = filterData.permissions
    showAdvancedFilter.value = false // Close modal
    fetchRoles(1) // Apply filters manually
}

// Pagination handlers
const prevPage = () => {
    if (currentPage.value > 1) {
        fetchRoles(currentPage.value - 1)
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        fetchRoles(currentPage.value + 1)
    }
}

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        fetchRoles(page)
    }
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
import BaseModal from '../../../components/ui/BaseModal.vue'
import Button from '../../../components/ui/Button.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import Pagination from '../../../components/ui/Pagination.vue'

// Table components
import SimpleUserTable from '../../../components/ui/SimpleUserTable.vue'
import SimpleUserTableHead from '../../../components/ui/SimpleUserTableHead.vue'
import SimpleUserTableHeadRow from '../../../components/ui/SimpleUserTableHeadRow.vue'
import SimpleUserTableHeadCol from '../../../components/ui/SimpleUserTableHeadCol.vue'
import SimpleUserTableBody from '../../../components/ui/SimpleUserTableBody.vue'
import SimpleUserTableBodyRow from '../../../components/ui/SimpleUserTableBodyRow.vue'
import SimpleUserTableBodyCol from '../../../components/ui/SimpleUserTableBodyCol.vue'

import RolePermissionsModal from './RolePermissionsModal.vue'
import RoleAdvancedFilterModal from './RoleAdvancedFilterModal.vue'
</script>
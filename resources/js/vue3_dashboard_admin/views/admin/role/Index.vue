<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Roles" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton v-if="canAddRole" icon="add" @click="goToAddRole">
                        Add New
                    </ActionButton>
                    <ActionDropdown v-if="canSearchRole">
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
                <LoadingState v-if="isLoading" message="Loading roles..." />

                <!-- Error State -->
                <ErrorState
                    v-else-if="errorMessage"
                    :message="errorMessage"
                    @retry="() => fetchRoles(1)"
                />

                <!-- Success Message -->
                <div v-else-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-green-600 text-lg">check_circle</span>
                        <span class="text-green-800 font-medium">{{ successMessage }}</span>
                    </div>
                </div>

                <!-- Active Filters Indicator -->
                <ActiveFiltersIndicator
                    v-else
                    :has-active-filters="hasActiveFilters"
                    @reset="resetFilters"
                />

                <!-- Empty State -->
                <EmptyState
                    v-if="!isLoading && !errorMessage && !successMessage && roles.length === 0"
                    icon="admin_panel_settings"
                    message="No roles found"
                    subtitle="Try adjusting your filters or add a new role"
                />

                <!-- Data Table -->
                <div v-else-if="!isLoading && !errorMessage && !successMessage && roles.length > 0">
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
                                            v-if="role.name !== 'super_admin' && canEditRole"
                                            variant="ghost"
                                            size="xs"
                                            left-icon="edit"
                                            title="Edit role"
                                            @click="editRole(role)"
                                        />
                                        <Button
                                            v-if="canViewRoleDetail"
                                            variant="ghost"
                                            size="xs"
                                            left-icon="visibility"
                                            title="View permissions"
                                            @click="viewPermissions(role)"
                                        />
                                        <Button
                                            v-if="role.name !== 'super_admin' && canDeleteRole"
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
                </div>

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
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../../../composables/useApi'
import { apiRoutes } from '../../../config/apiRoutes'
import { showConfirm, showToast } from '../../../composables/useSweetAlert'
import { useAuthStore } from '../../../stores/auth'

const router = useRouter()
const { get, del } = useApi()
const authStore = useAuthStore()

// Permission checks
const canAddRole = computed(() => authStore.hasPermission('role_management.add'))
const canEditRole = computed(() => authStore.hasPermission('role_management.edit'))
const canDeleteRole = computed(() => authStore.hasPermission('role_management.delete'))
const canViewRoleDetail = computed(() => authStore.hasPermission('role_management.view_detail'))
const canSearchRole = computed(() => authStore.hasPermission('role_management.search'))

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
// Pagination state (unified object, like user management)
const pagination = reactive({
    total: 0,
    total_pages: 0,
    current_page: 1,
    per_page: 5,
    from: 0,
    to: 0
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

// Pagination computed properties (like user management)
const currentStart = computed(() => {
    if (roles.value.length === 0) return 0
    return (pagination.current_page - 1) * pagination.per_page + 1
})

const currentEnd = computed(() => {
    const end = pagination.current_page * pagination.per_page
    return Math.min(end, pagination.total)
})

// Fetch roles from API with pagination and filters
const fetchRoles = async (page = pagination.current_page) => {
    try {
        isLoading.value = true
        errorMessage.value = ''

        const filterParams: any = {
            page,
            per_page: pagination.per_page
        }

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
            Object.assign(pagination, {
                total: data.total || 0,
                total_pages: data.total_pages || 0,
                current_page: data.current_page || page,
                per_page: data.per_page || pagination.per_page,
                from: data.from || currentStart.value,
                to: data.to || currentEnd.value
            })

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
    if (!canEditRole.value) {
        showToast({ icon: 'error', title: 'Access Denied', text: 'You do not have permission to edit roles.' })
        return
    }
    router.push({ name: 'role_management.edit', params: { id: role.id } })
}

const viewPermissions = (role: any) => {
    if (!canViewRoleDetail.value) {
        showToast({ icon: 'error', title: 'Access Denied', text: 'You do not have permission to view role permissions.' })
        return
    }
    selectedRole.value = role
    showPermissionsModal.value = true
}


const deleteRole = async (role: any) => {
    if (!canDeleteRole.value) {
        showToast({ icon: 'error', title: 'Access Denied', text: 'You do not have permission to delete roles.' })
        return
    }

    const roleName = role.display_name || role.name
    const confirmed = await showConfirm({
        title: `Delete role "${roleName}"?`,
        text: `This action cannot be undone.\nUsers assigned: ${role.users_count || 0}`,
        icon: 'warning',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    })
    if (!confirmed) return

    try {
        isDeleting.value = true
        errorMessage.value = ''

        // API call to delete role
        const response = await del(apiRoutes.roles.destroy(role.id))

        if (response.ok) {
            await showToast({ icon: 'success', title: `Role "${roleName}" deleted successfully`, timer: 0 })
            await fetchRoles(pagination.current_page)
        } else {
            // Handle specific error types
            const errorData = await response.json()
            const errorText = errorData.error || errorData.message || ''
            if (response.status === 403) {
                await showToast({ icon: 'error', title: 'Super admin role cannot be deleted', timer: 0 })
            } else {
                await showToast({ icon: 'error', title: 'Failed to delete role', text: errorText, timer: 0 })
            }
        }
    } catch (error) {
        console.error('Delete role error:', error)
        await showToast({ icon: 'error', title: 'An unexpected error occurred while deleting the role', timer: 0 })
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
    if (pagination.current_page > 1) {
        pagination.current_page--
        fetchRoles(pagination.current_page)
    }
}

const nextPage = () => {
    if (pagination.current_page < pagination.total_pages) {
        pagination.current_page++
        fetchRoles(pagination.current_page)
    }
}

const goToPage = (page: number) => {
    if (page >= 1 && page <= pagination.total_pages) {
        pagination.current_page = page
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
import LoadingState from '../../../components/ui/LoadingState.vue'
import ErrorState from '../../../components/ui/ErrorState.vue'
import ActiveFiltersIndicator from '../../../components/ui/ActiveFiltersIndicator.vue'
import EmptyState from '../../../components/ui/EmptyState.vue'

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
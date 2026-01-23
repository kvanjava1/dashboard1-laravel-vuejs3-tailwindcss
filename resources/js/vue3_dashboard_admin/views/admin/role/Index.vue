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
                    <p class="text-2xl font-bold text-slate-800">{{ pagination.total }}</p>
                    <p class="text-sm text-slate-600">Total Roles</p>
                </ContentBoxBody>
            </ContentBox>
            <ContentBox>
                <ContentBoxBody class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-3xl text-success">admin_panel_settings</span>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ roles.filter(r => r.name === 'super_admin').length }}</p>
                    <p class="text-sm text-slate-600">Admin Roles (Current Page)</p>
                </ContentBoxBody>
            </ContentBox>
            <ContentBox>
                <ContentBoxBody class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-3xl text-warning">manage_accounts</span>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ roles.filter(r => r.name !== 'super_admin').length }}</p>
                    <p class="text-sm text-slate-600">Custom Roles (Current Page)</p>
                </ContentBoxBody>
            </ContentBox>
            <ContentBox>
                <ContentBoxBody class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-3xl text-info">security</span>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ totalPermissions }}</p>
                    <p class="text-sm text-slate-600">Permissions (Current Page)</p>
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

                <!-- Data Table -->
                <div v-else>
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
                                            left-icon="delete"
                                            title="Delete role"
                                            @click="deleteRole(role)"
                                        />
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
        <Teleport to="body">
            <div
                v-if="showAdvancedFilter"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @click="showAdvancedFilter = false"
            >
                <div
                    class="bg-white rounded-lg p-6 max-w-md w-full mx-4"
                    @click.stop
                >
                <h3 class="text-lg font-semibold mb-4">Advanced Filter</h3>
                <p class="text-slate-600 mb-4">Filter roles by various criteria</p>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="outline"
                        @click="showAdvancedFilter = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="primary"
                        @click="applyFilters"
                    >
                        Apply Filters
                    </Button>
                </div>
            </div>
        </div>
        </Teleport>

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
const { get } = useApi()
const showAdvancedFilter = ref(false)
const isLoading = ref(true)
const errorMessage = ref('')
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

// Note: With server-side pagination, this only counts permissions from current page
// For accurate total, backend should provide this in the API response
const totalPermissions = computed(() => {
    const uniquePermissions = new Set()
    roles.value.forEach(role => {
        role.permissions.forEach((permission: string) => uniquePermissions.add(permission))
    })
    return uniquePermissions.size
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

// Fetch roles from API with pagination
const fetchRoles = async (page = currentPage.value) => {
    try {
        isLoading.value = true
        errorMessage.value = ''

        const response = await get(apiRoutes.roles.index({ page, per_page: itemsPerPage }))

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
</script>
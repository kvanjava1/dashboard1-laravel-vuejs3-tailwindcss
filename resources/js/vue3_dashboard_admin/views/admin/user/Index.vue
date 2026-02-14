<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Users" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton v-if="canAddUser" icon="add" @click="goToAddUser">
                        Add New
                    </ActionButton>
                    <ActionDropdown v-if="canSearchUser">
                        <ActionDropdownItem v-if="canSearchUser" icon="filter_list" @click="openAdvancedFilter">
                            Advanced Filter
                        </ActionDropdownItem>
                        <ActionDropdownItem icon="cleaning_services" @click="handleClearCache">
                            Clear Cache
                        </ActionDropdownItem>
                    </ActionDropdown>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="User Management" subtitle="Manage and review all registered users" />
                </template>
            </ContentBoxHeader>

            <!-- Data Table -->
            <ContentBoxBody>
                <!-- Loading State -->
                <LoadingState v-if="loading" message="Loading users..." />

                <!-- Error State -->
                <ErrorState v-else-if="error" :message="error" @retry="fetchUsers" />

                <!-- Active Filters Indicator -->
                <ActiveFiltersIndicator v-else :has-active-filters="hasActiveFilters" @reset="handleResetFilters" />

                <!-- Empty State -->
                <EmptyState v-if="!loading && !error && users.length === 0" icon="group" message="No users found"
                    subtitle="Try adjusting your filters or add a new user" />

                <!-- Data Table -->
                <div v-else-if="!loading && !error && users.length > 0">
                    <SimpleUserTable>
                        <SimpleUserTableHead>
                            <SimpleUserTableHeadRow>
                                <SimpleUserTableHeadCol>
                                    <div class="flex items-center gap-2">
                                        <span>User</span>
                                        <span
                                            class="material-symbols-outlined text-slate-400 text-base">arrow_drop_down</span>
                                    </div>
                                </SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Role</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Status</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Banned Status</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Joined Date</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Actions</SimpleUserTableHeadCol>
                            </SimpleUserTableHeadRow>
                        </SimpleUserTableHead>

                        <SimpleUserTableBody>
                            <SimpleUserTableBodyRow v-for="user in users" :key="user.id">
                                <SimpleUserTableBodyCol>
                                    <UserCellUser :name="user.name" :email="user.email" :avatar="user.avatar || ''" />
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
                                    <UserCellRole :role="user.role || ''" />
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
                                    <UserCellStatus :status="user.status" />
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
                                    <span class="text-sm text-slate-700">{{ user.is_banned ? 'Banned' : 'Not Banned'
                                        }}</span>
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
                                    <span class="text-sm text-slate-700">{{ user.joined_date }}</span>
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
                                    <CellActions @edit="handleEdit(user)" @delete="handleDelete(user)"
                                        @view="() => openUserDetail(user)" @ban="handleBan(user)"
                                        :show-edit="canEditUser && user.protection?.can_edit"
                                        :show-delete="canDeleteUser && user.protection?.can_delete"
                                        :show-view="canViewUserDetail" :show-ban="canBanUser && user.protection?.can_ban" :user="user" />
                                </SimpleUserTableBodyCol>
                            </SimpleUserTableBodyRow>
                        </SimpleUserTableBody>
                    </SimpleUserTable>

                    <!-- Pagination -->
                    <Pagination :current-start="currentStart" :current-end="currentEnd" :total="pagination.total"
                        :current-page="pagination.current_page" :total-pages="pagination.total_pages"
                        :rows-per-page="pagination.per_page" @prev="prevPage" @next="nextPage" @goto="goToPage" />
                </div>
            </ContentBoxBody>
        </ContentBox>

        <!-- Advanced Filter Modal -->
        <UserAdvancedFilterModal v-model="showAdvancedFilter" :initial-filters="currentFilters"
            :available-roles="availableRoles" :status-options="statusOptions" @apply="handleApplyFilters"
            @reset="handleResetFilters" />

        <!-- User Detail Modal -->
        <UserDetailModal v-model="showUserDetail" :user="selectedUser" />
    </AdminLayout>
</template>

<script setup lang="ts">
// Vue imports
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

// Composables and stores
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'
import { showConfirm, showToast } from '@/composables/useSweetAlert'
import { useAuthStore } from '@/stores/auth'
import { useUserData, type User } from '@/composables/user/useUserData'

// Third-party libraries
import Swal from 'sweetalert2'

// Component imports
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import ActionDropdown from '../../../components/ui/ActionDropdown.vue'
import ActionDropdownItem from '../../../components/ui/ActionDropdownItem.vue'
import SimpleUserTable from '../../../components/ui/SimpleUserTable.vue'
import SimpleUserTableHead from '../../../components/ui/SimpleUserTableHead.vue'
import SimpleUserTableHeadRow from '../../../components/ui/SimpleUserTableHeadRow.vue'
import SimpleUserTableHeadCol from '../../../components/ui/SimpleUserTableHeadCol.vue'
import SimpleUserTableBody from '../../../components/ui/SimpleUserTableBody.vue'
import SimpleUserTableBodyRow from '../../../components/ui/SimpleUserTableBodyRow.vue'
import SimpleUserTableBodyCol from '../../../components/ui/SimpleUserTableBodyCol.vue'
import UserCellUser from '../../../components/ui/UserCellUser.vue'
import UserCellRole from '../../../components/ui/UserCellRole.vue'
import UserCellStatus from '../../../components/ui/UserCellStatus.vue'
import CellActions from '../../../components/ui/CellActions.vue'
import Pagination from '../../../components/ui/Pagination.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import ErrorState from '../../../components/ui/ErrorState.vue'
import ActiveFiltersIndicator from '../../../components/ui/ActiveFiltersIndicator.vue'
import EmptyState from '../../../components/ui/EmptyState.vue'
import UserAdvancedFilterModal from '../../../components/user/UserAdvancedFilterModal.vue'
import UserDetailModal from '../../../components/user/UserDetailModal.vue'

const router = useRouter()
const { get, del, post } = useApi()
const authStore = useAuthStore()
const { fetchUsers: fetchUsersList, loading: fetchLoading, error: fetchError } = useUserData()

// User detail modal state
const showUserDetail = ref(false)
const selectedUser = ref<User | null>(null)

// Pagination indicator computed properties (like role management)
const currentStart = computed(() => {
    if (users.value.length === 0) return 0
    return (pagination.current_page - 1) * pagination.per_page + 1
})

const currentEnd = computed(() => {
    const end = pagination.current_page * pagination.per_page
    return Math.min(end, pagination.total)
})

// Permission checks
const canAddUser = computed(() => authStore.hasPermission('user_management.add'))
const canEditUser = computed(() => authStore.hasPermission('user_management.edit'))
const canDeleteUser = computed(() => authStore.hasPermission('user_management.delete'))
const canViewUserDetail = computed(() => authStore.hasPermission('user_management.view_detail'))
const canSearchUser = computed(() => authStore.hasPermission('user_management.search'))
const canBanUser = computed(() => authStore.hasPermission('user_management.ban'))

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return currentFilters.search.trim() !== '' ||
        currentFilters.name.trim() !== '' ||
        currentFilters.email.trim() !== '' ||
        currentFilters.role !== '' ||
        currentFilters.status !== '' ||
        currentFilters.is_banned !== '' ||
        currentFilters.date_from !== '' ||
        currentFilters.date_to !== '' ||
        currentFilters.sort_by !== 'created_at' ||
        currentFilters.sort_order !== 'desc'
})

// Modal state
const showAdvancedFilter = ref(false)

// Current filters
const currentFilters = reactive({
    search: '',
    name: '',
    email: '',
    role: '',
    status: '',
    is_banned: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc'
})

// UI State (using composable state)
const loading = fetchLoading
const error = fetchError

// Pagination
const pagination = reactive({
    total: 0,
    total_pages: 0,
    current_page: 1,
    per_page: 5,
    from: 0,
    to: 0
})

// Available roles and status options from API
const availableRoles = ref([])
const statusOptions = ref([])

const users = ref<User[]>([])

// Fetch users from API using composable
const fetchUsers = async () => {
    try {
        const params = {
            page: pagination.current_page,
            per_page: pagination.per_page,
            search: currentFilters.search,
            name: currentFilters.name,
            email: currentFilters.email,
            role: currentFilters.role,
            status: currentFilters.status,
            is_banned: currentFilters.is_banned,
            date_from: currentFilters.date_from,
            date_to: currentFilters.date_to,
            sort_by: currentFilters.sort_by,
            sort_order: currentFilters.sort_order
        }

        const data = await fetchUsersList(params)

        // Map data back to our refs
        users.value = data.users

        // Update pagination meta
        Object.assign(pagination, {
            total: data.meta.total,
            total_pages: data.meta.total_pages,
            current_page: data.meta.current_page,
            per_page: data.meta.per_page,
            from: data.meta.from,
            to: data.meta.to
        })

        // Store available roles and status options
        availableRoles.value = data.filters.available_roles
        statusOptions.value = data.filters.status_options

    } catch (err: any) {
        // Error is handled by the composable's error ref
        console.error('Error in Index.vue fetchUsers:', err)
    }
}

// Pagination handlers
const prevPage = () => {
    if (pagination.current_page > 1) {
        pagination.current_page--
        fetchUsers()
    }
}

const nextPage = () => {
    if (pagination.current_page < pagination.total_pages) {
        pagination.current_page++
        fetchUsers()
    }
}

const goToPage = (page: number) => {
    if (page >= 1 && page <= pagination.total_pages) {
        pagination.current_page = page
        fetchUsers()
    }
}

// User actions
const handleEdit = (user: User) => {
    console.log('Edit user:', user)
    // TODO: Navigate to edit page
    router.push({ name: 'user_management.edit', params: { id: user.id } })
}

const handleDelete = async (user: User) => {
    const confirmed = await showConfirm({
        title: `Delete user "${user.name}"?`,
        text: 'This action cannot be undone.',
        icon: 'warning',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    })

    if (!confirmed) return

    try {
        const url = apiRoutes.users.destroy(user.id)
        const response = await del(url)

        if (!response.ok) {
            let errorText = ''
            try {
                const errorData = await response.json()
                errorText = errorData.error || errorData.message || response.statusText
            } catch {
                errorText = response.statusText
            }
            await showToast({ icon: 'error', title: 'Failed to delete user', text: errorText, timer: 0 })
            return
        }

        // Remove user from local list
        users.value = users.value.filter(u => u.id !== user.id)

        // Refresh user count
        pagination.total--

        await showToast({ icon: 'success', title: 'User deleted successfully', timer: 0 })
    } catch (err: any) {
        await showToast({ icon: 'error', title: 'Failed to delete user', text: err?.message ?? '', timer: 0 })
        console.error('Error deleting user:', err)
    }
}

const handleBan = async (user: User) => {
    // Navigate to ban page
    router.push({ name: 'user_management.ban', params: { id: user.id } })
}

const handleView = (user: User) => {
    console.log('View user:', user)
    // TODO: Navigate to user detail page
    router.push({ name: 'user_management.view', params: { id: user.id } })
}

// Open user detail modal (moved here to group with other user actions)
const openUserDetail = async (user: User) => {
    if (!canViewUserDetail.value) {
        showToast({ icon: 'error', title: 'Access Denied', text: 'You do not have permission to view user details.' })
        return
    }

    try {
        const response = await get(apiRoutes.users.show(user.id))

        if (!response.ok) {
            let errorText = ''
            try {
                const errorData = await response.json()
                errorText = errorData.error || errorData.message || response.statusText
            } catch {
                errorText = response.statusText
            }
            throw new Error(errorText || 'Failed to fetch user details')
        }

        const data = await response.json()
        selectedUser.value = user
        showUserDetail.value = true
    } catch (err: any) {
        console.error('Failed to fetch user details:', err)
        showToast({ icon: 'error', title: 'Failed to load user details', text: err?.message ?? '' })
    }
}

const goToAddUser = () => {
    router.push({ name: 'user_management.add' })
}

const openAdvancedFilter = () => {
    showAdvancedFilter.value = true
}

const handleApplyFilters = (filters: any) => {
    Object.assign(currentFilters, filters)
    pagination.current_page = 1 // Reset to first page when filters change
    fetchUsers()
}

const handleResetFilters = () => {
    Object.assign(currentFilters, {
        search: '',
        name: '',
        email: '',
        role: '',
        status: '',
        is_banned: '',
        date_from: '',
        date_to: '',
        sort_by: 'created_at',
        sort_order: 'desc'
    })
    pagination.current_page = 1
    fetchUsers()
}

const handleClearCache = async () => {
    const ok = await showConfirm({
        title: 'Clear Users Cache?',
        text: 'This will force the system to reload all user data from the database.',
        icon: 'info',
        confirmButtonText: 'Yes, clear it'
    })

    if (!ok) return

    try {
        const response = await post(apiRoutes.users.clearCache, {})
        if (response.ok) {
            await showToast({ icon: 'success', title: 'Cache cleared', timer: 1200 })
            await fetchUsers()
        }
    } catch (e: any) {
        await showToast({ icon: 'error', title: 'Error', text: e.message })
    }
}

// Initial fetch
onMounted(() => {
    fetchUsers()
})
</script>

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
                <div v-if="loading" class="py-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    <p class="mt-2 text-sm text-slate-600">Loading users...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="py-8 text-center">
                    <span class="material-symbols-outlined text-danger text-4xl">error</span>
                    <p class="mt-2 text-sm text-slate-700">{{ error }}</p>
                    <button @click="fetchUsers" class="mt-4 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                        Try Again
                    </button>
                </div>

                <!-- Active Filters Indicator -->
                <div v-if="hasActiveFilters" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-lg">filter_list</span>
                            <span class="text-blue-800 font-medium">Filters Active</span>
                            <span class="text-blue-600 text-sm">Showing filtered results</span>
                        </div>
                        <button
                            @click="handleResetFilters"
                            class="px-3 py-1.5 text-sm font-medium text-blue-700 bg-white border border-blue-300 rounded-md hover:bg-blue-50 transition-colors"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="users.length === 0" class="py-12 text-center">
                    <span class="material-symbols-outlined text-slate-400 text-4xl">group</span>
                    <p class="mt-2 text-sm text-slate-700">No users found</p>
                    <p class="text-xs text-slate-500 mt-1">Try adjusting your filters or add a new user</p>
                </div>

                <!-- Data Table -->
                <div v-else>
                    <SimpleUserTable>
                        <SimpleUserTableHead>
                            <SimpleUserTableHeadRow>
                                <SimpleUserTableHeadCol>
                                    <div class="flex items-center gap-2">
                                        <span>User</span>
                                        <span class="material-symbols-outlined text-slate-400 text-base">arrow_drop_down</span>
                                    </div>
                                </SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Role</SimpleUserTableHeadCol>
                                <SimpleUserTableHeadCol>Status</SimpleUserTableHeadCol>
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
                                    <span class="text-sm text-slate-700">{{ user.joined_date }}</span>
                                </SimpleUserTableBodyCol>
                                <SimpleUserTableBodyCol>
                                    <UserCellActions
                                        @edit="handleEdit(user)"
                                        @delete="handleDelete(user)"
                                        @view="() => openUserDetail(user)"
                                        :show-edit="canEditUser"
                                        :show-delete="canDeleteUser && user.role !== 'super_admin' && user.email !== 'super@admin.com'"
                                        :show-view="canViewUserDetail"
                                    />
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
        <UserAdvancedFilterModal
            v-model="showAdvancedFilter"
            :initial-filters="currentFilters"
            :available-roles="availableRoles"
            :status-options="statusOptions"
            @apply="handleApplyFilters"
            @reset="handleResetFilters"
        />

        <!-- User Detail Modal -->
        <UserDetailModal
            v-model="showUserDetail"
            :user="selectedUser"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import UserDetailModal from './UserDetailModal.vue'
// User detail modal state
const showUserDetail = ref(false)
const selectedUser = ref<User | null>(null)

function openUserDetail(user: User) {
    // Check permission before opening user detail
    if (!canViewUserDetail.value) {
        showToast({ icon: 'error', title: 'Access Denied', text: 'You do not have permission to view user details.' })
        return
    }

    // Fetch full user details from API
    get(`/api/v1/users/${user.id}`)
        .then(async (response) => {
            const data = await response.json()
            selectedUser.value = data.user
            showUserDetail.value = true
        })
        .catch((err) => {
            console.error('Failed to fetch user details:', err)
            showToast({ icon: 'error', title: 'Failed to load user details', text: err?.message ?? '' })
        })
}
import { ref, reactive, computed, onMounted } from 'vue'
// Pagination indicator computed properties (like role management)
const currentStart = computed(() => {
    if (users.value.length === 0) return 0
    return (pagination.current_page - 1) * pagination.per_page + 1
})

const currentEnd = computed(() => {
    const end = pagination.current_page * pagination.per_page
    return Math.min(end, pagination.total)
})
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'
import { showConfirm, showToast } from '@/composables/useSweetAlert'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const { get, del } = useApi()
const authStore = useAuthStore()

// Permission checks
const canAddUser = computed(() => authStore.hasPermission('user_management.add'))
const canEditUser = computed(() => authStore.hasPermission('user_management.edit'))
const canDeleteUser = computed(() => authStore.hasPermission('user_management.delete'))
const canViewUserDetail = computed(() => authStore.hasPermission('user_management.view_detail'))
const canSearchUser = computed(() => authStore.hasPermission('user_management.search'))

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return currentFilters.search.trim() !== '' ||
           currentFilters.name.trim() !== '' ||
           currentFilters.email.trim() !== '' ||
           currentFilters.phone.trim() !== '' ||
           currentFilters.username.trim() !== '' ||
           currentFilters.location.trim() !== '' ||
           currentFilters.bio.trim() !== '' ||
           currentFilters.role !== '' ||
           currentFilters.status !== '' ||
           currentFilters.is_banned !== '' ||
           currentFilters.date_of_birth_from !== '' ||
           currentFilters.date_of_birth_to !== '' ||
           currentFilters.date_from !== '' ||
           currentFilters.date_to !== '' ||
           currentFilters.timezone.trim() !== '' ||
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
    phone: '',
    username: '',
    location: '',
    bio: '',
    role: '',
    status: '',
    is_banned: '',
    date_of_birth_from: '',
    date_of_birth_to: '',
    date_from: '',
    date_to: '',
    timezone: '',
    sort_by: 'created_at',
    sort_order: 'desc'
})

// UI State
const loading = ref(false)
const error = ref<string | null>(null)

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
import UserCellActions from '../../../components/ui/UserCellActions.vue'
import Pagination from '../../../components/ui/Pagination.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import UserAdvancedFilterModal from './UserAdvancedFilterModal.vue'

interface User {
    id: number
    name: string
    email: string
    username?: string
    phone?: string
    status: string
    bio?: string
    date_of_birth?: string
    location?: string
    timezone?: string
    last_activity?: string
    last_activity_formatted?: string
    is_banned?: boolean
    ban_reason?: string
    banned_until?: string
    banned_until_formatted?: string
    profile_image?: string
    profile_image_url?: string
    role?: string
    role_display_name?: string
    created_at: string
    updated_at: string
    joined_date: string
    avatar?: string  // For backward compatibility with existing code
}

const users = ref<User[]>([])

// Fetch users from API
const fetchUsers = async () => {
    loading.value = true
    error.value = null

    try {
        const params = {
            page: pagination.current_page,
            per_page: pagination.per_page,
            search: currentFilters.search,
            name: currentFilters.name,
            email: currentFilters.email,
            phone: currentFilters.phone,
            username: currentFilters.username,
            location: currentFilters.location,
            bio: currentFilters.bio,
            role: currentFilters.role,
            status: currentFilters.status,
            is_banned: currentFilters.is_banned,
            date_of_birth_from: currentFilters.date_of_birth_from,
            date_of_birth_to: currentFilters.date_of_birth_to,
            date_from: currentFilters.date_from,
            date_to: currentFilters.date_to,
            timezone: currentFilters.timezone,
            sort_by: currentFilters.sort_by,
            sort_order: currentFilters.sort_order
        }

        const url = apiRoutes.users.index(params)
        const response = await get(url)

        if (!response.ok) {
            throw new Error(`Failed to fetch users: ${response.status} ${response.statusText}`)
        }

        const data = await response.json()

        // Map API data to frontend User interface
        users.value = data.data.map((user: any) => ({
            id: user.id,
            name: user.name,
            email: user.email,
            avatar: user.profile_image_url || '',
            role: user.role_display_name || user.role || '',
            status: user.status || '',
            joined_date: user.joined_date
        }))

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
        error.value = err.message || 'Failed to load users'
        console.error('Error fetching users:', err)
    } finally {
        loading.value = false
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

const handleView = (user: User) => {
    console.log('View user:', user)
    // TODO: Navigate to user detail page
    router.push({ name: 'user_management.view', params: { id: user.id } })
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
        phone: '',
        username: '',
        location: '',
        bio: '',
        role: '',
        status: '',
        is_banned: '',
        date_of_birth_from: '',
        date_of_birth_to: '',
        date_from: '',
        date_to: '',
        timezone: '',
        sort_by: 'created_at',
        sort_order: 'desc'
    })
    pagination.current_page = 1
    fetchUsers()
}

// Initial fetch
onMounted(() => {
    fetchUsers()
})
</script>

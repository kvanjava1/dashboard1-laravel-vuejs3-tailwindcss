<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Users" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="add" @click="goToAddUser">
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

        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="User Management" subtitle="Manage and review all registered users" />
                </template>
            </ContentBoxHeader>

            <!-- Data Table -->
            <ContentBoxBody>
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
                                <UserCellUser :name="user.name" :email="user.email" :avatar="user.avatar" />
                            </SimpleUserTableBodyCol>
                            <SimpleUserTableBodyCol>
                                <UserCellRole :role="user.role" />
                            </SimpleUserTableBodyCol>
                            <SimpleUserTableBodyCol>
                                <UserCellStatus :status="user.status" />
                            </SimpleUserTableBodyCol>
                            <SimpleUserTableBodyCol>
                                <span class="text-sm text-slate-700">{{ user.joinedDate }}</span>
                            </SimpleUserTableBodyCol>
                            <SimpleUserTableBodyCol>
                                <UserCellActions @edit="handleEdit(user)" @delete="handleDelete(user)"
                                    @view="handleView(user)" />
                            </SimpleUserTableBodyCol>
                        </SimpleUserTableBodyRow>
                    </SimpleUserTableBody>
                </SimpleUserTable>

                <!-- Pagination -->
                <Pagination :current-start="1" :current-end="5" :total="24" :current-page="1" :total-pages="4"
                    :rows-per-page="10" @prev="prevPage" @next="nextPage" @goto="goToPage" />
            </ContentBoxBody>
        </ContentBox>

        <!-- Advanced Filter Modal -->
        <UserAdvancedFilterModal
            v-model="showAdvancedFilter"
            :initial-filters="currentFilters"
            @apply="handleApplyFilters"
            @reset="handleResetFilters"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Modal state
const showAdvancedFilter = ref(false)

// Current filters
const currentFilters = reactive({
    search: '',
    name: '',
    email: '',
    role: '',
    status: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc'
})
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
    avatar: string
    role: 'Administrator' | 'Editor' | 'Viewer'
    status: 'Active' | 'Pending' | 'Inactive'
    joinedDate: string
}

const users = ref<User[]>([
    {
        id: 1,
        name: 'Alex Johnson',
        email: 'alex.j@example.com',
        avatar: 'https://lh3.googleusercontent.com/a/ACg8ocKx_nV7qJmIBqGv6A4gH9YwYQdX7hD6tZ8bQjYF=s100-c',
        role: 'Administrator',
        status: 'Active',
        joinedDate: 'Jan 15, 2024'
    },
    {
        id: 2,
        name: 'Maria Garcia',
        email: 'maria.g@example.com',
        avatar: 'https://lh3.googleusercontent.com/a/ACg8ocJbV7hD6tZ8bQjYFKx_nV7qJmIBqGv6A4gH9YwYQdX=s100-c',
        role: 'Editor',
        status: 'Active',
        joinedDate: 'Feb 03, 2024'
    },
    {
        id: 3,
        name: 'David Chen',
        email: 'david.c@example.com',
        avatar: 'https://lh3.googleusercontent.com/a/ACg8ocLhD6tZ8bQjYFKx_nV7qJmIBqGv6A4gH9YwYQdX7qJm=s100-c',
        role: 'Viewer',
        status: 'Pending',
        joinedDate: 'Mar 22, 2024'
    },
    {
        id: 4,
        name: 'Sarah Williams',
        email: 'sarah.w@example.com',
        avatar: 'https://lh3.googleusercontent.com/a/ACg8ocN7qJmIBqGv6A4gH9YwYQdXhD6tZ8bQjYFKx_nV7qJm=s100-c',
        role: 'Administrator',
        status: 'Active',
        joinedDate: 'Apr 10, 2024'
    },
    {
        id: 5,
        name: 'Michael Brown',
        email: 'michael.b@example.com',
        avatar: 'https://lh3.googleusercontent.com/a/ACg8ocO7qJmIBqGv6A4gH9YwYQdXhD6tZ8bQjYFKx_nV7qJmIB=s100-c',
        role: 'Editor',
        status: 'Inactive',
        joinedDate: 'May 05, 2024'
    }
])

const handleEdit = (user: User) => {
    console.log('Edit user:', user)
}

const handleDelete = (user: User) => {
    console.log('Delete user:', user)
}

const handleView = (user: User) => {
    console.log('View user:', user)
}

const prevPage = () => {
    console.log('Previous page')
}

const nextPage = () => {
    console.log('Next page')
}

const goToPage = (page: number) => {
    console.log('Go to page:', page)
}

const goToAddUser = () => {
    router.push('/user_management/add')
}

const openAdvancedFilter = () => {
    showAdvancedFilter.value = true
}

const handleApplyFilters = (filters: any) => {
    Object.assign(currentFilters, filters)
    console.log('Applied filters:', filters)
    // TODO: Implement actual filtering logic here
    // This would typically make an API call with the filters
}

const handleResetFilters = () => {
    Object.assign(currentFilters, {
        search: '',
        name: '',
        email: '',
        role: '',
        status: '',
        date_from: '',
        date_to: '',
        sort_by: 'created_at',
        sort_order: 'desc'
    })
    console.log('Filters reset')
    // TODO: Reset the data to original state
}
</script>
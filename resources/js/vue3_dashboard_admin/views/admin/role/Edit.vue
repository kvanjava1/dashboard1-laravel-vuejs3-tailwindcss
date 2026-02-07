<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Edit Role" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="arrow_back" @click="goBack">
                        Back
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Loading State -->
        <LoadingState v-if="isLoadingRole" message="Loading role..." />

        <!-- Error State -->
        <div v-else-if="roleError" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-500 text-xl">error</span>
                <div>
                    <p class="text-red-700 font-medium">Failed to load role</p>
                    <p class="text-red-600 text-sm">{{ roleError }}</p>
                    <Button variant="danger" size="sm" class="mt-2" @click="fetchRole">
                        Try Again
                    </Button>
                </div>
            </div>
        </div>

        <!-- Role Form -->
        <RoleForm v-else :is-edit="true" :initial-data="roleData" @cancel="goBack" @success="handleSuccess" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
// Composables and stores
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'
import { useRoleData } from '@/composables/role/useRoleData'

const router = useRouter()
const route = useRoute()
const { fetchRole: fetchRoleData, loading: fetchLoading, error: fetchError } = useRoleData()

// State
const isLoadingRole = fetchLoading
const roleError = fetchError
const roleData = ref<any>(null)

// Get role ID from route params
const roleId = computed(() => route.params.id as string)

// Navigation
const goBack = () => {
    router.push({ name: 'role_management.index' })
}

// Fetch role data
const fetchRole = async () => {
    try {
        const id = parseInt(roleId.value)
        if (isNaN(id)) {
            router.push({ name: 'role_management.index' })
            return
        }

        const role = await fetchRoleData(id)

        // Prevent editing super admin role
        if (role.name === 'super_admin') {
            router.push({ name: 'role_management.index' })
            return
        }

        // Set role data for form
        roleData.value = {
            id: role.id,
            name: role.name,
            display_name: role.display_name || '',
            description: role.description || '',
            permissions: role.permissions || []
        }
    } catch (error) {
        console.error('Error in Edit.vue fetchRole:', error)
    }
}

const handleSuccess = (role: any) => {
    // Redirect to role list after successful update
    router.push({ name: 'role_management.index' })
}

// Lifecycle hook
onMounted(async () => {
    await fetchRole()
})

// Import components
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import Button from '../../../components/ui/Button.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import RoleForm from '../../../components/role/RoleForm.vue'
</script>
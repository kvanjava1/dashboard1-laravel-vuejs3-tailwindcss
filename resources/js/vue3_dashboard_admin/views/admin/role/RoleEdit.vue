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
        <div v-if="isLoadingRole" class="flex items-center justify-center py-12">
            <div class="flex items-center gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <span class="text-slate-600">Loading role...</span>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="roleError" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-500 text-xl">error</span>
                <div>
                    <p class="text-red-700 font-medium">Failed to load role</p>
                    <p class="text-red-600 text-sm">{{ roleError }}</p>
                    <Button
                        variant="danger"
                        size="sm"
                        class="mt-2"
                        @click="fetchRole"
                    >
                        Try Again
                    </Button>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div v-else class="space-y-6">
            <!-- Role Information Card -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">manage_accounts</span>
                            <ContentBoxTitle title="Role Information" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Role Name (Read-only) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Role Name
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                readonly
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed"
                            />
                            <p class="text-xs text-slate-500 mt-1">Role name cannot be changed after creation</p>
                        </div>

                        <!-- Display Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Display Name <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.display_name"
                                type="text"
                                placeholder="e.g., Content Editor"
                                required
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            />
                            <p class="text-xs text-slate-500 mt-1">Human-readable name for the role</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Description
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Describe what this role can do..."
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
                        ></textarea>
                    </div>
                </ContentBoxBody>
            </ContentBox>

            <!-- Permissions Card -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">security</span>
                            <ContentBoxTitle title="Permissions" subtitle="Select the permissions this role should have" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <!-- Permissions Loading State -->
                    <div v-if="isLoadingPermissions" class="flex items-center justify-center py-12">
                        <div class="flex items-center gap-3">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            <span class="text-slate-600">Loading permissions...</span>
                        </div>
                    </div>

                    <!-- Permissions Error State -->
                    <div v-else-if="permissionsError" class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-red-500 text-xl">error</span>
                            <div>
                                <p class="text-red-700 font-medium">Failed to load permissions</p>
                                <p class="text-red-600 text-sm">{{ permissionsError }}</p>
                                <Button
                                    variant="danger"
                                    size="sm"
                                    class="mt-2"
                                    @click="fetchPermissions"
                                >
                                    Try Again
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Content -->
                    <div v-else>
                        <!-- Quick Select Actions -->
                        <div class="flex flex-wrap gap-3 mb-6">
                            <Button
                                variant="ghost"
                                left-icon="select_all"
                                @click="selectAllPermissions"
                            >
                                Select All
                            </Button>
                            <Button
                                variant="ghost"
                                left-icon="clear_all"
                                @click="clearAllPermissions"
                            >
                                Clear All
                            </Button>
                            <Button
                                variant="ghost"
                                left-icon="settings"
                                @click="selectCommonPermissions"
                            >
                                Common Set
                            </Button>
                        </div>

                        <!-- Permission Groups -->
                        <div class="space-y-6">
                            <!-- Dashboard Permissions -->
                            <div v-if="(dashboardPermissions || []).length > 0">
                                <h4 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">dashboard</span>
                                    Dashboard
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <label v-for="permission in dashboardPermissions || []" :key="permission.name"
                                           class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                                        <input
                                            v-model="form.permissions"
                                            :value="permission.name"
                                            type="checkbox"
                                            class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/30"
                                        />
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-slate-700">{{ permission.label }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- User Management Permissions -->
                            <div v-if="(userPermissions || []).length > 0">
                                <h4 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">group</span>
                                    User Management
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <label v-for="permission in userPermissions || []" :key="permission.name"
                                           class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                                        <input
                                            v-model="form.permissions"
                                            :value="permission.name"
                                            type="checkbox"
                                            class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/30"
                                        />
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-slate-700">{{ permission.label }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Report Permissions -->
                            <div v-if="(reportPermissions || []).length > 0">
                                <h4 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">analytics</span>
                                    Reports
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <label v-for="permission in reportPermissions || []" :key="permission.name"
                                           class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                                        <input
                                            v-model="form.permissions"
                                            :value="permission.name"
                                            type="checkbox"
                                            class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/30"
                                        />
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-slate-700">{{ permission.label }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Other Permissions -->
                            <div v-if="(otherPermissions || []).length > 0">
                                <h4 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">settings</span>
                                    Other Permissions
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <label v-for="permission in otherPermissions || []" :key="permission.name"
                                           class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                                        <input
                                            v-model="form.permissions"
                                            :value="permission.name"
                                            type="checkbox"
                                            class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/30"
                                        />
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-slate-700">{{ permission.label }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- No Permissions Message -->
                            <div v-if="(dashboardPermissions || []).length === 0 && (userPermissions || []).length === 0 && (reportPermissions || []).length === 0 && (otherPermissions || []).length === 0"
                                 class="text-center py-8">
                                <span class="material-symbols-outlined text-4xl text-slate-400 mb-2 block">security</span>
                                <p class="text-slate-600">No permissions available</p>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Permissions Summary -->
                    <div class="mt-6 p-4 bg-slate-50 rounded-lg">
                        <h5 class="text-sm font-semibold text-slate-700 mb-2">
                            Selected Permissions ({{ form.permissions.length }})
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="permission in form.permissions"
                                :key="permission"
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary"
                            >
                                {{ permission }}
                            </span>
                        </div>
                        <p v-if="form.permissions.length === 0" class="text-xs text-slate-500">
                            No permissions selected
                        </p>
                    </div>
                </ContentBoxBody>
            </ContentBox>

            <!-- Error Message -->
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-500 text-lg">error</span>
                    <p class="text-red-700 text-sm font-medium">{{ errorMessage }}</p>
                </div>
            </div>

            <!-- Form Actions Card -->
            <ContentBox>
                <ContentBoxBody>
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                        <Button
                            variant="outline"
                            class="w-full sm:w-auto"
                            left-icon="close"
                            @click="goBack"
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="primary"
                            class="w-full sm:w-auto"
                            left-icon="save"
                            :loading="isSubmitting"
                            @click="handleSubmit"
                        >
                            {{ isSubmitting ? 'Updating...' : 'Update Role' }}
                        </Button>
                    </div>
                </ContentBoxBody>
            </ContentBox>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useApi } from '../../../composables/useApi'

const router = useRouter()
const route = useRoute()
const { get, put } = useApi()

// State
const isSubmitting = ref(false)
const errorMessage = ref('')
const isLoadingPermissions = ref(true)
const permissionsError = ref('')
const isLoadingRole = ref(true)
const roleError = ref('')

// Get role ID from route params
const roleId = computed(() => route.params.id as string)

// Permissions data
const permissions = ref<{
    dashboard: any[]
    user_management: any[]
    report: any[]
    other: any[]
}>({
    dashboard: [],
    user_management: [],
    report: [],
    other: []
})

// Form data
const form = reactive({
    name: '',
    display_name: '',
    description: '',
    permissions: [] as string[]
})

// Computed permissions
const dashboardPermissions = computed(() => permissions.value?.dashboard || [])
const userPermissions = computed(() => permissions.value?.user_management || [])
const reportPermissions = computed(() => permissions.value?.report || [])
const otherPermissions = computed(() => permissions.value?.other || [])

// Navigation
const goBack = () => {
    router.push({ name: 'role_management.index' })
}

// Fetch permissions from API
const fetchPermissions = async () => {
    try {
        isLoadingPermissions.value = true
        permissionsError.value = ''

        const response = await get('/api/v1/permissions/grouped')

        if (response.ok) {
            const data = await response.json()
            permissions.value = data.permissions
        } else {
            const errorData = await response.json()
            permissionsError.value = errorData.message || 'Failed to load permissions'
        }
    } catch (error) {
        console.error('Error fetching permissions:', error)
        permissionsError.value = 'An unexpected error occurred while loading permissions'
    } finally {
        isLoadingPermissions.value = false
    }
}

// Fetch role data
const fetchRole = async () => {
    try {
        isLoadingRole.value = true
        roleError.value = ''

        const response = await get(`/api/v1/roles/${roleId.value}`)

        if (response.ok) {
            const data = await response.json()
            const role = data.role

            // Populate form with existing data
            form.name = role.name
            form.display_name = role.display_name || ''
            form.description = role.description || ''
            form.permissions = role.permissions || []
        } else {
            const errorData = await response.json()
            roleError.value = errorData.message || 'Failed to load role data'
        }
    } catch (error) {
        console.error('Error fetching role:', error)
        roleError.value = 'An unexpected error occurred while loading role data'
    } finally {
        isLoadingRole.value = false
    }
}

// Form validation
const validateForm = (): boolean => {
    if (!form.display_name.trim()) {
        errorMessage.value = 'Display name is required'
        return false
    }

    if (form.permissions.length === 0) {
        errorMessage.value = 'Please select at least one permission'
        return false
    }

    return true
}

// Permission selection helpers
const selectAllPermissions = () => {
    form.permissions = [
        ...(dashboardPermissions.value?.map((p: any) => p.name) || []),
        ...(userPermissions.value?.map((p: any) => p.name) || []),
        ...(reportPermissions.value?.map((p: any) => p.name) || []),
        ...(otherPermissions.value?.map((p: any) => p.name) || [])
    ]
}

const clearAllPermissions = () => {
    form.permissions = []
}

const selectCommonPermissions = () => {
    const commonPerms = [
        'dashboard.view',
        'user_management.view',
        'user_management.search',
        'report.view'
    ].filter(perm =>
        (dashboardPermissions.value || []).some((p: any) => p.name === perm) ||
        (userPermissions.value || []).some((p: any) => p.name === perm) ||
        (reportPermissions.value || []).some((p: any) => p.name === perm) ||
        (otherPermissions.value || []).some((p: any) => p.name === perm)
    )
    form.permissions = commonPerms
}

// Handle form submission
const handleSubmit = async () => {
    if (!validateForm()) {
        return
    }

    isSubmitting.value = true
    errorMessage.value = ''

    try {
        // Prepare data for API
        const roleData = {
            display_name: form.display_name,
            description: form.description,
            permissions: form.permissions
        }

        // Update role via API
        const response = await put(`/api/v1/roles/${roleId.value}`, roleData)

        if (response.ok) {
            const data = await response.json()
            console.log('Role updated successfully:', data)

            // Success - redirect to roles list
            router.push({ name: 'role_management.index' })
        } else {
            // Handle API error
            const errorData = await response.json()
            errorMessage.value = errorData.message || 'Failed to update role. Please try again.'
            console.error('API Error:', errorData)
        }
    } catch (error) {
        console.error('Error updating role:', error)
        errorMessage.value = 'An unexpected error occurred. Please try again.'
    } finally {
        isSubmitting.value = false
    }
}

// Lifecycle hook
onMounted(async () => {
    await Promise.all([
        fetchPermissions(),
        fetchRole()
    ])
})

// Import components
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import Button from '../../../components/ui/Button.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
</script>
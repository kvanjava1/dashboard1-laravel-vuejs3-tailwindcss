<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle :title="`Edit User: ${userData?.name || 'Loading...'}`" />
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
        <LoadingState v-if="loading" message="Loading user data..." />

        <!-- Error State -->
        <ErrorState
            v-else-if="loadError"
            :message="loadError"
            @retry="fetchUser"
        />

        <!-- User Form -->
        <UserForm
            v-else
            :user="userData"
            :is-edit="true"
            @cancel="goBack"
            @success="handleSuccess"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserData } from '../../../composables/user/useUserData'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import ErrorState from '../../../components/ui/ErrorState.vue'
import UserForm from '../../../components/user/UserForm.vue'

const route = useRoute()
const router = useRouter()
const { fetchUser: fetchUserData, loading, error } = useUserData()

const userData = ref<any>(null)
const loadError = ref<string>('')

const fetchUser = async () => {
    try {
        loadError.value = ''
        const userId = route.params.id as string
        userData.value = await fetchUserData(parseInt(userId))

        // Check if user is super admin and redirect
        if (userData.value && (userData.value.role === 'super_admin' || userData.value.email === 'super@admin.com')) {
            router.push({ name: 'user_management.index' })
            return
        }
    } catch (err: any) {
        loadError.value = err.message || 'Failed to load user data'
        console.error('Error fetching user:', err)
    }
}

const goBack = () => {
    router.push({ name: 'user_management.index' })
}

const handleSuccess = (user: any) => {
    // Redirect to user list after successful update
    router.push({ name: 'user_management.index' })
}

onMounted(() => {
    fetchUser()
})
</script>
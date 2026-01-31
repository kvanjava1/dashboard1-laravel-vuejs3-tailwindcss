<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Add New User" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="arrow_back" @click="goBack">
                        Back
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- User Form -->
        <UserForm
            @cancel="goBack"
            @success="handleSuccess"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import UserForm from '../../../components/user/UserForm.vue'

const router = useRouter()

const goBack = () => {
    router.push({ name: 'user_management.index' })
}

const handleSuccess = (user: any) => {
    // Redirect to user list after successful creation
    router.push({ name: 'user_management.index' })
}
</script>
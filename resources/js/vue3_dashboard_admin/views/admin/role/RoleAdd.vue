<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Add New Role" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="arrow_back" @click="goBack">
                        Back
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Role Form -->
        <RoleForm
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
import RoleForm from '../../../components/role/RoleForm.vue'

const router = useRouter()

const goBack = () => {
    router.push({ name: 'role_management.index' })
}

const handleSuccess = (role: any) => {
    // Redirect to role list after successful creation
    router.push({ name: 'role_management.index' })
}
</script>
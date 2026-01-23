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

        <!-- Form Container -->
        <div class="space-y-6">
            <!-- Personal Information Card -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">person</span>
                            <ContentBoxTitle title="Personal Information" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.first_name"
                                type="text"
                                placeholder="John"
                                required
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Last Name <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.last_name"
                                type="text"
                                placeholder="Doe"
                                required
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            />
                        </div>
                    </div>
                </ContentBoxBody>
            </ContentBox>

            <!-- Contact Information Card -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">email</span>
                            <ContentBoxTitle title="Contact Information" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">mail</span>
                                </div>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="john.doe@example.com"
                                    required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Phone Number
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">phone</span>
                                </div>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="+1 (555) 123-4567"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                        </div>
                    </div>
                </ContentBoxBody>
            </ContentBox>

            <!-- Account Security Card -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">lock</span>
                            <ContentBoxTitle title="Account Security" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                </div>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="••••••••"
                                    required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Minimum 8 characters</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                </div>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="••••••••"
                                    required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                        </div>
                    </div>
                </ContentBoxBody>
            </ContentBox>

            <!-- Role & Permissions Card -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">manage_accounts</span>
                            <ContentBoxTitle title="Role & Permissions" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                User Role <span class="text-danger">*</span>
                            </label>
                            <select
                                v-model="form.role"
                                required
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                            >
                                <option value="">Select a role</option>
                                <option value="administrator">Administrator</option>
                                <option value="editor">Editor</option>
                                <option value="viewer">Viewer</option>
                                <option value="moderator">Moderator</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Account Status <span class="text-danger">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                required
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </ContentBoxBody>
            </ContentBox>

            <!-- Additional Information Card -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">info</span>
                            <ContentBoxTitle title="Additional Information" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Bio / Description
                        </label>
                        <textarea
                            v-model="form.bio"
                            rows="3"
                            placeholder="Brief description about the user..."
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
                        ></textarea>
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
                            {{ isSubmitting ? 'Creating...' : 'Create User' }}
                        </Button>
                    </div>
                </ContentBoxBody>
            </ContentBox>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../../../composables/useApi'
import { apiRoutes } from '../../../config/apiRoutes'
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

interface UserForm {
    first_name: string
    last_name: string
    email: string
    phone: string
    password: string
    password_confirmation: string
    role: string
    status: string
    bio: string
}

const router = useRouter()
const { post } = useApi()
const isSubmitting = ref(false)
const errorMessage = ref('')

const form = reactive<UserForm>({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    role: '',
    status: 'active',
    bio: ''
})

const goBack = () => {
    router.push({ name: 'user_management.index' })
}

const validateForm = (): boolean => {
    // Basic validation
    if (!form.first_name.trim()) {
        alert('First name is required')
        return false
    }

    if (!form.last_name.trim()) {
        alert('Last name is required')
        return false
    }

    if (!form.email.trim()) {
        alert('Email is required')
        return false
    }

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(form.email)) {
        alert('Please enter a valid email address')
        return false
    }

    if (!form.password) {
        alert('Password is required')
        return false
    }

    if (form.password.length < 8) {
        alert('Password must be at least 8 characters long')
        return false
    }

    if (form.password !== form.password_confirmation) {
        alert('Passwords do not match')
        return false
    }

    if (!form.role) {
        alert('Please select a user role')
        return false
    }

    if (!form.status) {
        alert('Please select account status')
        return false
    }

    return true
}

const handleSubmit = async () => {
    if (!validateForm()) {
        return
    }

    isSubmitting.value = true
    errorMessage.value = ''

    try {
        // Prepare data for API (exclude password_confirmation)
        const userData = {
            first_name: form.first_name,
            last_name: form.last_name,
            email: form.email,
            phone: form.phone,
            password: form.password,
            password_confirmation: form.password_confirmation,
            role: form.role,
            status: form.status,
            bio: form.bio
        }

        // Make API call to create user
        const response = await post(apiRoutes.users.store, userData)

        if (response.ok) {
            const data = await response.json()
            console.log('User created successfully:', data)

            // Success - redirect to users list
            router.push({ name: 'user_management.index' })
        } else {
            // Handle API error
            const errorData = await response.json()
            errorMessage.value = errorData.message || 'Failed to create user. Please try again.'
            console.error('API Error:', errorData)
        }
    } catch (error) {
        console.error('Error creating user:', error)
        errorMessage.value = 'An unexpected error occurred. Please try again.'
    } finally {
        isSubmitting.value = false
    }
}
</script>
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
        <div v-if="loading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2 text-sm text-slate-600">Loading user data...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="loadError" class="py-8 text-center">
            <span class="material-symbols-outlined text-danger text-4xl">error</span>
            <p class="mt-2 text-sm text-slate-700">{{ loadError }}</p>
            <button @click="fetchUser" class="mt-4 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                Try Again
            </button>
        </div>

        <!-- Form Container -->
        <div v-else class="space-y-6">
            <!-- Profile Picture Card -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">photo_camera</span>
                            <ContentBoxTitle title="Profile Picture" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <div class="flex flex-col items-center space-y-4">
                        <!-- Image Cropper/Preview -->
                        <div class="relative w-full max-w-md">
                            <!-- Cropper Modal -->
                            <div v-if="showCropper" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                                <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
                                    <h3 class="text-lg font-semibold mb-4 text-center">Crop Your Profile Picture</h3>

                                    <!-- Vue Advanced Cropper -->
                                    <div class="mb-4">
                                        <Cropper
                                            ref="cropperRef"
                                            :src="cropperImage"
                                            :stencil-props="{
                                                aspectRatio: 1
                                            }"
                                            :canvas="{
                                                height: 300,
                                                width: 300
                                            }"
                                        />
                                    </div>

                                    <!-- Crop Controls -->
                                    <div class="flex justify-center gap-3">
                                        <Button @click="cancelCrop" variant="outline">
                                            Cancel
                                        </Button>
                                        <Button @click="applyCrop" variant="primary">
                                            <span class="material-symbols-outlined mr-2">crop</span>
                                            Apply Crop
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Preview (1:1 aspect ratio, circular) -->
                            <div class="relative flex justify-center">
                                <div class="w-32 h-32 rounded-full border-4 border-dashed border-slate-300 flex items-center justify-center overflow-hidden bg-slate-50 transition-colors hover:border-primary/50">
                                    <img
                                        v-if="previewImage || userData?.profile_image_url"
                                        :src="previewImage || userData?.profile_image_url"
                                        class="w-full h-full object-cover"
                                        alt="Profile preview"
                                    />
                                    <span v-else class="material-symbols-outlined text-4xl text-slate-400">person</span>
                                </div>

                                <!-- Remove image button -->
                                <button
                                    v-if="previewImage || userData?.profile_image_url"
                                    @click="removeImage"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg"
                                    title="Remove image"
                                >
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>

                                <!-- Edit/Crop button -->
                                <button
                                    v-if="previewImage || userData?.profile_image_url"
                                    @click="editImage"
                                    class="absolute -bottom-2 -right-2 w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center hover:bg-primary-dark transition-colors shadow-lg"
                                    title="Crop image"
                                >
                                    <span class="material-symbols-outlined text-sm">crop</span>
                                </button>
                            </div>
                        </div>

                        <!-- Upload controls -->
                        <div class="flex flex-col items-center space-y-2">
                            <input
                                ref="fileInput"
                                type="file"
                                @change="handleFileSelect"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                            />

                            <Button
                                @click="fileInput?.click()"
                                variant="outline"
                                :disabled="isSubmitting"
                            >
                                <span class="material-symbols-outlined mr-2">upload</span>
                                {{ (previewImage || userData?.profile_image_url) ? 'Change Photo' : 'Upload Photo' }}
                            </Button>

                            <p class="text-xs text-slate-500 text-center">
                                JPG, PNG, WebP up to 2MB<br>
                                Will be cropped to square (1:1 ratio)
                            </p>
                        </div>
                    </div>
                </ContentBoxBody>
            </ContentBox>

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

            <!-- Account Security Card (Optional) -->
            <ContentBox>
                <ContentBoxHeader>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">lock</span>
                            <ContentBoxTitle title="Change Password (Optional)" />
                        </div>
                    </template>
                </ContentBoxHeader>
                <ContentBoxBody>
                    <p class="text-sm text-slate-600 mb-4">Leave password fields blank to keep current password</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- New Password -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                New Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                </div>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="••••••••"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Minimum 8 characters</p>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Confirm New Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                </div>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="••••••••"
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
                            <div v-if="isLoadingRoles" class="flex items-center gap-2 py-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
                                <span class="text-slate-600 text-sm">Loading roles...</span>
                            </div>
                            
                            <div v-else-if="roleError" class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <p class="text-red-600 text-sm">{{ roleError }}</p>
                                <Button variant="outline" size="sm" class="mt-2" @click="fetchRoles">
                                    Retry
                                </Button>
                            </div>
                            
                            <div v-else-if="roles.length === 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-yellow-600 text-lg">warning</span>
                                    <div>
                                        <p class="text-yellow-800 font-medium">No roles available</p>
                                        <p class="text-yellow-600 text-sm">Please create roles first before editing users.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <select
                                v-else
                                v-model="form.role"
                                required
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                            >
                                <option value="">Select a role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.display_name || role.name }}
                                </option>
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

            <!-- Error Messages -->
            <div v-if="errorMessages.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-red-500 text-lg">error</span>
                    <p class="text-red-700 text-sm font-medium">
                        {{ errorMessages.length === 1 ? 'Error' : 'Please fix the following errors' }}:
                    </p>
                </div>
                <div class="space-y-1">
                    <p v-for="error in errorMessages" :key="error"
                       class="text-red-600 text-sm ml-6">
                        • {{ error }}
                    </p>
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
                            {{ isSubmitting ? 'Updating...' : 'Update User' }}
                        </Button>
                    </div>
                </ContentBoxBody>
            </ContentBox>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'
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

// Image cropper
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

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
const route = useRoute()
const { get, patch } = useApi()
const isSubmitting = ref(false)
const errorMessages = ref<string[]>([])
const loading = ref(true)
const loadError = ref<string>('')

// Role fetching
const roles = ref<any[]>([])
const isLoadingRoles = ref(false)
const roleError = ref<string>('')

// Image handling
const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const previewImage = ref<string>('')

// Cropper state
const showCropper = ref(false)
const cropperImage = ref<string>('')
const cropperRef = ref<any>(null)

// User data
const userData = ref<any>(null)

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

// Fetch user data
const fetchUser = async () => {
    loading.value = true
    loadError.value = ''
    
    try {
        const userId = route.params.id as string
        const url = apiRoutes.users.show(userId)
        const response = await get(url)

        if (!response.ok) {
            throw new Error(`Failed to fetch user: ${response.status} ${response.statusText}`)
        }

        const data = await response.json()
        userData.value = data.user

        // Parse name into first_name and last_name
        const nameParts = userData.value.name.split(' ')
        const firstName = nameParts[0] || ''
        const lastName = nameParts.slice(1).join(' ') || ''

        // Populate form
        Object.assign(form, {
          first_name: firstName,
          last_name: lastName,
          email: userData.value.email,
          phone: userData.value.phone || '',
          password: '',
          password_confirmation: '',
          role: userData.value.role || '',
          status: userData.value.status || 'active',
          bio: userData.value.bio || ''
        })

    } catch (err: any) {
        loadError.value = err.message || 'Failed to load user data'
        console.error('Error fetching user:', err)
    } finally {
        loading.value = false
    }
}

// Fetch roles from API
const fetchRoles = async () => {
    try {
        isLoadingRoles.value = true
        roleError.value = ''
        
        const response = await get(apiRoutes.roles.options)

        if (response.ok) {
            const data = await response.json()
            roles.value = data.roles || []
        } else {
            const errorData = await response.json()
            roleError.value = errorData.message || 'Failed to load roles'
        }
    } catch (error) {
        console.error('Error fetching roles:', error)
        roleError.value = 'An unexpected error occurred'
    } finally {
        isLoadingRoles.value = false
    }
}

// Fetch user and roles when component mounts
onMounted(() => {
  fetchUser()
  fetchRoles()
})

const goBack = () => {
    router.push({ name: 'user_management.index' })
}

const validateForm = (): boolean => {
    errorMessages.value = []

    // Basic validation
    if (!form.first_name.trim()) {
        errorMessages.value.push('First name is required')
    }

    if (!form.last_name.trim()) {
        errorMessages.value.push('Last name is required')
    }

    if (!form.email.trim()) {
        errorMessages.value.push('Email is required')
    }

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (form.email.trim() && !emailRegex.test(form.email)) {
        errorMessages.value.push('Please enter a valid email address')
    }

    // Password validation (only if provided)
    if (form.password) {
        if (form.password.length < 8) {
            errorMessages.value.push('Password must be at least 8 characters long')
        }

        if (form.password !== form.password_confirmation) {
            errorMessages.value.push('Passwords do not match')
        }
    }

    if (!form.role) {
        errorMessages.value.push('Please select a user role')
    }

    if (!form.status) {
        errorMessages.value.push('Please select account status')
    }

    return errorMessages.value.length === 0
}

const handleSubmit = async () => {
    if (!validateForm()) {
        return
    }

    isSubmitting.value = true
    errorMessages.value = []

    try {
        const userId = route.params.id as string
        
        // Prepare FormData for file upload
        const formData = new FormData()
        formData.append('first_name', form.first_name)
        formData.append('last_name', form.last_name)
        formData.append('email', form.email)
        formData.append('phone', form.phone)
        formData.append('role', form.role)
        formData.append('status', form.status)
        formData.append('bio', form.bio)

        // Only append password if provided
        if (form.password) {
            formData.append('password', form.password)
            formData.append('password_confirmation', form.password_confirmation)
        }

        // Add profile image if selected
        if (selectedFile.value) {
            formData.append('profile_image', selectedFile.value)
        }

        // Make API call to update user
        const url = apiRoutes.users.update(userId)
        const response = await patch(url, formData)

        if (response.ok) {
          const data = await response.json()

          // Success - redirect to users list
          router.push({ name: 'user_management.index' })
        } else {
          // Handle API error
          const errorData = await response.json()

          // Handle validation errors (Laravel returns errors object)
          if (errorData.errors) {
            errorMessages.value = Object.values(errorData.errors).flat() as string[]
          } else {
            errorMessages.value = [errorData.message || 'Failed to update user. Please try again.']
          }
        }
    } catch (error) {
        console.error('Error updating user:', error)
        errorMessages.value = ['An unexpected error occurred. Please try again.']
    } finally {
        isSubmitting.value = false
    }
}

// Image handling methods
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp']
    if (!allowedTypes.includes(file.type)) {
        alert('Please select a valid image file (JPG, PNG, or WebP)')
        return
    }

    // Validate file size (2MB max)
    const maxSize = 2 * 1024 * 1024 // 2MB in bytes
    if (file.size > maxSize) {
        alert('Please select an image smaller than 2MB')
        return
    }

    selectedFile.value = file

    // Create image for cropper
    const reader = new FileReader()
    reader.onload = (e) => {
        cropperImage.value = e.target?.result as string
        showCropper.value = true
    }
    reader.readAsDataURL(file)
}

// Cropper methods
const applyCrop = async () => {
    if (!cropperRef.value) return

    const { canvas } = cropperRef.value.getResult()
    if (!canvas) return

    // Convert canvas to blob
    canvas.toBlob((blob: Blob) => {
        if (!blob) return

        // Create file from blob
        const croppedFile = new File([blob], 'cropped-image.jpg', {
            type: 'image/jpeg',
            lastModified: Date.now()
        })

        selectedFile.value = croppedFile
        previewImage.value = canvas.toDataURL('image/jpeg')

        showCropper.value = false
        cropperImage.value = ''
    }, 'image/jpeg', 0.9)
}

const cancelCrop = () => {
    showCropper.value = false
    cropperImage.value = ''
    selectedFile.value = null

    // Reset file input
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const editImage = () => {
    if (selectedFile.value && previewImage.value) {
        // Convert current preview back to cropper
        cropperImage.value = previewImage.value
        showCropper.value = true
    } else if (userData.value?.profile_image_url) {
        // Use existing profile image for cropping
        cropperImage.value = userData.value.profile_image_url
        showCropper.value = true
    }
}

const removeImage = () => {
    selectedFile.value = null
    previewImage.value = ''
    cropperImage.value = ''
    showCropper.value = false

    if (fileInput.value) {
        fileInput.value.value = ''
    }
}
</script>
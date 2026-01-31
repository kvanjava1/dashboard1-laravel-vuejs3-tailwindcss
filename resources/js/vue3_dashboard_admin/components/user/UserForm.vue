<template>
  <div class="space-y-6">
    <!-- Profile Image Section -->
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
        <UserProfileImageUpload
          v-model="profileImage"
          @image-selected="handleImageSelected"
          :disabled="loading"
        />
      </ContentBoxBody>
    </ContentBox>

    <!-- Form Sections -->
    <UserFormSections
      :form-data="formData"
      :is-edit="isEdit"
    />

    <!-- Error Messages -->
    <FormErrors :errors="errorMessages" />

    <!-- Form Actions -->
    <UserFormActions
      :loading="loading"
      :is-edit="isEdit"
      @cancel="$emit('cancel')"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'
import { showToast } from '@/composables/useSweetAlert'
import ContentBox from '../ui/ContentBox.vue'
import ContentBoxHeader from '../ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../ui/ContentBoxTitle.vue'
import ContentBoxBody from '../ui/ContentBoxBody.vue'
import UserProfileImageUpload from './UserProfileImageUpload.vue'
import UserFormSections from './UserFormSections.vue'
import FormErrors from './FormErrors.vue'
import UserFormActions from './UserFormActions.vue'

interface UserFormData {
  name: string
  email: string
  phone: string
  password: string
  password_confirmation: string
  role: string
  is_banned: boolean
  is_active: boolean
  bio: string
}

interface Props {
  user?: any // For edit mode
  isEdit?: boolean
}

interface Emits {
  (e: 'cancel'): void
  (e: 'success', user: any): void
}

const props = withDefaults(defineProps<Props>(), {
  user: null,
  isEdit: false
})

const emit = defineEmits<Emits>()

const router = useRouter()
const { post, put } = useApi()

// State
const loading = ref(false)
const errorMessages = ref<string[]>([])
const profileImage = ref<File | null>(null)

// Form data
const formData = reactive<UserFormData>({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  role: '',
  is_banned: false,
  is_active: true,
  bio: ''
})

// Watch for user prop changes (for edit mode)
watch(() => props.user, (newUser) => {
  if (newUser && props.isEdit) {
    populateFormData(newUser)
  }
}, { immediate: true })

// Populate form data for edit mode
const populateFormData = (user: any) => {
  formData.name = user.name || ''
  formData.email = user.email || ''
  formData.phone = user.phone || ''
  formData.role = user.role || ''
  formData.is_banned = user.is_banned || false
  formData.is_active = user.is_active || true
  formData.bio = user.bio || ''

  // Password fields are left empty for edit mode
  formData.password = ''
  formData.password_confirmation = ''
}

// Handle image selection
const handleImageSelected = (file: File) => {
  profileImage.value = file
}

// Validate form
const validateForm = (): boolean => {
  errorMessages.value = []

  // Basic validation
  if (!formData.name.trim()) {
    errorMessages.value.push('Full name is required')
    return false
  }

  if (!formData.email.trim()) {
    errorMessages.value.push('Email is required')
    return false
  }

  // Basic email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(formData.email)) {
    errorMessages.value.push('Please enter a valid email address')
    return false
  }

  if (!props.isEdit) {
    if (!formData.password) {
      errorMessages.value.push('Password is required')
      return false
    }

    if (formData.password.length < 8) {
      errorMessages.value.push('Password must be at least 8 characters long')
      return false
    }

    if (formData.password !== formData.password_confirmation) {
      errorMessages.value.push('Passwords do not match')
      return false
    }
  } else {
    // Edit mode: if password is provided, validate it
    if (formData.password && formData.password.length < 8) {
      errorMessages.value.push('Password must be at least 8 characters long')
      return false
    }

    if (formData.password !== formData.password_confirmation) {
      errorMessages.value.push('Passwords do not match')
      return false
    }
  }

  if (!formData.role) {
    errorMessages.value.push('Please select a user role')
    return false
  }

  return true
}

// Handle form submission
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  loading.value = true
  errorMessages.value = []

  try {
    // Prepare FormData for file upload
    const submitData = new FormData()

    // Add form fields
    submitData.append('name', formData.name)
    submitData.append('email', formData.email)
    submitData.append('phone', formData.phone || '')
    submitData.append('role', formData.role)
    submitData.append('is_banned', formData.is_banned ? '1' : '0')
    submitData.append('is_active', formData.is_active ? '1' : '0')
    submitData.append('bio', formData.bio || '')

    // Add password fields if provided
    if (formData.password) {
      submitData.append('password', formData.password)
      submitData.append('password_confirmation', formData.password_confirmation)
    }

    // Add profile image if selected
    if (profileImage.value) {
      submitData.append('profile_image', profileImage.value)
    }

    // Determine API endpoint and method
    const isUpdate = props.isEdit && props.user
    const url = isUpdate ? apiRoutes.users.update(props.user.id) : apiRoutes.users.store
    const apiMethod = isUpdate ? put : post

    // Make API call
    const response = await apiMethod(url, submitData)

    if (response.ok) {
      const data = await response.json()

      const successMessage = isUpdate ? 'User updated successfully' : 'User created successfully'
      await showToast({ icon: 'success', title: successMessage, timer: 3000 })

      emit('success', data.user)

      // Reset form for add mode
      if (!isUpdate) {
        resetForm()
      }
    } else {
      // Handle API error
      const errorData = await response.json()

      // Handle validation errors (Laravel returns errors object)
      if (errorData.errors) {
        errorMessages.value = Object.values(errorData.errors).flat() as string[]
      } else {
        errorMessages.value = [errorData.message || 'Failed to save user. Please try again.']
      }

      console.error('API Error:', errorData)
    }
  } catch (error) {
    console.error('Error saving user:', error)
    errorMessages.value = ['An unexpected error occurred. Please try again.']
  } finally {
    loading.value = false
  }
}

// Reset form (for add mode)
const resetForm = () => {
  formData.name = ''
  formData.email = ''
  formData.phone = ''
  formData.password = ''
  formData.password_confirmation = ''
  formData.role = ''
  formData.is_banned = false
  formData.is_active = true
  formData.bio = ''
  profileImage.value = null
  errorMessages.value = []
}

// Expose methods for parent components
defineExpose({
  resetForm,
  validateForm
})
</script>
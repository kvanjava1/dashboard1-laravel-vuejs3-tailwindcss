<template>
  <div class="space-y-6">
    <!-- Role Information Section -->
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
        <RoleFormSections
          :form-data="formData"
          :is-edit="isEdit"
        />
      </ContentBoxBody>
    </ContentBox>

    <!-- Permissions Section -->
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
        <RolePermissionsSection
          :form-data="formData"
          :permissions="permissions"
          :loading="permissionsLoading"
          :error="permissionsError"
          @fetch-permissions="fetchPermissions"
        />
      </ContentBoxBody>
    </ContentBox>

    <!-- Error Messages -->
    <FormErrors :errors="errorMessages" />

    <!-- Form Actions -->
    <RoleFormActions
      :loading="loading"
      :is-edit="isEdit"
      @cancel="$emit('cancel')"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onMounted } from 'vue'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'
import { showToast } from '@/composables/useSweetAlert'
import ContentBox from '../ui/ContentBox.vue'
import ContentBoxHeader from '../ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../ui/ContentBoxTitle.vue'
import ContentBoxBody from '../ui/ContentBoxBody.vue'
import FormErrors from '../user/FormErrors.vue'
import RoleFormSections from './RoleFormSections.vue'
import RolePermissionsSection from './RolePermissionsSection.vue'
import RoleFormActions from './RoleFormActions.vue'

// Props
interface Props {
  isEdit?: boolean
  initialData?: {
    id?: number
    name?: string
    display_name?: string
    description?: string
    permissions?: string[]
  }
}

const props = withDefaults(defineProps<Props>(), {
  isEdit: false,
  initialData: () => ({})
})

// Emits
const emit = defineEmits<{
  cancel: []
  success: [role: any]
}>()

// API
const { post, put, get } = useApi()

// State
const loading = ref(false)
const permissionsLoading = ref(true)
const permissionsError = ref('')
const errorMessages = ref<string[]>([])

// Form data
const formData = reactive({
  name: '',
  display_name: '',
  description: '',
  permissions: [] as string[]
})

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

// Initialize form data
watch(() => props.initialData, (newData) => {
  if (newData) {
    formData.name = newData.name || ''
    formData.display_name = newData.display_name || ''
    formData.description = newData.description || ''
    formData.permissions = newData.permissions || []
  }
}, { immediate: true })

// Fetch permissions
const fetchPermissions = async () => {
  try {
    permissionsLoading.value = true
    permissionsError.value = ''

    const response = await get(apiRoutes.permissions.grouped)

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
    permissionsLoading.value = false
  }
}

// Form validation
const validateForm = (): boolean => {
  errorMessages.value = []

  if (!formData.name.trim()) {
    errorMessages.value.push('Role name is required')
  }

  if (!formData.display_name.trim()) {
    errorMessages.value.push('Display name is required')
  }

  if (!/^[a-z_]+$/.test(formData.name)) {
    errorMessages.value.push('Role name can only contain lowercase letters and underscores')
  }

  if (formData.permissions.length === 0) {
    errorMessages.value.push('Please select at least one permission')
  }

  return errorMessages.value.length === 0
}

// Handle form submission
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  loading.value = true
  errorMessages.value = []

  try {
    const roleData = {
      name: formData.name,
      display_name: formData.display_name,
      description: formData.description,
      permissions: formData.permissions
    }

    // Ensure we have required data for edit mode
    if (props.isEdit && (!props.initialData || !props.initialData.id)) {
      errorMessages.value = ['Invalid role data for editing']
      return
    }

    const response = props.isEdit
      ? await put(apiRoutes.roles.update(props.initialData!.id!), roleData)
      : await post(apiRoutes.roles.store, roleData)

    if (response.ok) {
      const data = await response.json()
      const message = props.isEdit ? 'Role updated successfully' : 'Role created successfully'
      await showToast({ icon: 'success', title: message, timer: 2000 })
      emit('success', data.role)
    } else {
      const errorData = await response.json()
      if (errorData.errors) {
        errorMessages.value = Object.values(errorData.errors).flat() as string[]
      } else {
        errorMessages.value = [errorData.message || 'An error occurred']
      }
    }
  } catch (error) {
    console.error('Error submitting form:', error)
    errorMessages.value = ['An unexpected error occurred']
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchPermissions()
})
</script>
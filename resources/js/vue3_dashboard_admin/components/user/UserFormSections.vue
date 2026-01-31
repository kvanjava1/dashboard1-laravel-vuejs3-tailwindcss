<template>
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
        <div class="grid grid-cols-1 gap-4">
          <!-- Full Name -->
          <FormField
            v-model="formData.name"
            label="Full Name"
            type="text"
            placeholder="John Doe"
            required
          />
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
          <FormField
            v-model="formData.email"
            label="Email Address"
            type="email"
            placeholder="john.doe@example.com"
            leftIcon="mail"
            required
            :disabled="isEdit"
          />

          <!-- Phone -->
          <FormField
            v-model="formData.phone"
            label="Phone Number"
            type="tel"
            placeholder="+1 (555) 123-4567"
            leftIcon="phone"
          />
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
          <div v-if="!isEdit">
            <label class="block text-sm font-semibold text-slate-700 mb-2">
              Password <span class="text-danger">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
              </div>
              <input
                v-model="formData.password"
                type="password"
                placeholder="••••••••"
                required
                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              />
            </div>
            <p class="text-xs text-slate-500 mt-1">Minimum 8 characters</p>
          </div>

          <!-- Confirm Password -->
          <div v-if="!isEdit">
            <label class="block text-sm font-semibold text-slate-700 mb-2">
              Confirm Password <span class="text-danger">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
              </div>
              <input
                v-model="formData.password_confirmation"
                type="password"
                placeholder="••••••••"
                required
                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              />
            </div>
          </div>

          <!-- Change Password (Edit mode) -->
          <div v-else class="md:col-span-2">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-blue-600 text-lg">info</span>
                <p class="text-blue-800 font-medium">Password Management</p>
              </div>
              <p class="text-blue-600 text-sm mb-3">
                Leave password fields empty to keep the current password, or fill them to change it.
              </p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">
                    New Password
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                    </div>
                    <input
                      v-model="formData.password"
                      type="password"
                      placeholder="••••••••"
                      class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                    />
                  </div>
                  <p class="text-xs text-slate-500 mt-1">Minimum 8 characters (optional)</p>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Confirm New Password
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                    </div>
                    <input
                      v-model="formData.password_confirmation"
                      type="password"
                      placeholder="••••••••"
                      class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </ContentBoxBody>
    </ContentBox>

    <!-- Account Status Card -->
    <ContentBox>
      <ContentBoxHeader>
        <template #title>
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">admin_panel_settings</span>
            <ContentBoxTitle title="Account Status" />
          </div>
        </template>
      </ContentBoxHeader>
      <ContentBoxBody>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Is Banned -->
          <div>
            <label class="flex items-center gap-2">
              <input
                v-model="formData.is_banned"
                type="checkbox"
                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"
              />
              <span class="text-sm font-semibold text-slate-700">Banned</span>
            </label>
            <p class="text-xs text-slate-500 mt-1">Check to ban this user account</p>
          </div>

          <!-- Is Active -->
          <div>
            <label class="flex items-center gap-2">
              <input
                v-model="formData.is_active"
                type="checkbox"
                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"
              />
              <span class="text-sm font-semibold text-slate-700">Active</span>
            </label>
            <p class="text-xs text-slate-500 mt-1">Check to activate this user account</p>
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
            v-model="formData.bio"
            rows="3"
            placeholder="Brief description about the user..."
            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
          ></textarea>
        </div>
      </ContentBoxBody>
    </ContentBox>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'
import ContentBox from '../ui/ContentBox.vue'
import ContentBoxHeader from '../ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../ui/ContentBoxTitle.vue'
import ContentBoxBody from '../ui/ContentBoxBody.vue'
import FormField from '../ui/FormField.vue'
import Button from '../ui/Button.vue'

interface Props {
  formData: {
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
  isEdit?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isEdit: false
})

const { get } = useApi()

// Role fetching
const roles = ref<any[]>([])
const isLoadingRoles = ref(false)
const roleError = ref<string>('')

// Status fetching
const statuses = ref<any[]>([])
const isLoadingStatuses = ref(false)
const statusError = ref<string>('')

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

// Fetch statuses from API
const fetchStatuses = async () => {
  try {
    isLoadingStatuses.value = true
    statusError.value = ''

    // Since we don't have a dedicated status endpoint, we'll fetch from users endpoint
    // The users index returns status_options in filters
    const response = await get(apiRoutes.users.index({ page: 1, per_page: 1 }))

    if (response.ok) {
      const data = await response.json()
      statuses.value = data.filters?.status_options || []
    } else {
      const errorData = await response.json()
      statusError.value = errorData.message || 'Failed to load statuses'
    }
  } catch (error) {
    console.error('Error fetching statuses:', error)
    statusError.value = 'An unexpected error occurred'
  } finally {
    isLoadingStatuses.value = false
  }
}

// Initialize data fetching
onMounted(() => {
  fetchRoles()
  fetchStatuses()
})
</script>
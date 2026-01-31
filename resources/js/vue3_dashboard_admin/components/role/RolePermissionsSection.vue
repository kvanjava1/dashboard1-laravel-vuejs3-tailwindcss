<template>
  <!-- Loading State -->
  <LoadingState v-if="loading" message="Loading permissions..." />

  <!-- Error State -->
  <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
    <div class="flex items-center gap-3">
      <span class="material-symbols-outlined text-red-500 text-xl">error</span>
      <div>
        <p class="text-red-700 font-medium">Failed to load permissions</p>
        <p class="text-red-600 text-sm">{{ error }}</p>
        <Button
          variant="danger"
          size="sm"
          class="mt-2"
          @click="$emit('fetch-permissions')"
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
    </div>

    <!-- Dynamic Permission Groups -->
    <div class="space-y-6">
      <div v-for="(group, groupName) in permissions" :key="groupName">
        <h4 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
          <span class="material-symbols-outlined text-lg">{{ getGroupIcon(groupName) }}</span>
          {{ formatGroupName(groupName) }}
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <label
            v-for="permission in group || []"
            :key="permission.name"
            class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors"
          >
            <input
              v-model="formData.permissions"
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
      <div v-if="Object.values(permissions).every(g => (g || []).length === 0)" class="text-center py-8">
        <span class="material-symbols-outlined text-4xl text-slate-400 mb-2 block">security</span>
        <p class="text-slate-600">No permissions available</p>
      </div>
    </div>

    <!-- Selected Permissions Summary -->
    <div class="mt-6 p-4 bg-slate-50 rounded-lg">
      <h5 class="text-sm font-semibold text-slate-700 mb-2">
        Selected Permissions ({{ formData.permissions.length }})
      </h5>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="permission in formData.permissions"
          :key="permission"
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary"
        >
          {{ permission }}
        </span>
      </div>
      <p v-if="formData.permissions.length === 0" class="text-xs text-slate-500">
        No permissions selected
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
// Props
interface Props {
  formData: {
    name: string
    display_name: string
    description: string
    permissions: string[]
  }
  permissions: {
    dashboard: any[]
    user_management: any[]
    report: any[]
    other: any[]
  }
  loading?: boolean
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  error: ''
})

// Emits
const emit = defineEmits<{
  'fetch-permissions': []
}>()

// Helpers for dynamic group rendering
const groupIcons: Record<string, string> = {
  dashboard: 'dashboard',
  user_management: 'group',
  role_management: 'admin_panel_settings',
  report: 'analytics',
  other: 'settings',
}

function getGroupIcon(groupName: string) {
  return groupIcons[groupName] || 'settings'
}

function formatGroupName(groupName: string) {
  return groupName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Permission selection helpers
const selectAllPermissions = () => {
  props.formData.permissions = Object.values(props.permissions)
    .flat()
    .map((p: any) => p.name)
}

const clearAllPermissions = () => {
  props.formData.permissions = []
}

// Import components
import Button from '../ui/Button.vue'
import LoadingState from '../ui/LoadingState.vue'
</script>
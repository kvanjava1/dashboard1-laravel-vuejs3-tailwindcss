<template>
  <BaseModal v-model="isOpen" size="xl">
    <template #header>
      <span class="material-symbols-outlined text-2xl text-primary">person</span>
      <div>
        <h3 class="text-xl font-semibold text-slate-800">User Details</h3>
        <p class="text-sm text-slate-600" v-if="user">
          Viewing details for: <span class="font-medium">{{ user.name }}</span>
        </p>
      </div>
    </template>
    <template #body>
      <div v-if="user" class="p-0">
        <!-- Profile Header -->
        <div class="flex flex-col items-center gap-4 mb-8 p-6 bg-gradient-to-r from-primary/5 to-primary/10 rounded-xl border border-primary/20">
          <div class="w-24 h-24 border-4 border-primary bg-slate-100 flex items-center justify-center overflow-hidden relative"
               :style="{ borderRadius: 'var(--radius-avatar)' }">
            <img
              v-if="user.profile_image_url"
              :src="user.profile_image_url"
              alt="Avatar"
              class="w-full h-full object-cover"
              :style="{ borderRadius: 'var(--radius-avatar)' }"
            />
            <img
              v-else
              :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff&size=128`"
              alt="Avatar"
              class="w-full h-full object-cover"
              :style="{ borderRadius: 'var(--radius-avatar)' }"
            />
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-slate-800">{{ user.name }}</div>
            <div class="text-sm text-slate-500 mb-2">{{ user.email }}</div>
            <div class="flex items-center justify-center gap-2">
              <UserCellRole :role="user.role || ''" />
              <span class="text-slate-400">•</span>
              <UserCellStatus :status="user.status" />
            </div>
          </div>
        </div>

        <!-- User Information Sections -->
        <div class="space-y-6">
          <!-- Basic Information -->
          <div class="bg-slate-50 rounded-xl border border-slate-200 p-6">
            <h4 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-lg">info</span>
              Basic Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div v-if="user.username">
                <div class="text-xs font-medium text-slate-500 mb-1">Username</div>
                <div class="text-sm text-slate-800">{{ user.username }}</div>
              </div>
              <div v-if="user.phone">
                <div class="text-xs font-medium text-slate-500 mb-1">Phone</div>
                <div class="text-sm text-slate-800">{{ user.phone }}</div>
              </div>
              <div v-if="user.date_of_birth">
                <div class="text-xs font-medium text-slate-500 mb-1">Date of Birth</div>
                <div class="text-sm text-slate-800">{{ formatDate(user.date_of_birth) }}</div>
              </div>
              <div v-if="user.location">
                <div class="text-xs font-medium text-slate-500 mb-1">Location</div>
                <div class="text-sm text-slate-800">{{ user.location }}</div>
              </div>
              <div v-if="user.timezone">
                <div class="text-xs font-medium text-slate-500 mb-1">Timezone</div>
                <div class="text-sm text-slate-800">{{ user.timezone }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 mb-1">Joined Date</div>
                <div class="text-sm text-slate-800">{{ user.joined_date }}</div>
              </div>
            </div>
          </div>

          <!-- Bio Section -->
          <div v-if="user.bio" class="bg-slate-50 rounded-xl border border-slate-200 p-6">
            <h4 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-lg">description</span>
              Bio
            </h4>
            <div class="text-sm text-slate-700 leading-relaxed">{{ user.bio }}</div>
          </div>

          <!-- Account Status -->
          <div class="bg-slate-50 rounded-xl border border-slate-200 p-6">
            <h4 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-lg">account_circle</span>
              Account Status
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div class="text-xs font-medium text-slate-500 mb-1">Status</div>
                <UserCellStatus :status="user.status" />
              </div>
              <div v-if="user.last_activity_formatted">
                <div class="text-xs font-medium text-slate-500 mb-1">Last Activity</div>
                <div class="text-sm text-slate-800">{{ user.last_activity_formatted }}</div>
              </div>
              <div v-if="user.is_banned" class="md:col-span-2">
                <div class="text-xs font-medium text-slate-500 mb-2">Ban Information</div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                  <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-500 mt-0.5">block</span>
                    <div class="flex-1">
                      <div class="text-sm font-medium text-red-800 mb-1">Account Banned</div>
                      <div class="text-sm text-red-700">This account has been permanently banned. Check ban history for details.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- System Information -->
          <div class="bg-slate-50 rounded-xl border border-slate-200 p-6">
            <h4 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-lg">settings</span>
              System Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div class="text-xs font-medium text-slate-500 mb-1">User ID</div>
                <div class="text-sm text-slate-800 font-mono">{{ user.id }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 mb-1">Role</div>
                <div class="text-sm text-slate-800">{{ user.role_display_name || user.role }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 mb-1">Created At</div>
                <div class="text-sm text-slate-800">{{ formatDateTime(user.created_at) }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 mb-1">Last Updated</div>
                <div class="text-sm text-slate-800">{{ formatDateTime(user.updated_at) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
    <template #footer>
      <Button type="button" variant="outline" @click="closeModal">
        Close
      </Button>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed, onMounted, watch, ref, onUnmounted } from 'vue'
import BaseModal from '../ui/BaseModal.vue'
import UserCellUser from '../ui/UserCellUser.vue'
import UserCellRole from '../ui/UserCellRole.vue'
import UserCellStatus from '../ui/UserCellStatus.vue'
import Button from '../ui/Button.vue'

interface User {
  id: number
  name: string
  email: string
  username?: string
  phone?: string
  status: string
  bio?: string
  date_of_birth?: string
  location?: string
  timezone?: string
  last_activity?: string
  last_activity_formatted?: string
  is_banned?: boolean
  ban_reason?: string | undefined
  banned_until?: string | undefined
  banned_until_formatted?: string
  profile_image?: string
  profile_image_url?: string
  role?: string
  role_display_name?: string
  created_at: string
  updated_at: string
  joined_date: string
}

const props = defineProps<{
  modelValue: boolean
  user?: User | null
}>()
const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)

// Watch for modal value changes
watch(() => props.modelValue, (newValue) => {
  isOpen.value = newValue
})

watch(isOpen, (newValue) => {
  emit('update:modelValue', newValue)
})

const closeModal = () => {
  isOpen.value = false
}

function formatDate(dateString: string): string {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function formatDateTime(dateString: string): string {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

watch(() => props.user, (val) => {
  console.log('UserDetailModal user changed:', val)
}, { immediate: true })

// Handle escape key
const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeModal()
  }
}

document.addEventListener('keydown', handleKeydown)

// Cleanup
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

</script>

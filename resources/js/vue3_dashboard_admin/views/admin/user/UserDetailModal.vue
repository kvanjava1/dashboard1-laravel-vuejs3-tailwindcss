<template>
  <BaseModal v-model="isOpen" size="md">
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
        <div class="flex flex-col items-center gap-2 mb-6">
          <div class="w-20 h-20 rounded-full border-2 border-primary bg-slate-100 flex items-center justify-center overflow-hidden mb-2 relative">
            <img
              v-if="user.avatar"
              :src="user.avatar"
              alt="Avatar"
              class="w-full h-full object-cover rounded-full"
            />
            <img
              v-else
              :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff&size=128`"
              alt="Avatar"
              class="w-full h-full object-cover rounded-full"
            />
          </div>
          <div class="text-lg font-bold text-slate-800">{{ user.name }}</div>
          <div class="text-sm text-slate-500">{{ user.email }}</div>
        </div>
        <div class="bg-slate-50 rounded-xl border border-slate-200 p-6 mb-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <div class="text-xs font-medium text-slate-500 mb-1">Role</div>
              <UserCellRole :role="user.role" />
            </div>
            <div>
              <div class="text-xs font-medium text-slate-500 mb-1">Status</div>
              <UserCellStatus :status="user.status" />
            </div>
            <div>
              <div class="text-xs font-medium text-slate-500 mb-1">Joined Date</div>
              <div class="text-sm text-slate-800">{{ user.joinedDate }}</div>
            </div>
          </div>
        </div>
      </div>
    </template>
    <template #footer>
      <button @click="closeModal" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
        Close
      </button>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import BaseModal from '../../../components/ui/BaseModal.vue'
import UserCellUser from '../../../components/ui/UserCellUser.vue'
import UserCellRole from '../../../components/ui/UserCellRole.vue'
import UserCellStatus from '../../../components/ui/UserCellStatus.vue'

interface User {
  id: number
  name: string
  email: string
  avatar: string
  role: string
  status: string
  joinedDate: string
}

const props = defineProps<{
  modelValue: boolean
  user?: User | null
}>()
const emit = defineEmits(['update:modelValue'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v)
})

function closeModal() {
  isOpen.value = false
}

watch(() => props.user, (val) => {
  console.log('UserDetailModal user changed:', val)
}, { immediate: true })
</script>

<template>
  <router-view />
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

onMounted(async () => {
  // Restore user session from localStorage on app startup
  try {
    await authStore.restoreSession()
  } catch (error) {
    console.error('Failed to restore session:', error)
    // Session restoration failed, user will need to login again
  }
})
</script>
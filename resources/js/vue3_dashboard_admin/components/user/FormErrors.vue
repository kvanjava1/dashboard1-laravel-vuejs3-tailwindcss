<template>
  <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
    <div class="flex items-center gap-2 mb-3">
      <span class="material-symbols-outlined text-red-500 text-lg">error</span>
      <p class="text-red-700 text-sm font-medium">
        {{ errors.length === 1 ? 'Error' : 'Please fix the following errors' }}:
      </p>
    </div>
    <div class="space-y-1">
      <p v-for="error in errors" :key="error"
         class="text-red-600 text-sm ml-6">
        • {{ error }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  errors: string[]
}

defineProps<Props>()
</script>
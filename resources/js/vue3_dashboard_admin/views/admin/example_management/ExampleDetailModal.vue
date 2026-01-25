<template>
  <BaseModal v-model="isOpen" size="md">
    <template #header>
      <h3 class="text-xl font-semibold text-slate-800">Example Details</h3>
    </template>
    <template #body>
      <div v-if="example">
        <div class="mb-2"><b>Name:</b> {{ example.name }}</div>
        <div class="mb-2"><b>Created At:</b> {{ example.created_at }}</div>
        <div class="mb-2"><b>Updated At:</b> {{ example.updated_at }}</div>
      </div>
    </template>
    <template #footer>
      <button @click="closeModal" class="btn btn-ghost">Close</button>
    </template>
  </BaseModal>
</template>
<script setup lang="ts">
import { computed } from 'vue'
import BaseModal from '../../../components/ui/BaseModal.vue'
const props = defineProps<{ modelValue: boolean, example?: any }>()
const emit = defineEmits(['update:modelValue'])
const isOpen = computed({ get: () => props.modelValue, set: v => emit('update:modelValue', v) })
function closeModal() { isOpen.value = false }
</script>
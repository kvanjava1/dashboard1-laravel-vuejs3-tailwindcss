<template>
  <BaseModal v-model="isOpen" size="md">
    <template #header>
      <h3 class="text-xl font-semibold text-slate-800">Edit Example</h3>
    </template>
    <template #body>
      <form @submit.prevent="editExample">
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Name</label>
          <input v-model="form.name" type="text" class="input input-bordered w-full" required maxlength="255" />
        </div>
      </form>
    </template>
    <template #footer>
      <button @click="closeModal" class="btn btn-ghost">Cancel</button>
      <button @click="editExample" class="btn btn-primary">Save</button>
    </template>
  </BaseModal>
</template>
<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import BaseModal from '../../../components/ui/BaseModal.vue'
const props = defineProps<{ modelValue: boolean, example?: any }>()
const emit = defineEmits(['update:modelValue', 'updated'])
const isOpen = computed({ get: () => props.modelValue, set: v => emit('update:modelValue', v) })
const form = ref({ name: '' })
watch(() => props.example, (val) => { if (val) form.value.name = val.name })
function closeModal() { isOpen.value = false }
function editExample() {
  // TODO: API call to backend to update example
  emit('updated')
  closeModal()
}
</script>
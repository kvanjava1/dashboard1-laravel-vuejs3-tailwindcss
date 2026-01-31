<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Role Name -->
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2">
        Role Name <span class="text-danger">*</span>
      </label>
      <input
        v-model="formData.name"
        type="text"
        placeholder="e.g., editor, moderator"
        required
        :disabled="isEdit"
        class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all disabled:bg-slate-100 disabled:cursor-not-allowed"
      />
      <p class="text-xs text-slate-500 mt-1">
        Use lowercase with underscores (e.g., content_editor)
        <span v-if="isEdit" class="text-amber-600">• Cannot be changed after creation</span>
      </p>
    </div>

    <!-- Display Name -->
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2">
        Display Name <span class="text-danger">*</span>
      </label>
      <input
        v-model="formData.display_name"
        type="text"
        placeholder="e.g., Content Editor"
        required
        class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
      />
      <p class="text-xs text-slate-500 mt-1">Human-readable name for the role</p>
    </div>
  </div>

  <!-- Description -->
  <div class="mt-4">
    <label class="block text-sm font-semibold text-slate-700 mb-2">
      Description
    </label>
    <textarea
      v-model="formData.description"
      rows="3"
      placeholder="Describe what this role can do..."
      class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
    ></textarea>
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
  isEdit?: boolean
}

defineProps<Props>()
</script>
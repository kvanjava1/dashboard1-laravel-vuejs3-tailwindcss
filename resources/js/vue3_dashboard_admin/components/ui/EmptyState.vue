<template>
    <div class="py-12 text-center">
        <span class="material-symbols-outlined text-slate-400 text-4xl">{{ icon }}</span>
        <p class="mt-2 text-sm text-slate-700">{{ message }}</p>
        <p v-if="subtitle" class="text-xs text-slate-500 mt-1">{{ subtitle }}</p>
    </div>
</template>

<script setup lang="ts">
interface Props {
    icon?: string
    message: string
    subtitle?: string
}

withDefaults(defineProps<Props>(), {
    icon: 'group',
    subtitle: ''
})
</script>
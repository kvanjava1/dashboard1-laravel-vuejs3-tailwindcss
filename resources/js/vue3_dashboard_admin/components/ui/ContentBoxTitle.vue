<template>
    <div>
        <h3 class="text-lg font-bold text-slate-800">{{ title }}</h3>
        <p v-if="subtitle" class="text-slate-500 text-sm mt-1">{{ subtitle }}</p>
    </div>
</template>

<script setup lang="ts" define-props>
interface Props {
    title: string
    subtitle?: string
}

defineProps<Props>()
</script>

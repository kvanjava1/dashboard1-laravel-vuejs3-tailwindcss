<template>
    <div class="text-sm text-slate-500">
        Showing <span class="font-semibold text-slate-700">{{ currentStart }}-{{ currentEnd }}</span> of
        <span class="font-semibold text-slate-700">{{ total }}</span> users
    </div>
</template>

<script setup lang="ts" define-props>
interface Props {
    currentStart: number
    currentEnd: number
    total: number
}

defineProps<Props>()
</script>

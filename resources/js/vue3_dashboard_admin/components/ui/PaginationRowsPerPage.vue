<template>
    <div class="text-sm text-slate-500">
        <span class="font-medium text-slate-700">{{ rowsPerPage }}</span> rows per page
    </div>
</template>

<script setup lang="ts" define-props>
interface Props {
    rowsPerPage: number
}

defineProps<Props>()
</script>

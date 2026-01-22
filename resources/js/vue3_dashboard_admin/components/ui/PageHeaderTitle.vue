<template>
    <div>
        <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 tracking-tight">{{ title }}</h2>
    </div>
</template>

<script setup lang="ts" define-props>
interface Props {
    title: string
}

defineProps<Props>()
</script>

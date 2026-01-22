<template>
    <th :style="{ width }" class="text-left py-3 px-4 text-sm font-semibold text-slate-700">
        <slot />
    </th>
</template>

<script setup lang="ts" define-props>
interface Props {
    width?: string
}

defineProps<Props>()
</script>

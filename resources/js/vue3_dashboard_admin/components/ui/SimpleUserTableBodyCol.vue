<template>
    <td :style="{ width }" class="py-4 px-4">
        <slot />
    </td>
</template>

<script setup lang="ts" define-props>
interface Props {
    width?: string
}

defineProps<Props>()
</script>

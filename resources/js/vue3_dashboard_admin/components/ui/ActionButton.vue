<template>
    <Button
        :variant="variant === 'primary' ? 'primary' : 'outline'"
        :size="size"
        :left-icon="icon"
        :full-width="fullWidth"
        v-bind="$attrs"
        @click="(e) => emit('click', e)"
    >
        <slot />
    </Button>
</template>

<script setup lang="ts">
import Button from './Button.vue'

interface Props {
    variant?: 'primary' | 'secondary'
    icon?: string
    size?: 'xs' | 'sm' | 'md' | 'lg'
    fullWidth?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'sm',
    fullWidth: false
})

const emit = defineEmits<{
    click: [event: Event]
}>()
</script>

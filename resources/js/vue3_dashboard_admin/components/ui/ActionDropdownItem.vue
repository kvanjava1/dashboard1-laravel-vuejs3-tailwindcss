<template>
    <Button
        type="button"
        variant="outline"
        size="sm"
        full-width
        class="!justify-start !shadow-none"
        @click="handleClick"
    >
        <span v-if="icon" class="material-symbols-outlined text-slate-600 text-sm mr-2">{{ icon }}</span>
        <span class="text-sm text-slate-700">
            <slot />
        </span>
    </Button>
</template>

<script setup lang="ts">
import Button from './Button.vue'

interface Props {
    icon?: string
}

interface Emits {
    (e: 'click'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const handleClick = () => {
    emit('click')
}
</script>

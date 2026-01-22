<template>
    <button
        class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors flex items-center gap-3"
        @click="handleClick">
        <span v-if="icon" class="material-symbols-outlined text-slate-600 text-sm">{{ icon }}</span>
        <span class="text-sm text-slate-700">
            <slot />
        </span>
    </button>
</template>

<script setup lang="ts">
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

<template>
    <button type="button" @click="handleClick"
        class="w-full flex items-center gap-3 px-4 py-2.5 text-left transition-all duration-150 rounded-xl group hover:bg-gradient-to-r hover:from-primary/5 hover:to-primary/10 active:scale-[0.98]">
        <!-- Icon -->
        <span v-if="icon"
            class="material-symbols-outlined text-gray-600 text-xl group-hover:text-primary group-hover:scale-110 transition-all duration-200">
            {{ icon }}
        </span>

        <!-- Text -->
        <span class="flex-1 text-sm font-medium text-gray-700 group-hover:text-gray-900">
            <slot />
        </span>

        <!-- Optional Badge/Indicator -->
        <span v-if="badge" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-primary/10 text-primary">
            {{ badge }}
        </span>
    </button>
</template>

<script setup lang="ts">
interface Props {
    icon?: string
    badge?: string | number
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

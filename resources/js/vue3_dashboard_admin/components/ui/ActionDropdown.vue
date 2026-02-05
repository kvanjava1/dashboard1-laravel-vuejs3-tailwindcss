<template>
    <div class="relative">
        <Button
            type="button"
            :variant="variant === 'primary' ? 'primary' : 'outline'"
            :size="size"
            @click="toggleDropdown"
            :class="[
                showLabel ? '' : '!p-2',
                isOpen ? 'bg-slate-100' : ''
            ]"
            :title="label"
            :aria-label="label"
        >
            <span class="material-symbols-outlined text-[18px]">{{ icon }}</span>
            <span v-if="showLabel" class="hidden sm:inline">{{ label }}</span>
        </Button>
        <div
            :class="[
                `absolute top-full ${align === 'right' ? 'right-0' : 'left-0'} mt-1 bg-white rounded-lg border border-border-light shadow-hard transition-all duration-200 z-10`,
                'min-w-56 max-w-[calc(100vw-2rem)] max-h-[70vh] overflow-auto',
                // Desktop hover behavior
                'opacity-0 invisible group-hover:opacity-100 group-hover:visible',
                // Mobile click behavior
                isOpen ? 'opacity-100 visible' : 'opacity-0 invisible'
            ]">
            <div class="p-1" @click="closeDropdown">
                <slot />
            </div>
        </div>

        <!-- Mobile overlay to close dropdown when clicking outside -->
        <div
            v-if="isOpen"
            class="fixed inset-0 z-5"
            @click="closeDropdown"
        ></div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import Button from './Button.vue'

withDefaults(defineProps<{
    label?: string
    icon?: string
    variant?: 'primary' | 'secondary'
    size?: 'xs' | 'sm' | 'md' | 'lg'
    showLabel?: boolean
    align?: 'left' | 'right'
}>(), {
    label: 'More',
    icon: 'more_horiz',
    variant: 'secondary',
    size: 'sm',
    showLabel: false,
    align: 'right'
})

const isOpen = ref(false)

const toggleDropdown = () => {
    isOpen.value = !isOpen.value
}

const closeDropdown = () => {
    isOpen.value = false
}

// Close dropdown on escape key
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && isOpen.value) {
        closeDropdown()
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
    <div class="relative group">
        <!-- Trigger Button -->
        <button type="button" @click="toggleDropdown" :class="[
            'flex items-center justify-center gap-2 px-3 py-2 rounded-lg font-medium transition-all duration-200',
            'border border-gray-300 bg-white text-gray-700',
            'hover:bg-gray-50 hover:border-gray-400 hover:shadow-sm',
            'active:scale-95',
            isOpen ? 'bg-gray-100 border-gray-400 shadow-sm' : '',
            showLabel ? 'px-4' : 'w-10 h-10'
        ]" :title="label" :aria-label="label" :aria-expanded="isOpen">
            <span class="material-symbols-outlined text-xl">{{ icon }}</span>
            <span v-if="showLabel" class="hidden sm:inline text-sm">{{ label }}</span>
        </button>

        <!-- Dropdown Menu -->
        <Transition enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95 -translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-2">
            <div v-if="isOpen" :class="[
                'absolute top-full mt-2 bg-white rounded-2xl border border-gray-200 shadow-xl z-50',
                'min-w-[200px] overflow-hidden',
                align === 'right' ? 'right-0' : 'left-0'
            ]">
                <!-- Dropdown Content -->
                <div class="py-1.5 px-1" @click="closeDropdown">
                    <slot />
                </div>
            </div>
        </Transition>

        <!-- Backdrop (Mobile) -->
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isOpen" class="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm sm:hidden"
                @click="closeDropdown" />
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

withDefaults(defineProps<{
    label?: string
    icon?: string
    variant?: 'primary' | 'secondary'
    size?: 'xs' | 'sm' | 'md' | 'lg'
    showLabel?: boolean
    align?: 'left' | 'right'
}>(), {
    label: 'More',
    icon: 'more_vert',
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

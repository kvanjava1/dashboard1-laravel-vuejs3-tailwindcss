<template>
    <div class="relative">
        <ActionButton
            variant="secondary"
            icon="more_horiz"
            @click="toggleDropdown"
            :class="{ 'bg-slate-100': isOpen }"
        >
            More Actions
        </ActionButton>
        <div
            :class="[
                'absolute top-full left-0 mt-1 bg-white rounded-xl border border-border-light shadow-hard transition-all duration-200 z-10',
                // Desktop hover behavior
                'opacity-0 invisible group-hover:opacity-100 group-hover:visible',
                // Mobile click behavior
                isOpen ? 'opacity-100 visible' : 'opacity-0 invisible'
            ]">
            <div @click="closeDropdown">
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
import ActionButton from './ActionButton.vue'

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

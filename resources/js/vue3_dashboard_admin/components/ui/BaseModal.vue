<template>
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @click="closeModal"
        >
            <!-- Modal Content -->
            <div
                :class="[
                    'bg-white rounded-lg shadow-xl overflow-hidden',
                    sizeClasses[size]
                ]"
                @click.stop
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-slate-200">
                    <div class="flex items-center gap-3">
                        <slot name="header">
                            <!-- Default header content -->
                            <span class="material-symbols-outlined text-primary text-2xl">info</span>
                            <div>
                                <h3 class="text-xl font-semibold text-slate-800">{{ title }}</h3>
                                <p v-if="subtitle" class="text-sm text-slate-600">{{ subtitle }}</p>
                            </div>
                        </slot>
                    </div>
                    <button
                        @click="closeModal"
                        class="p-2 hover:bg-slate-100 rounded-full transition-colors"
                    >
                        <span class="material-symbols-outlined text-slate-400">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div
                    class="overflow-y-auto"
                    :class="[bodyPadding ? 'p-6' : '', bodyClasses[size]]"
                >
                    <slot name="body">
                        <!-- Default body content -->
                        <p class="text-slate-600">Modal content goes here</p>
                    </slot>
                </div>

                <!-- Modal Footer -->
                <div
                    v-if="$slots.footer"
                    class="flex flex-col sm:flex-row items-center justify-end gap-3 p-6 border-t border-slate-200 bg-slate-50/50"
                >
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    modelValue: boolean
    title?: string
    subtitle?: string
    size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl'
    bodyPadding?: boolean
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    title: 'Modal Title',
    size: 'md',
    bodyPadding: true
})

const emit = defineEmits<Emits>()

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const sizeClasses = {
    sm: 'w-full max-w-md',
    md: 'w-full max-w-2xl',
    lg: 'w-full max-w-4xl',
    xl: 'w-full max-w-6xl',
    '2xl': 'w-full max-w-7xl'
}

const bodyClasses = {
    sm: 'max-h-[70vh]',
    md: 'max-h-[80vh]',
    lg: 'max-h-[85vh]',
    xl: 'max-h-[90vh]',
    '2xl': 'max-h-[90vh]'
}

const closeModal = () => {
    isOpen.value = false
}

// Handle escape key
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && isOpen.value) {
        closeModal()
    }
}

document.addEventListener('keydown', handleKeydown)

// Cleanup
import { onUnmounted } from 'vue'
onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})
</script>
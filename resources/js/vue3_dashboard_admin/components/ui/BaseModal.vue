<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300"
      leave-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click="handleBackdropClick"
        @keydown.escape="closeModal"
      >
        <Transition
          enter-active-class="transition-all duration-300"
          leave-active-class="transition-all duration-200"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div
            v-if="isOpen"
            :class="[
              'bg-white rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col',
              sizeClasses[size]
            ]"
            @click.stop
            role="dialog"
            aria-modal="true"
            :aria-labelledby="title ? 'modal-title' : undefined"
          >
            <!-- Header -->
            <div
              v-if="$slots.header || title"
              class="flex items-center justify-between p-6 border-b border-slate-200 bg-slate-50/50"
            >
              <div class="flex items-center gap-3">
                <slot name="header">
                  <div>
                    <h2
                      v-if="title"
                      id="modal-title"
                      class="text-xl font-semibold text-slate-800"
                    >
                      {{ title }}
                    </h2>
                    <p
                      v-if="subtitle"
                      class="text-sm text-slate-600 mt-1"
                    >
                      {{ subtitle }}
                    </p>
                  </div>
                </slot>
              </div>
              <Button
                @click="closeModal"
                type="button"
                variant="ghost"
                size="xs"
                class="!p-2 rounded-full"
                aria-label="Close modal"
              >
                <span class="material-symbols-outlined text-slate-400 text-xl">close</span>
              </Button>
            </div>

            <!-- Body -->
            <div
              class="flex-1 overflow-y-auto"
              :class="bodyPadding ? 'p-6' : ''"
            >
              <slot name="body">
                <div class="text-center py-8">
                  <span class="material-symbols-outlined text-4xl text-slate-300 mb-4">info</span>
                  <p class="text-slate-600">Modal content goes here</p>
                </div>
              </slot>
            </div>

            <!-- Footer -->
            <div
              v-if="$slots.footer"
              class="flex flex-col sm:flex-row items-center justify-end gap-3 p-6 border-t border-slate-200 bg-slate-50/50"
            >
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue'
import Button from './Button.vue'

interface Props {
  modelValue: boolean
  title?: string
  subtitle?: string
  size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl'
  bodyPadding?: boolean
  closeOnBackdrop?: boolean
  closeOnEscape?: boolean
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'open'): void
  (e: 'close'): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  title: '',
  subtitle: '',
  size: 'md',
  bodyPadding: true,
  closeOnBackdrop: true,
  closeOnEscape: true
})

const emit = defineEmits<Emits>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => {
    emit('update:modelValue', value)
    if (value) {
      emit('open')
    } else {
      emit('close')
    }
  }
})

const sizeClasses = {
  sm: 'w-full max-w-md',
  md: 'w-full max-w-2xl',
  lg: 'w-full max-w-4xl',
  xl: 'w-full max-w-6xl',
  '2xl': 'w-full max-w-7xl'
}

const closeModal = () => {
  isOpen.value = false
}

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    closeModal()
  }
}

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && props.closeOnEscape && isOpen.value) {
    closeModal()
  }
}

// Prevent body scroll when modal is open
const updateBodyScroll = () => {
  if (isOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

// Watch for modal state changes
watch(isOpen, updateBodyScroll, { immediate: true })

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  updateBodyScroll()
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

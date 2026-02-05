<template>
    <div class="py-8 text-center">
        <span class="material-symbols-outlined text-danger text-4xl">error</span>
        <p class="mt-2 text-sm text-slate-700">{{ message }}</p>
        <Button
            v-if="showRetry"
            type="button"
            variant="primary"
            size="sm"
            @click="$emit('retry')"
            class="mt-4"
        >
            Try Again
        </Button>
    </div>
</template>

<script setup lang="ts">
import Button from './Button.vue'

interface Props {
    message?: string
    showRetry?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    message: 'An error occurred',
    showRetry: true
})

defineEmits<{
    retry: []
}>()
</script>

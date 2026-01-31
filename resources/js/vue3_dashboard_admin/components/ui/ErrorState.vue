<template>
    <div class="py-8 text-center">
        <span class="material-symbols-outlined text-danger text-4xl">error</span>
        <p class="mt-2 text-sm text-slate-700">{{ message }}</p>
        <button
            v-if="showRetry"
            @click="$emit('retry')"
            class="mt-4 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors"
        >
            Try Again
        </button>
    </div>
</template>

<script setup lang="ts">
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
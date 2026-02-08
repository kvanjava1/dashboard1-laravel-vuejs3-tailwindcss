<template>
    <div class="py-12 text-center">
        <LoadingSpinner size="lg" color="primary" class="mx-auto" />
        <p class="mt-2 text-sm text-slate-600">{{ message }}</p>
    </div>
</template>

<script setup lang="ts">
import LoadingSpinner from './LoadingSpinner.vue'
interface Props {
    message?: string
}

withDefaults(defineProps<Props>(), {
    message: 'Loading...'
})
</script>
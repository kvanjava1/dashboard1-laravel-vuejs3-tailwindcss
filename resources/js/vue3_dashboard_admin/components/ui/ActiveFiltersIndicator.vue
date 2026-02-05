<template>
    <div v-if="hasActiveFilters" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600 text-lg">filter_list</span>
                <span class="text-blue-800 font-medium">Filters Active</span>
                <span class="text-blue-600 text-sm">Showing filtered results</span>
            </div>
            <Button type="button" variant="outline" size="sm" @click="$emit('reset')">
                Clear Filters
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import Button from './Button.vue'

interface Props {
    hasActiveFilters: boolean
}

defineProps<Props>()

defineEmits<{
    reset: []
}>()
</script>

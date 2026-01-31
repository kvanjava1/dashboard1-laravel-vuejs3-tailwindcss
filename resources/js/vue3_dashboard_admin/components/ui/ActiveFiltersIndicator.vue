<template>
    <div v-if="hasActiveFilters" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600 text-lg">filter_list</span>
                <span class="text-blue-800 font-medium">Filters Active</span>
                <span class="text-blue-600 text-sm">Showing filtered results</span>
            </div>
            <button
                @click="$emit('reset')"
                class="px-3 py-1.5 text-sm font-medium text-blue-700 bg-white border border-blue-300 rounded-md hover:bg-blue-50 transition-colors"
            >
                Clear Filters
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    hasActiveFilters: boolean
}

defineProps<Props>()

defineEmits<{
    reset: []
}>()
</script>
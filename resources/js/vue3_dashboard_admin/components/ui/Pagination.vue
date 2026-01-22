<template>
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-border-light">
        <PaginationInfo :current-start="currentStart" :current-end="currentEnd" :total="total" />
        <PaginationControls :current-page="currentPage" :total-pages="totalPages" @prev="$emit('prev')"
            @next="$emit('next')" @goto="(page) => $emit('goto', page)" />
        <PaginationRowsPerPage :rows-per-page="rowsPerPage" />
    </div>
</template>

<script setup lang="ts" define-props>
interface Props {
    currentStart: number
    currentEnd: number
    total: number
    currentPage: number
    totalPages: number
    rowsPerPage: number
}

defineProps<Props>()
defineEmits<{
    prev: []
    next: []
    goto: [page: number]
}>()

import PaginationInfo from './PaginationInfo.vue'
import PaginationControls from './PaginationControls.vue'
import PaginationRowsPerPage from './PaginationRowsPerPage.vue'
</script>

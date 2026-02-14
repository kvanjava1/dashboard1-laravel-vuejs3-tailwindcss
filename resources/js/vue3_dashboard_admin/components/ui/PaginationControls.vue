<template>
    <div class="flex items-center gap-2">
        <Button
            type="button"
            variant="outline"
            size="xs"
            class="!p-0 size-8"
            :style="{ borderRadius: 'var(--radius-pagination)' }"
            :disabled="currentPage === 1"
            @click="$emit('prev')"
            aria-label="Previous page"
        >
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </Button>

        <div class="flex items-center gap-1">
            <Button
                v-for="page in totalPages"
                :key="page"
                type="button"
                :variant="page === currentPage ? 'primary' : 'outline'"
                size="xs"
                class="!p-0 size-8"
                :style="{ borderRadius: 'var(--radius-pagination)' }"
                @click="$emit('goto', page)"
            >
                {{ page }}
            </Button>
        </div>

        <Button
            type="button"
            variant="outline"
            size="xs"
            class="!p-0 size-8"
            :style="{ borderRadius: 'var(--radius-pagination)' }"
            @click="$emit('next')"
            aria-label="Next page"
        >
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </Button>
    </div>
</template>

<script setup lang="ts" define-props>
import Button from './Button.vue'

interface Props {
    currentPage: number
    totalPages: number
}

defineProps<Props>()
defineEmits<{
    prev: []
    next: []
    goto: [page: number]
}>()
</script>

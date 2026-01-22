<template>
    <div class="flex items-center gap-2">
        <button
            class="size-8 rounded-full border border-border-light text-slate-600 hover:text-primary hover:border-primary hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            :disabled="currentPage === 1" @click="$emit('prev')">
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </button>

        <div class="flex items-center gap-1">
            <button v-for="page in totalPages" :key="page" :class="[
                'size-8 rounded-full text-sm font-medium transition-colors flex items-center justify-center',
                page === currentPage
                    ? 'bg-primary text-white'
                    : 'border border-border-light text-slate-700 hover:bg-slate-100'
            ]" @click="$emit('goto', page)">
                {{ page }}
            </button>
        </div>

        <button
            class="size-8 rounded-full border border-border-light text-slate-600 hover:text-primary hover:border-primary hover:bg-slate-50 transition-colors flex items-center justify-center"
            @click="$emit('next')">
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </button>
    </div>
</template>

<script setup lang="ts" define-props>
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

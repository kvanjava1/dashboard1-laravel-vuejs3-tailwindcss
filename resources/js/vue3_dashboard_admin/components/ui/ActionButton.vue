<template>
    <button :class="{
        'px-4 lg:px-5 py-2.5 rounded-full text-sm font-bold transition-all duration-200 flex items-center gap-2 active:scale-95 shadow-md': true,
        'text-white bg-gradient-to-r from-primary to-primary-dark hover:shadow-hard hover:scale-[1.02]': variant === 'primary',
        'border border-border-light text-slate-700 hover:bg-white hover:border-slate-300 hover:shadow-medium': variant === 'secondary'
    }"
        v-bind="$attrs">
        <span v-if="icon" class="material-symbols-outlined text-[18px]">{{ icon }}</span>
        <slot />
    </button>
</template>

<script setup lang="ts">
interface Props {
    variant?: 'primary' | 'secondary'
    icon?: string
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary'
})
</script>

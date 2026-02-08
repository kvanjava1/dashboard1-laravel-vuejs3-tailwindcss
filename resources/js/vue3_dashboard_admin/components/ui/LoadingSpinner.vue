<template>
    <span class="material-symbols-outlined animate-spin" :class="[
        sizeClasses[size],
        colorClasses[color]
    ]">
        refresh
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    size?: 'xs' | 'sm' | 'md' | 'lg'
    color?: 'white' | 'primary' | 'slate'
}

const props = withDefaults(defineProps<Props>(), {
    size: 'sm',
    color: 'white'
})

const sizeClasses = {
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg'
}

const colorClasses = {
    white: 'text-white',
    primary: 'text-primary',
    slate: 'text-slate-400'
}
</script>

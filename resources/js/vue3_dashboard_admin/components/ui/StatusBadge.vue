<template>
    <span :class="badgeClass" :style="{ borderRadius: 'var(--radius-badge)' }">
        <span class="size-1.5 rounded-full" :class="dotClass"></span>
        <span>{{ displayLabel }}</span>
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type Status =
    | 'active'
    | 'inactive'
    | 'pending'
    | 'banned'
    | 'disabled'
    | 'enabled'
    | string

const props = withDefaults(defineProps<{
    status: Status | boolean
    label?: string
}>(), {
    label: ''
})

const normalized = computed(() => {
    if (typeof props.status === 'boolean') {
        return props.status ? 'active' : 'inactive'
    }
    return (props.status || '').toString().trim().toLowerCase()
})

const displayLabel = computed(() => {
    if (props.label) return props.label
    const value = normalized.value
    if (!value) return 'Unknown'
    return value.charAt(0).toUpperCase() + value.slice(1)
})

const tone = computed(() => {
    const value = normalized.value
    if (value === 'active' || value === 'enabled') return 'active'
    if (value === 'inactive' || value === 'disabled') return 'inactive'
    if (value === 'pending') return 'pending'
    if (value === 'banned') return 'banned'
    return 'inactive'
})

const badgeClass = computed(() => {
    const base = 'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold border'
    const variants: Record<string, string> = {
        active: 'bg-green-50 text-green-700 border-green-200',
        inactive: 'bg-slate-50 text-slate-600 border-slate-200',
        pending: 'bg-amber-50 text-amber-700 border-amber-200',
        banned: 'bg-red-50 text-red-700 border-red-200'
    }
    return [base, variants[tone.value]].join(' ')
})

const dotClass = computed(() => {
    const variants: Record<string, string> = {
        active: 'bg-green-500',
        inactive: 'bg-slate-400',
        pending: 'bg-amber-500',
        banned: 'bg-red-500'
    }
    return variants[tone.value]
})
</script>

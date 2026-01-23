<template>
    <button
        :class="buttonClasses"
        :disabled="disabled || loading"
        v-bind="$attrs"
        @click="handleClick"
    >
        <!-- Loading spinner -->
        <span v-if="loading" class="material-symbols-outlined text-sm animate-spin mr-2">
            refresh
        </span>

        <!-- Left icon -->
        <span v-if="leftIcon && !loading" class="material-symbols-outlined text-sm mr-2">
            {{ leftIcon }}
        </span>

        <!-- Button content -->
        <slot />

        <!-- Right icon -->
        <span v-if="rightIcon" class="material-symbols-outlined text-sm ml-2">
            {{ rightIcon }}
        </span>
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    variant?: 'primary' | 'secondary' | 'danger' | 'outline' | 'ghost' | 'success' | 'warning'
    size?: 'xs' | 'sm' | 'md' | 'lg'
    loading?: boolean
    disabled?: boolean
    leftIcon?: string
    rightIcon?: string
    fullWidth?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    loading: false,
    disabled: false,
    fullWidth: false
})

const emit = defineEmits<{
    click: [event: Event]
}>()

const handleClick = (event: Event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event)
    }
}

const buttonClasses = computed(() => {
    const baseClasses = [
        'inline-flex items-center justify-center font-medium transition-all duration-200',
        'focus:outline-none focus:ring-2 focus:ring-offset-2',
        'active:scale-95',
        'disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100'
    ]

    // Size classes
    const sizeClasses = {
        xs: 'px-2.5 py-1.5 text-xs rounded',
        sm: 'px-3 py-2 text-sm rounded-md',
        md: 'px-4 py-2.5 text-sm rounded-lg',
        lg: 'px-6 py-3 text-base rounded-lg'
    }

    // Variant classes
    const variantClasses = {
        primary: [
            'bg-primary text-white shadow-md',
            'hover:bg-primary-dark hover:shadow-lg',
            'focus:ring-primary/50',
            'disabled:hover:bg-primary'
        ].join(' '),

        secondary: [
            'bg-slate-600 text-white shadow-md',
            'hover:bg-slate-700 hover:shadow-lg',
            'focus:ring-slate-500/50',
            'disabled:hover:bg-slate-600'
        ].join(' '),

        danger: [
            'bg-red-600 text-white shadow-md',
            'hover:bg-red-700 hover:shadow-lg',
            'focus:ring-red-500/50',
            'disabled:hover:bg-red-600'
        ].join(' '),

        success: [
            'bg-green-600 text-white shadow-md',
            'hover:bg-green-700 hover:shadow-lg',
            'focus:ring-green-500/50',
            'disabled:hover:bg-green-600'
        ].join(' '),

        warning: [
            'bg-amber-600 text-white shadow-md',
            'hover:bg-amber-700 hover:shadow-lg',
            'focus:ring-amber-500/50',
            'disabled:hover:bg-amber-600'
        ].join(' '),

        outline: [
            'border border-slate-300 bg-white text-slate-700 shadow-sm',
            'hover:bg-slate-50 hover:border-slate-400 hover:shadow-md',
            'focus:ring-slate-500/50',
            'disabled:hover:bg-white disabled:hover:border-slate-300'
        ].join(' '),

        ghost: [
            'text-slate-600 bg-transparent',
            'hover:bg-slate-100 hover:text-slate-900',
            'focus:ring-slate-500/50',
            'disabled:hover:bg-transparent disabled:hover:text-slate-600'
        ].join(' ')
    }

    // Full width
    if (props.fullWidth) {
        baseClasses.push('w-full')
    }

    // Loading state adjustments
    if (props.loading) {
        baseClasses.push('cursor-wait')
    }

    return [
        ...baseClasses,
        sizeClasses[props.size],
        variantClasses[props.variant]
    ].join(' ')
})
</script>
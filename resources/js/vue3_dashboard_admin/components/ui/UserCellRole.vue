<template>
    <span :class="[
        'px-3 py-1 text-xs font-medium rounded-full',
        roleClass(role)
    ]">
        {{ role }}
    </span>
</template>

<script setup lang="ts" define-props>
interface Props {
    role: 'Administrator' | 'Editor' | 'Viewer'
}

defineProps<Props>()

function roleClass(role: string): string {
    const roleClasses: Record<string, string> = {
        'Administrator': 'bg-primary/10 text-primary',
        'Editor': 'bg-secondary/10 text-secondary',
        'Viewer': 'bg-slate-100 text-slate-700'
    }
    return roleClasses[role] || 'bg-slate-100 text-slate-700'
}
</script>

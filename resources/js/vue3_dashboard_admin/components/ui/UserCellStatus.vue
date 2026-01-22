<template>
    <div class="flex items-center gap-2">
        <span :class="[
            'size-2 rounded-full',
            statusColor(status),
            { 'animate-pulse': status === 'Active' }
        ]">
        </span>
        <span class="text-sm text-slate-700">{{ status }}</span>
    </div>
</template>

<script setup lang="ts" define-props>
interface Props {
    status: 'Active' | 'Pending' | 'Inactive'
}

defineProps<Props>()

function statusColor(status: string): string {
    const statusColors: Record<string, string> = {
        'Active': 'bg-success',
        'Pending': 'bg-warning',
        'Inactive': 'bg-danger'
    }
    return statusColors[status] || 'bg-slate-400'
}
</script>

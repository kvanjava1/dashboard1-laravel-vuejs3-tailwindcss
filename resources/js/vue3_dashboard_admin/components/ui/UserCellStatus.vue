<template>
    <div class="flex items-center gap-2">
        <span :class="[
            'size-2 rounded-full',
            statusColor(status),
            { 'animate-pulse': status === 'active' || status === 'Active' }
        ]">
        </span>
        <span class="text-sm text-slate-700">{{ formatStatus(status) }}</span>
    </div>
</template>

<script setup lang="ts" define-props>
interface Props {
    status: string
}

defineProps<Props>()

function statusColor(status: string): string {
    const statusColors: Record<string, string> = {
        'active': 'bg-success',
        'Active': 'bg-success',
        'pending': 'bg-warning',
        'Pending': 'bg-warning',
        'inactive': 'bg-danger',
        'Inactive': 'bg-danger'
    }
    return statusColors[status] || 'bg-slate-400'
}

function formatStatus(status: string): string {
    if (!status) return 'Unknown'
    return status.charAt(0).toUpperCase() + status.slice(1)
}
</script>

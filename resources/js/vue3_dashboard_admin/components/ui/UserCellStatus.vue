<template>
    <StatusBadge :status="normalizedStatus" :label="formatStatus(status)" />
</template>

<script setup lang="ts" define-props>
import { computed } from 'vue'
import StatusBadge from './StatusBadge.vue'

interface Props {
    status: string
}

const props = defineProps<Props>()

const normalizedStatus = computed(() => (props.status || '').toString().trim().toLowerCase())

function formatStatus(status: string): string {
    if (!status) return 'Unknown'
    return status.charAt(0).toUpperCase() + status.slice(1)
}
</script>

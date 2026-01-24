<template>
    <span :class="[
        'px-3 py-1 text-xs font-medium rounded-full',
        roleClass(role)
    ]">
        {{ formatRole(role) }}
    </span>
</template>

<script setup lang="ts" define-props>
interface Props {
    role: string
}

defineProps<Props>()

function roleClass(role: string): string {
    const roleClasses: Record<string, string> = {
        'Super Administrator': 'bg-purple-100 text-purple-800',
        'Administrator': 'bg-primary/10 text-primary',
        'Editor': 'bg-secondary/10 text-secondary',
        'Viewer': 'bg-slate-100 text-slate-700',
        'Moderator': 'bg-blue-100 text-blue-800',
        'super_admin': 'bg-purple-100 text-purple-800',
        'administrator': 'bg-primary/10 text-primary',
        'editor': 'bg-secondary/10 text-secondary',
        'viewer': 'bg-slate-100 text-slate-700',
        'moderator': 'bg-blue-100 text-blue-800'
    }
    return roleClasses[role] || 'bg-slate-100 text-slate-700'
}

function formatRole(role: string): string {
    if (!role) return 'Unknown'
    
    // If it's already formatted, return as is
    if (role.includes(' ')) return role
    
    // Convert snake_case or camelCase to Title Case with spaces
    return role
        .replace(/_/g, ' ')
        .replace(/([A-Z])/g, ' $1')
        .replace(/^./, str => str.toUpperCase())
        .trim()
}
</script>

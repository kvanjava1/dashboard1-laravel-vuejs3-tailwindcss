<template>
    <div class="flex items-center gap-2">
        <Button
            v-if="showEdit !== false"
            variant="outline"
            size="sm"
            left-icon="edit"
            @click="$emit('edit')"
        >
            Edit
        </Button>
        <Button
            v-if="showBan !== false"
            :variant="user?.is_banned ? 'success' : 'warning'"
            size="sm"
            :left-icon="user?.is_banned ? 'check_circle' : 'block'"
            @click="$emit('ban')"
            :title="user?.is_banned ? 'Manage Ban Status' : 'Ban User'"
        >
            {{ user?.is_banned ? 'Unban' : 'Ban' }}
        </Button>
        <Button
            v-if="showDelete !== false"
            variant="danger"
            size="sm"
            left-icon="delete"
            @click="$emit('delete')"
        >
            Delete
        </Button>
        <Button
            v-if="showView !== false"
            variant="outline"
            size="sm"
            left-icon="visibility"
            @click="$emit('view')"
        >
            View
        </Button>
    </div>
</template>

<script setup lang="ts">
import Button from './Button.vue'

defineProps<{ 
    showEdit?: boolean; 
    showDelete?: boolean; 
    showView?: boolean;
    showBan?: boolean;
    user?: any;
}>()
defineEmits<{
    edit: []
    delete: []
    view: []
    ban: []
}>()
</script>

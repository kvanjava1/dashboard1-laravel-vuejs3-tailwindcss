<template>
    <div class="flex items-center gap-2">
        <button
            v-if="showEdit !== false"
            class="p-2 text-slate-600 hover:text-primary hover:bg-slate-100 rounded-full transition-colors"
            @click="$emit('edit')">
            <span class="material-symbols-outlined text-[20px]">edit</span>
        </button>
        <button
            v-if="showDelete !== false"
            class="p-2 text-slate-600 hover:text-danger hover:bg-slate-100 rounded-full transition-colors"
            @click="$emit('delete')">
            <span class="material-symbols-outlined text-[20px]">delete</span>
        </button>
        <button
            v-if="showView !== false"
            class="p-2 text-slate-600 hover:text-info hover:bg-slate-100 rounded-full transition-colors"
            @click="$emit('view')">
            <span class="material-symbols-outlined text-[20px]">visibility</span>
        </button>
    </div>
</template>

<script setup lang="ts">
defineProps<{ showEdit?: boolean; showDelete?: boolean; showView?: boolean }>()
defineEmits<{
    edit: []
    delete: []
    view: []
}>()
</script>

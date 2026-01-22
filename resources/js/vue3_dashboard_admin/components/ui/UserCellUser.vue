<template>
    <div class="flex items-center gap-3">
        <div class="size-10 rounded-full bg-cover bg-center border border-border-light"
            :style="{ backgroundImage: `url(${avatar})` }">
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-800">{{ name }}</p>
            <p class="text-xs text-slate-500">{{ email }}</p>
        </div>
    </div>
</template>

<script setup lang="ts" define-props>
interface Props {
    name: string
    email: string
    avatar: string
}

defineProps<Props>()
</script>

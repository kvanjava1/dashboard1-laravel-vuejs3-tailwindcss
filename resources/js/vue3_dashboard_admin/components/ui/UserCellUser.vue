<template>
    <div class="flex items-center gap-3">
        <div class="relative size-10 border border-border-light flex items-center justify-center bg-slate-200 overflow-hidden" :style="{ borderRadius: 'var(--radius-avatar)' }">
            <img
                :src="avatar && avatar.trim() !== '' ? avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&color=fff&size=64`"
                alt="Avatar"
                class="absolute inset-0 w-full h-full object-cover"
                :style="{ borderRadius: 'var(--radius-avatar)' }"
                loading="lazy"
                @error="(e) => { const t = e.target as HTMLImageElement | null; if (t) { t.style.display = 'none'; const s = t.nextElementSibling as HTMLElement | null; if (s) s.style.display = 'flex'; } }"
            />
            <span
                class="w-full h-full flex items-center justify-center text-xs font-bold text-white select-none"
                style="display: none; background: #64748b;"
            >
                {{ name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2) }}
            </span>
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

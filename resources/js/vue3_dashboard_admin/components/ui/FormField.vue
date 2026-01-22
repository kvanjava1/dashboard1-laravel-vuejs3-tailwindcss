<template>
    <div class="space-y-2">
        <label
            v-if="label"
            :for="inputId"
            class="block text-sm font-semibold text-slate-700"
        >
            {{ label }}
            <span v-if="required" class="text-danger">*</span>
        </label>

        <select
            v-if="type === 'select'"
            :id="inputId"
            :value="modelValue"
            :required="required"
            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
            @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <slot />
        </select>

        <input
            v-else-if="type === 'date'"
            :id="inputId"
            :value="modelValue"
            :type="type"
            :placeholder="placeholder"
            :required="required"
            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />

        <input
            v-else
            :id="inputId"
            :value="modelValue"
            :type="type"
            :placeholder="placeholder"
            :required="required"
            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />

        <p v-if="help" class="text-xs text-slate-500">
            {{ help }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    modelValue: string | number
    label?: string
    type?: 'text' | 'email' | 'password' | 'date' | 'select'
    placeholder?: string
    help?: string
    required?: boolean
    id?: string
}

interface Emits {
    (e: 'update:modelValue', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    required: false
})

defineEmits<Emits>()

const inputId = computed(() => props.id || `field-${Math.random().toString(36).substr(2, 9)}`)
</script>
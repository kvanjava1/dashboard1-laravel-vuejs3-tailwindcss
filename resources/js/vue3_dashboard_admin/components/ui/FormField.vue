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

        <div class="relative">
            <!-- Left Icon -->
            <div
                v-if="leftIcon"
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
            >
                <span class="material-symbols-outlined text-slate-400 text-[20px]">{{ leftIcon }}</span>
            </div>

            <select
                v-if="type === 'select'"
                :id="inputId"
                :value="modelValue"
                :required="required"
                :class="[
                    'w-full border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer',
                    leftIcon ? 'pl-10 pr-4' : 'px-4',
                    'py-2.5'
                ]"
                :style="{ borderRadius: 'var(--radius-select)' }"
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
                :class="[
                    'w-full border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all',
                    leftIcon ? 'pl-10 pr-4' : 'px-4',
                    'py-2.5'
                ]"
                :style="{ borderRadius: 'var(--radius-input)' }"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            />

            <textarea
                v-else-if="type === 'textarea'"
                :id="inputId"
                :value="modelValue"
                :placeholder="placeholder"
                :required="required"
                :class="[
                    'w-full border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all',
                    leftIcon ? 'pl-10 pr-4' : 'px-4',
                    'py-2.5'
                ]"
                :style="{ borderRadius: 'var(--radius-input)' }"
                @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
            />

            <input
                v-else-if="type === 'number'"
                :id="inputId"
                :value="modelValue"
                type="number"
                :placeholder="placeholder"
                :required="required"
                :class="[
                    'w-full border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all',
                    leftIcon ? 'pl-10 pr-4' : 'px-4',
                    'py-2.5'
                ]"
                :style="{ borderRadius: 'var(--radius-input)' }"
                @input="$emit('update:modelValue', Number(($event.target as HTMLInputElement).value))"
            />

            <input
                v-else
                :id="inputId"
                :value="modelValue"
                :type="type"
                :placeholder="placeholder"
                :required="required"
                :class="[
                    'w-full border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all',
                    leftIcon ? 'pl-10 pr-4' : 'px-4',
                    'py-2.5'
                ]"
                :style="{ borderRadius: 'var(--radius-input)' }"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            />
        </div>

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
    type?: 'text' | 'email' | 'password' | 'date' | 'tel' | 'select' | 'textarea' | 'number'
    placeholder?: string
    help?: string
    required?: boolean
    id?: string
    leftIcon?: string
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
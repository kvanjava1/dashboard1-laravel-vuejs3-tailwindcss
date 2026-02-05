<template>
    <ContentBox>
        <ContentBoxHeader>
            <template #title>
                <ContentBoxTitle
                    :title="mode === 'edit' ? 'Edit Category' : 'Create Category'"
                    subtitle="UI-only for now (dummy local data)"
                />
            </template>
        </ContentBoxHeader>

        <ContentBoxBody>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                    v-model="form.name"
                    label="Name"
                    placeholder="e.g. Announcements"
                    required
                    left-icon="category"
                />

                <FormField
                    v-model="form.slug"
                    label="Slug"
                    placeholder="e.g. announcements"
                    help="Leave empty to auto-generate from the name."
                    left-icon="link"
                />

                <FormField v-model="form.status" label="Status" type="select" left-icon="toggle_on">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </FormField>

                <div class="md:col-span-2">
                    <FormField
                        v-model="form.description"
                        label="Description"
                        type="textarea"
                        placeholder="Optional short description..."
                        left-icon="notes"
                    />
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mt-8 pt-6 border-t border-border-light">
                <Button variant="outline" @click="$emit('cancel')">Cancel</Button>
                <Button :loading="isSaving" variant="primary" left-icon="save" @click="handleSubmit">
                    {{ mode === 'edit' ? 'Save Changes' : 'Create' }}
                </Button>
            </div>
        </ContentBoxBody>
    </ContentBox>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import ContentBox from '../ui/ContentBox.vue'
import ContentBoxHeader from '../ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../ui/ContentBoxTitle.vue'
import ContentBoxBody from '../ui/ContentBoxBody.vue'
import FormField from '../ui/FormField.vue'
import Button from '../ui/Button.vue'
import { showToast } from '@/composables/useSweetAlert'

type Mode = 'create' | 'edit'

type Category = {
    id: number
    name: string
    slug: string
    description?: string
    is_active: boolean
    created_at: string
    updated_at: string
}

const props = defineProps<{
    mode: Mode
    category?: Category | null
}>()

const emit = defineEmits<{
    cancel: []
    success: [category: Category]
}>()

const isSaving = ref(false)

const form = reactive({
    name: '',
    slug: '',
    description: '',
    status: 'active' as 'active' | 'inactive'
})

watch(() => props.category, (value) => {
    if (props.mode !== 'edit' || !value) return
    form.name = value.name
    form.slug = value.slug
    form.description = value.description ?? ''
    form.status = value.is_active ? 'active' : 'inactive'
}, { immediate: true })

const slugify = (value: string) => {
    return value
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
}

const handleSubmit = async () => {
    const name = form.name.trim()
    if (!name) {
        await showToast({ icon: 'warning', title: 'Name is required' })
        return
    }

    const computedSlug = (form.slug.trim() ? slugify(form.slug) : slugify(name)) || 'category'
    const description = form.description.trim()
    const isActive = form.status === 'active'

    isSaving.value = true
    try {
        let category: Category

        if (props.mode === 'edit') {
            const current = props.category
            if (!current) throw new Error('Category not found')

            category = {
                ...current,
                name,
                slug: computedSlug,
                is_active: isActive,
                updated_at: new Date().toISOString(),
                ...(description ? { description } : {})
            }

            await showToast({ icon: 'success', title: 'Category updated (dummy)', timer: 1200 })
        } else {
            const now = new Date().toISOString()
            category = {
                id: Date.now(),
                name,
                slug: computedSlug,
                is_active: isActive,
                created_at: now,
                updated_at: now,
                ...(description ? { description } : {})
            }
            await showToast({ icon: 'success', title: 'Category created (dummy)', timer: 1200 })
        }

        emit('success', category)
    } catch (err) {
        await showToast({ icon: 'error', title: 'Failed', text: err instanceof Error ? err.message : 'Unknown error' })
    } finally {
        isSaving.value = false
    }
}
</script>

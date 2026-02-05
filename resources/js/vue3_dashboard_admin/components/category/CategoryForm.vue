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

                <FormField
                    v-model="form.parent_id"
                    label="Parent Category"
                    type="select"
                    help="Optional. Leave as Root for top-level category."
                    left-icon="account_tree"
                >
                    <option value="">Root (no parent)</option>
                    <option
                        v-for="opt in parentOptions"
                        :key="opt.id"
                        :value="String(opt.id)"
                    >
                        {{ opt.label }}
                    </option>
                </FormField>

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
import { computed, reactive, ref, watch } from 'vue'
import ContentBox from '../ui/ContentBox.vue'
import ContentBoxHeader from '../ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../ui/ContentBoxTitle.vue'
import ContentBoxBody from '../ui/ContentBoxBody.vue'
import FormField from '../ui/FormField.vue'
import Button from '../ui/Button.vue'
import { showToast } from '@/composables/useSweetAlert'
import type { Category } from '@/mocks/categories'

type Mode = 'create' | 'edit'

const props = defineProps<{
    mode: Mode
    category?: Category | null
    allCategories?: Category[]
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
    parent_id: '',
    status: 'active' as 'active' | 'inactive'
})

watch(() => props.category, (value) => {
    if (props.mode !== 'edit' || !value) return
    form.name = value.name
    form.slug = value.slug
    form.description = value.description ?? ''
    form.status = value.is_active ? 'active' : 'inactive'
    form.parent_id = value.parent_id === null ? '' : String(value.parent_id)
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
    const all = props.allCategories || []
    const currentId = props.mode === 'edit' ? (props.category?.id ?? null) : null
    const slugTaken = all.some((c) => c.slug.toLowerCase() === computedSlug.toLowerCase() && c.id !== currentId)
    if (slugTaken) {
        await showToast({ icon: 'warning', title: 'Slug already exists', text: 'Slug must be globally unique.' })
        return
    }

    const parentId = form.parent_id ? Number(form.parent_id) : null
    if (form.parent_id && !Number.isFinite(parentId)) {
        await showToast({ icon: 'warning', title: 'Invalid parent category' })
        return
    }
    if (currentId && parentId === currentId) {
        await showToast({ icon: 'warning', title: 'Invalid parent category', text: 'A category cannot be its own parent.' })
        return
    }

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
                parent_id: parentId,
                is_active: isActive,
                updated_at: new Date().toISOString(),
                ...(description ? { description } : {})
            }

            await showToast({ icon: 'success', title: 'Category updated (dummy)', timer: 1200 })
        } else {
            const now = new Date().toISOString()
            category = {
                id: Date.now(),
                parent_id: parentId,
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

type ParentOption = { id: number; label: string }

const parentOptions = computed<ParentOption[]>(() => {
    const all = props.allCategories || []
    const currentId = props.mode === 'edit' ? (props.category?.id ?? null) : null

    const byId = new Map<number, Category>()
    for (const c of all) byId.set(c.id, c)

    const childrenByParent = new Map<number | null, Category[]>()
    for (const c of all) {
        const parentKey = c.parent_id !== null && byId.has(c.parent_id) ? c.parent_id : null
        const bucket = childrenByParent.get(parentKey) || []
        bucket.push(c)
        childrenByParent.set(parentKey, bucket)
    }

    const excluded = new Set<number>()
    if (currentId) {
        excluded.add(currentId)
        const stack = [currentId]
        while (stack.length) {
            const id = stack.pop()!
            const kids = childrenByParent.get(id) || []
            for (const k of kids) {
                if (!excluded.has(k.id)) {
                    excluded.add(k.id)
                    stack.push(k.id)
                }
            }
        }
    }

    const result: ParentOption[] = []
    const walk = (parentId: number | null, depth: number) => {
        const children = childrenByParent.get(parentId) || []
        for (const child of children) {
            if (!excluded.has(child.id)) {
                const prefix = depth === 0 ? '' : `${'—'.repeat(depth)} `
                result.push({ id: child.id, label: `${prefix}${child.name}` })
            }
            walk(child.id, depth + 1)
        }
    }

    walk(null, 0)
    return result
})
</script>

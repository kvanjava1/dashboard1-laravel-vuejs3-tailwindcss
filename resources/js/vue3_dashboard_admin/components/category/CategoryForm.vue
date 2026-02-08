<template>
    <ContentBox>
        <ContentBoxHeader>
            <template #title>
                <ContentBoxTitle :title="mode === 'edit' ? 'Edit Category' : 'Create Category'"
                    :subtitle="mode === 'edit' ? 'Update category details' : 'Add a new category'" />
            </template>
        </ContentBoxHeader>

        <ContentBoxBody>
            <!-- Error State -->
            <ErrorState v-if="error" :message="error" @retry="() => { error = null }" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Type Selection -->
                <FormField v-model="form.type" label="Type" type="select" required left-icon="category"
                    :disabled="mode === 'edit'" help="Category type cannot be changed after creation.">
                    <option value="" disabled selected>Select Type</option>
                    <option value="article">Article</option>
                    <option value="gallery">Gallery</option>
                </FormField>

                <FormField v-model="form.name" label="Name" placeholder="e.g. Announcements" required
                    left-icon="label" />

                <FormField v-model="form.slug" label="Slug" placeholder="e.g. announcements"
                    help="Leave empty to auto-generate from the name." left-icon="link"
                    :error="validationErrors.slug?.[0]" />

                <FormField v-model="form.parent_id" label="Parent Category" type="select"
                    help="Optional. Select Root for top-level category." left-icon="account_tree"
                    :error="validationErrors.parent_id?.[0]">
                    <option value="" disabled selected>Select Parent Category</option>
                    <option value="root">Root (no parent)</option>
                    <option v-for="opt in parentOptions" :key="opt.id" :value="String(opt.id)">
                        {{ opt.label }}
                    </option>
                </FormField>

                <FormField v-model="form.status" label="Status" type="select" left-icon="toggle_on">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </FormField>

                <div class="md:col-span-2">
                    <FormField v-model="form.description" label="Description" type="textarea"
                        placeholder="Optional short description..." left-icon="notes" />
                </div>
            </div>

            <div
                class="flex flex-col sm:flex-row items-center justify-end gap-3 mt-8 pt-6 border-t border-border-light">
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
import ErrorState from '../ui/ErrorState.vue'
import { showToast } from '@/composables/useSweetAlert'
import { useApi } from '@/composables/useApi'
import { apiRoutes } from '@/config/apiRoutes'

// Define a local interface or import from a shared types file if available
interface Category {
    id: number
    name: string
    slug: string
    description?: string
    parent_id: number | null
    is_active: boolean
    type: string | { slug: string }
    children?: Category[]
}

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

const { post, put } = useApi()
const isSaving = ref(false)
const error = ref<string | null>(null)
const validationErrors = ref<Record<string, string[]>>({})

const form = reactive({
    name: '',
    slug: '',
    description: '',
    parent_id: '',
    status: 'active' as 'active' | 'inactive',
    type: '' // No default type to force user awareness
})

watch(() => props.category, (value) => {
    if (props.mode !== 'edit' || !value) return
    form.name = value.name
    form.slug = value.slug
    form.description = value.description ?? ''
    form.status = value.is_active ? 'active' : 'inactive'
    form.parent_id = value.parent_id === null ? 'root' : String(value.parent_id)

    // Handle type: might be string or object from backend relation
    if (typeof value.type === 'object' && value.type !== null) {
        form.type = (value.type as any).slug || 'article'
    } else {
        form.type = (value.type as string) || 'article'
    }
}, { immediate: true })

const slugify = (value: string) => {
    return value
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
}

// Watch for type change to reset parent_id
watch(() => form.type, () => {
    if (props.mode === 'create') {
        form.parent_id = ''
    }
})

const handleSubmit = async () => {
    const name = form.name.trim()
    if (!name) {
        await showToast({ icon: 'warning', title: 'Name is required' })
        return
    }

    if (!form.type) {
        await showToast({ icon: 'warning', title: 'Category Type is required' })
        return
    }

    if (!form.parent_id) {
        await showToast({ icon: 'warning', title: 'Parent Category is required', text: 'Please select a parent category or "Root"' })
        return
    }

    // Auto-generate slug if empty, but let backend handle validation/uniqueness mostly
    // We can do simple slug generation here for UX
    const computedSlug = form.slug.trim() || slugify(name)

    const parentId = form.parent_id === 'root' ? null : Number(form.parent_id)
    const description = form.description.trim()
    const isActive = form.status === 'active'

    const payload = {
        name,
        slug: computedSlug,
        parent_id: parentId,
        description,
        is_active: isActive,
        type: form.type
    }

    isSaving.value = true
    error.value = null
    validationErrors.value = {}

    try {
        let response
        if (props.mode === 'edit' && props.category) {
            response = await put(apiRoutes.categories.update(props.category.id), payload)
        } else {
            response = await post(apiRoutes.categories.store, payload)
        }

        if (response.ok) {
            const data = await response.json()
            const savedCategory = data.data

            await showToast({
                icon: 'success',
                title: props.mode === 'edit' ? 'Category updated' : 'Category created',
                timer: 1500
            })

            emit('success', savedCategory)
        } else {
            const errorData = await response.json()
            if (errorData.errors) {
                validationErrors.value = errorData.errors
                await showToast({ icon: 'error', title: 'Validation Error', text: 'Please check the form for errors.' })
            } else {
                error.value = errorData.message || 'Failed to save category.'
                await showToast({ icon: 'error', title: 'Error', text: error.value || 'Unknown error' })
            }
        }
    } catch (err: any) {
        error.value = err.message || 'An unexpected error occurred.'
        await showToast({ icon: 'error', title: 'Error', text: error.value || 'Unknown error' })
    } finally {
        isSaving.value = false
    }
}

type ParentOption = { id: number; label: string }

const parentOptions = computed<ParentOption[]>(() => {
    let all = props.allCategories || []

    // Filter by selected type to ensure parent is same type
    if (form.type) {
        all = all.filter(c => {
            const catType = typeof c.type === 'object' && c.type !== null ? (c.type as any).slug : c.type
            return catType === form.type
        })
    }

    const currentId = props.mode === 'edit' ? (props.category?.id ?? null) : null

    // Helper to build hierarchy options, excluding current node and its children (to prevent cycles)
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
        children.sort((a, b) => a.name.localeCompare(b.name)) // Sort by name

        for (const child of children) {
            if (!excluded.has(child.id)) {
                const prefix = depth === 0 ? '' : `${'—'.repeat(depth)} `
                result.push({ id: child.id, label: `${prefix}${child.name}` })
                walk(child.id, depth + 1)
            }
        }
    }

    walk(null, 0)
    return result
})
</script>

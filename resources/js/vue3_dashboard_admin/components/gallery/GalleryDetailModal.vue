<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="modelValue"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                @click.self="close">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-primary/10">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-3xl">photo_library</span>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ gallery?.title || 'Gallery Details' }}
                                </h2>
                                <p class="text-sm text-gray-600 mt-1">{{ gallery?.category }}</p>
                            </div>
                        </div>
                        <button @click="close" class="p-2 rounded-lg hover:bg-gray-100 transition-colors" title="Close">
                            <span class="material-symbols-outlined text-gray-600">close</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="overflow-y-auto max-h-[calc(90vh-180px)]">
                        <div v-if="gallery" class="p-6 space-y-6">
                            <!-- Cover Image -->
                            <div class="rounded-xl overflow-hidden shadow-lg">
                                <img :src="gallery.coverImage" :alt="gallery.title" class="w-full h-auto" />
                            </div>



                            <!-- Gallery Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="material-symbols-outlined text-blue-600">photo_library</span>
                                        <span class="text-sm font-medium text-blue-900">Total Items</span>
                                    </div>
                                    <p class="text-3xl font-bold text-blue-600">{{ gallery.itemCount }}</p>
                                </div>

                                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="material-symbols-outlined text-green-600">toggle_on</span>
                                        <span class="text-sm font-medium text-green-900">Status</span>
                                    </div>
                                    <StatusBadge :status="gallery.status" class="text-lg" />
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">description</span>
                                    Description
                                </h3>
                                <p class="text-gray-700 leading-relaxed">{{ gallery.description }}</p>
                            </div>

                            <!-- Additional Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Category</p>
                                    <p class="text-gray-900 font-semibold">{{ gallery.category }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Created Date</p>
                                    <p class="text-gray-900 font-semibold">{{ formatDateTime(gallery.createdAt) }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Updated Date</p>
                                    <p class="text-gray-900 font-semibold">{{ formatDateTime(gallery.updatedAt || gallery.updated_at) }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Slug</p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-gray-900 font-mono text-sm truncate">{{ gallery.slug }}</p>
                                        <button v-if="gallery.slug" @click="copySlug" class="p-1 rounded-md hover:bg-gray-100 transition-colors" title="Copy slug">
                                            <span class="material-symbols-outlined text-sm">content_copy</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <p class="text-sm font-medium text-gray-500 mb-1">Tags</p>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <span v-for="(t, idx) in (gallery.tags || [])" :key="idx" class="px-3 py-1.5 rounded-full text-sm font-bold border border-border-light text-slate-700 bg-white">
                                            {{ t }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                        <Button variant="outline" @click="close">
                            Close
                        </Button>
                        <Button v-if="canEditGallery" variant="primary" left-icon="edit" @click="editGallery">
                            Edit Gallery
                        </Button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { computed } from 'vue'
import Button from '../ui/Button.vue'
import StatusBadge from '../ui/StatusBadge.vue'
import { useAuthStore } from '@/stores/auth'
import { showToast } from '@/composables/useSweetAlert'

interface Props {
    modelValue: boolean
    gallery: any
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()
const router = useRouter()
const authStore = useAuthStore()

const close = () => {
    emit('update:modelValue', false)
}

const canEditGallery = computed(() => authStore.hasPermission('gallery_management.edit'))

const editGallery = () => {
    if (props.gallery && canEditGallery.value) {
        router.push({ name: 'gallery_management.edit', params: { id: props.gallery.id } })
        close()
    } else {
        showToast({ icon: 'error', title: 'Access Denied', text: 'You do not have permission to edit this gallery.' })
    }
}

function formatDateTime(dateString: string): string {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const copySlug = async () => {
    try {
        if (!props.gallery?.slug) return
        await navigator.clipboard.writeText(String(props.gallery.slug))
        await showToast({ icon: 'success', title: 'Copied', text: 'Slug copied to clipboard' })
    } catch (e) {
        await showToast({ icon: 'error', title: 'Error', text: 'Failed to copy slug' })
    }
}


</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .bg-white,
.modal-leave-active .bg-white {
    transition: transform 0.3s ease;
}

.modal-enter-from .bg-white,
.modal-leave-to .bg-white {
    transform: scale(0.95);
}
</style>

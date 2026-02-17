<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="close">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden">
          <!-- Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-primary/10">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-primary text-3xl">image</span>
              <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ image?.name || 'Image' }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ image?.gallery?.title || 'Media item' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button @click="close" class="p-2 rounded-lg hover:bg-gray-100 transition-colors" title="Close">
                <span class="material-symbols-outlined text-gray-600">close</span>
              </button>
            </div>
          </div>

          <!-- Body -->
          <div class="overflow-y-auto max-h-[calc(90vh-180px)]">
            <div v-if="image" class="p-6 space-y-6">
              <div class="rounded-xl overflow-hidden shadow-lg">
                <img :src="image.url" :alt="image.alt_text || image.name" class="w-full h-auto object-contain" />
              </div>

              <div class="space-y-4">
                <div>
                  <p class="text-sm font-medium text-gray-500 mb-1">Filename</p>
                  <p class="text-gray-900 font-mono text-sm truncate">{{ image.filename }}</p>
                </div>

                <div>
                  <p class="text-sm font-medium text-gray-500 mb-1">Uploaded</p>
                  <p class="text-gray-900 font-semibold">{{ formatDateTime(image.uploaded_at) }}</p>
                </div>

                <div>
                  <p class="text-sm font-medium text-gray-500 mb-1">Size</p>
                  <p class="text-gray-900 font-semibold">{{ humanSize(image.size) }}</p>
                </div>

                <div>
                  <p class="text-sm font-medium text-gray-500 mb-1">Type</p>
                  <p class="text-gray-900 font-semibold">{{ image.mime_type || image.extension }}</p>
                </div>

                <div>
                  <p class="text-sm font-medium text-gray-500 mb-1">Alt Text</p>
                  <p class="text-gray-700 leading-relaxed">{{ image.alt_text || '-' }}</p>
                </div>

                <div v-if="image.gallery">
                  <p class="text-sm font-medium text-gray-500 mb-1">Gallery</p>
                  <div class="flex items-center gap-2">
                    <p class="text-gray-900 font-semibold">{{ image.gallery.title }}</p>
                    <router-link v-if="canViewGallery" :to="{ name: 'gallery_management.edit', params: { id: image.gallery.id } }" class="text-sm text-primary hover:underline ml-2">Open gallery</router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
            <a :href="image?.url" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border-light hover:bg-gray-100 transition-colors text-sm">
              <span class="material-symbols-outlined text-sm">download</span>
              Download
            </a>

            <Button v-if="canEditImage" variant="outline" left-icon="edit" @click="editImage">Edit</Button>
            <Button v-if="canDeleteImage" variant="danger" left-icon="delete" @click="confirmDelete">Delete</Button>
            <Button variant="secondary" @click="close">Close</Button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import Button from '../ui/Button.vue'
import { useAuthStore } from '@/stores/auth'
import { showConfirm, showToast } from '@/composables/useSweetAlert'
import { useMediaData } from '@/composables/image/useMediaData'

interface Props {
  modelValue: boolean
  image?: any
}
const props = defineProps<Props>()

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'deleted', id: number): void
}
const emit = defineEmits<Emits>()

const router = useRouter()
const authStore = useAuthStore()
const { deleteMedia } = useMediaData()

const close = () => emit('update:modelValue', false)

const canEditImage = computed(() => authStore.hasPermission('image_management.edit'))
const canDeleteImage = computed(() => authStore.hasPermission('image_management.delete'))
const canViewGallery = computed(() => authStore.hasPermission('gallery_management.view'))

const editImage = () => {
  if (!props.image) return
  router.push({ name: 'image_management.edit', params: { id: props.image.id } })
  close()
}

const confirmDelete = async () => {
  if (!props.image) return
  const ok = await showConfirm({ title: 'Delete Image?', text: `Delete "${props.image.name}" permanently?`, icon: 'warning' })
  if (!ok) return
  try {
    await deleteMedia(props.image.id)
    await showToast({ icon: 'success', title: 'Deleted', text: 'Image deleted.' })
    emit('deleted', props.image.id)
    close()
  } catch (err: any) {
    await showToast({ icon: 'error', title: 'Error', text: err.message || 'Failed to delete image' })
  }
}

function humanSize(bytes: number) {
  if (!bytes && bytes !== 0) return ''
  const mb = bytes / (1024 * 1024)
  return `${mb.toFixed(mb < 1 ? 2 : 1)} MB`
}

function formatDateTime(dateString: string) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
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

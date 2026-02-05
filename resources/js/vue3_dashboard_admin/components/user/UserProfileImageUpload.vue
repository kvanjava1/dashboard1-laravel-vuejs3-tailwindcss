<template>
  <div class="flex flex-col items-center space-y-4">
    <!-- Cropper Modal -->
    <div
      v-if="showCropper"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
        <h3 class="text-lg font-semibold mb-4 text-center">Crop Your Profile Picture</h3>

        <!-- Vue Advanced Cropper -->
        <div class="mb-4">
          <Cropper
            ref="cropperRef"
            :src="cropperImage"
            :stencil-props="{
              aspectRatio: 1
            }"
            :canvas="{
              height: 300,
              width: 300
            }"
          />
        </div>

        <!-- Crop Controls -->
        <div class="flex justify-center gap-3">
          <Button @click="cancelCrop" variant="outline">
            Cancel
          </Button>
          <Button @click="applyCrop" variant="primary">
            <span class="material-symbols-outlined mr-2">crop</span>
            Apply Crop
          </Button>
        </div>
      </div>
    </div>

    <!-- Image Preview (1:1 aspect ratio, circular) -->
    <div class="relative flex justify-center">
      <div class="w-32 h-32 rounded-full border-4 border-dashed border-slate-300 flex items-center justify-center overflow-hidden bg-slate-50 transition-colors hover:border-primary/50">
        <img
          v-if="previewImage"
          :src="previewImage"
          class="w-full h-full object-cover"
          alt="Profile preview"
        />
        <span v-else class="material-symbols-outlined text-4xl text-slate-400">person</span>
      </div>

      <!-- Remove image button -->
      <Button
        v-if="previewImage"
        @click="removeImage"
        type="button"
        variant="danger"
        size="xs"
        class="absolute -top-2 -right-2 !p-0 w-6 h-6 rounded-full shadow-lg"
        title="Remove image"
      >
        <span class="material-symbols-outlined text-sm">close</span>
      </Button>

      <!-- Edit/Crop button -->
      <Button
        v-if="previewImage"
        @click="editImage"
        type="button"
        variant="primary"
        size="xs"
        class="absolute -bottom-2 -right-2 !p-0 w-6 h-6 rounded-full shadow-lg"
        title="Crop image"
      >
        <span class="material-symbols-outlined text-sm">crop</span>
      </Button>
    </div>

    <!-- Upload controls -->
    <div class="flex flex-col items-center space-y-2">
      <input
        ref="fileInput"
        type="file"
        @change="handleFileSelect"
        accept="image/jpeg,image/png,image/webp"
        class="hidden"
      />

      <Button
        @click="fileInput?.click()"
        variant="outline"
        :disabled="disabled"
      >
        <span class="material-symbols-outlined mr-2">upload</span>
        {{ previewImage ? 'Change Photo' : 'Upload Photo' }}
      </Button>

      <p class="text-xs text-slate-500 text-center">
        JPG, PNG, WebP up to 2MB<br>
        Will be cropped to square (1:1 ratio)
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Button from '../ui/Button.vue'

// Image cropper
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

interface Props {
  modelValue?: File | null
  existingImage?: string
  disabled?: boolean
}

interface Emits {
  (e: 'update:modelValue', value: File | null): void
  (e: 'image-selected', file: File): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  existingImage: '',
  disabled: false
})

const emit = defineEmits<Emits>()

// Refs
const fileInput = ref<HTMLInputElement | null>(null)
const cropperRef = ref<any>(null)

// State
const selectedFile = ref<File | null>(null)
const previewImage = ref<string>('')
const cropperImage = ref<string>('')
const showCropper = ref(false)

// Computed
const hasImage = computed(() => !!previewImage.value)

// Watch for existing image changes
watch(() => props.existingImage, (newImage) => {
  if (newImage && !selectedFile.value) {
    previewImage.value = newImage
  }
}, { immediate: true })

// Methods
const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) return

  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    alert('Please select a valid image file (JPG, PNG, or WebP)')
    return
  }

  // Validate file size (2MB max)
  const maxSize = 2 * 1024 * 1024 // 2MB in bytes
  if (file.size > maxSize) {
    alert('Please select an image smaller than 2MB')
    return
  }

  selectedFile.value = file

  // Create image for cropper
  const reader = new FileReader()
  reader.onload = (e) => {
    cropperImage.value = e.target?.result as string
    showCropper.value = true
  }
  reader.readAsDataURL(file)
}

const applyCrop = async () => {
  if (!cropperRef.value) return

  const { canvas } = cropperRef.value.getResult()
  if (!canvas) return

  // Convert canvas to blob
  canvas.toBlob((blob: Blob) => {
    if (!blob) return

    // Create file from blob
    const croppedFile = new File([blob], 'cropped-image.jpg', {
      type: 'image/jpeg',
      lastModified: Date.now()
    })

    selectedFile.value = croppedFile
    previewImage.value = canvas.toDataURL('image/jpeg')

    emit('update:modelValue', croppedFile)
    emit('image-selected', croppedFile)

    showCropper.value = false
    cropperImage.value = ''
  }, 'image/jpeg', 0.9)
}

const cancelCrop = () => {
  showCropper.value = false
  cropperImage.value = ''
  selectedFile.value = null

  // Reset file input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const editImage = () => {
  if (selectedFile.value && previewImage.value) {
    // Convert current preview back to cropper
    cropperImage.value = previewImage.value
    showCropper.value = true
  }
}

const removeImage = () => {
  selectedFile.value = null
  previewImage.value = ''
  cropperImage.value = ''
  showCropper.value = false

  emit('update:modelValue', null)

  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Expose methods for parent components
defineExpose({
  hasImage,
  removeImage
})
</script>

import { ref, readonly, computed } from 'vue'

export const useImageUpload = () => {
  // State
  const selectedFile = ref<File | null>(null)
  const previewImage = ref<string>('')
  const cropperImage = ref<string>('')
  const showCropper = ref(false)

  // Validation
  const validateImageFile = (file: File): boolean => {
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp']
    if (!allowedTypes.includes(file.type)) {
      throw new Error('Please select a valid image file (JPG, PNG, or WebP)')
    }

    // Validate file size (2MB max)
    const maxSize = 2 * 1024 * 1024 // 2MB in bytes
    if (file.size > maxSize) {
      throw new Error('Please select an image smaller than 2MB')
    }

    return true
  }

  // Handle file selection
  const handleFileSelect = (file: File) => {
    validateImageFile(file)
    selectedFile.value = file

    // Create image for cropper
    const reader = new FileReader()
    reader.onload = (e) => {
      cropperImage.value = e.target?.result as string
      showCropper.value = true
    }
    reader.readAsDataURL(file)
  }

  // Apply crop
  const applyCrop = async (cropperRef: any): Promise<File | null> => {
    if (!cropperRef) return null

    const { canvas } = cropperRef.getResult()
    if (!canvas) return null

    return new Promise((resolve) => {
      // Convert canvas to blob
      canvas.toBlob((blob: Blob) => {
        if (!blob) {
          resolve(null)
          return
        }

        // Create file from blob
        const croppedFile = new File([blob], 'cropped-image.jpg', {
          type: 'image/jpeg',
          lastModified: Date.now()
        })

        selectedFile.value = croppedFile
        previewImage.value = canvas.toDataURL('image/jpeg')

        resolve(croppedFile)
      }, 'image/jpeg', 0.9)
    })
  }

  // Cancel crop
  const cancelCrop = () => {
    showCropper.value = false
    cropperImage.value = ''
    selectedFile.value = null
  }

  // Edit existing image
  const editImage = () => {
    if (selectedFile.value && previewImage.value) {
      cropperImage.value = previewImage.value
      showCropper.value = true
    }
  }

  // Remove image
  const removeImage = () => {
    selectedFile.value = null
    previewImage.value = ''
    cropperImage.value = ''
    showCropper.value = false
  }

  // Reset
  const reset = () => {
    selectedFile.value = null
    previewImage.value = ''
    cropperImage.value = ''
    showCropper.value = false
  }

  return {
    // State
    selectedFile: readonly(selectedFile),
    previewImage: readonly(previewImage),
    cropperImage: readonly(cropperImage),
    showCropper: readonly(showCropper),

    // Computed
    hasImage: computed(() => !!previewImage.value),

    // Methods
    handleFileSelect,
    applyCrop,
    cancelCrop,
    editImage,
    removeImage,
    reset,
    validateImageFile
  }
}
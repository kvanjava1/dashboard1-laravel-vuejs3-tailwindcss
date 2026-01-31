<template>
  <!-- Form Actions Card -->
  <ContentBox>
    <ContentBoxBody>
      <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
        <Button
          variant="outline"
          class="w-full sm:w-auto"
          left-icon="close"
          @click="$emit('cancel')"
        >
          Cancel
        </Button>
        <Button
          variant="primary"
          class="w-full sm:w-auto"
          left-icon="save"
          :loading="loading"
          @click="$emit('submit')"
        >
          {{ isEdit ? 'Update Role' : 'Create Role' }}
        </Button>
      </div>
    </ContentBoxBody>
  </ContentBox>
</template>

<script setup lang="ts">
// Props
interface Props {
  loading?: boolean
  isEdit?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  isEdit: false
})

// Emits
defineEmits<{
  cancel: []
  submit: []
}>()

// Import components
import ContentBox from '../ui/ContentBox.vue'
import ContentBoxBody from '../ui/ContentBoxBody.vue'
import Button from '../ui/Button.vue'
</script>
<template>
  <ContentBox>
    <ContentBoxBody>
      <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
        <Button
          variant="outline"
          class="w-full sm:w-auto"
          left-icon="close"
          @click="$emit('cancel')"
          :disabled="loading"
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
          {{ isEdit ? 'Update User' : 'Create User' }}
        </Button>
      </div>
    </ContentBoxBody>
  </ContentBox>
</template>

<script setup lang="ts">
import Button from '../ui/Button.vue'
import ContentBox from '../ui/ContentBox.vue'
import ContentBoxBody from '../ui/ContentBoxBody.vue'

interface Props {
  loading?: boolean
  isEdit?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  isEdit: false
})

defineEmits<{
  cancel: []
  submit: []
}>()
</script>
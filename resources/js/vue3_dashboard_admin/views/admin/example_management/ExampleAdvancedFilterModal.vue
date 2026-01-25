<template>
  <BaseModal v-model="isOpen" size="md">
    <!-- Modal Header -->
    <template #header>
      <span class="material-symbols-outlined text-primary text-2xl">filter_list</span>
      <div>
        <h2 class="text-xl font-bold text-slate-800">Advanced Example Filter</h2>
        <p class="text-sm text-slate-500">Search and filter examples by multiple criteria</p>
      </div>
    </template>
    <!-- Modal Body -->
    <template #body>
      <div class="space-y-8">
        <!-- Basic Filters -->
        <div>
          <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">search</span>
            Search Criteria
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField v-model="filters.name" label="Name" type="text" placeholder="Enter example name" />
            <FormField v-model="filters.type" label="Type" type="select">
              <option value="">All Types</option>
              <option value="A">Type A</option>
              <option value="B">Type B</option>
              <option value="C">Type C</option>
            </FormField>
            <FormField v-model="filters.status" label="Status" type="select">
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </FormField>
          </div>
        </div>
        <!-- Date Range -->
        <div>
          <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">calendar_today</span>
            Date Range
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField v-model="filters.date_from" label="From Date" type="date" help="Created start date" />
            <FormField v-model="filters.date_to" label="To Date" type="date" help="Created end date" />
          </div>
        </div>
        <!-- Sort Options -->
        <div>
          <h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">sort</span>
            Sorting
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField v-model="filters.sort_by" label="Sort By" type="select">
              <option value="name">Name</option>
              <option value="type">Type</option>
              <option value="status">Status</option>
              <option value="created_at">Created Date</option>
            </FormField>
            <FormField v-model="filters.sort_order" label="Order" type="select">
              <option value="asc">Ascending</option>
              <option value="desc">Descending</option>
            </FormField>
          </div>
        </div>
      </div>
    </template>
    <!-- Modal Footer -->
    <template #footer>
      <button @click="resetFilters" class="w-full sm:w-auto px-6 py-2.5 rounded-full border border-border-light text-slate-700 text-sm font-semibold hover:bg-slate-100 transition-all duration-200">Reset Filters</button>
      <button @click="applyFilters" class="w-full sm:w-auto px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-bold hover:shadow-hard hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2">
        <span class="material-symbols-outlined text-[18px]">filter_list</span>
        Apply Filters
      </button>
    </template>
  </BaseModal>
</template>
<script setup lang="ts">
import { ref, reactive, watch, onUnmounted } from 'vue'
import BaseModal from '../../../components/ui/BaseModal.vue'
import FormField from '../../../components/ui/FormField.vue'

interface FilterOptions {
  name: string
  type: string
  status: string
  date_from: string
  date_to: string
  sort_by: string
  sort_order: string
}
interface Props {
  modelValue: boolean
  initialFilters?: Partial<FilterOptions>
}
interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'apply', filters: FilterOptions): void
  (e: 'reset'): void
}
const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  initialFilters: () => ({})
})
const emit = defineEmits<Emits>()
const isOpen = ref(false)
const filters = reactive<FilterOptions>({
  name: '',
  type: '',
  status: '',
  date_from: '',
  date_to: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  ...props.initialFilters
})
watch(() => props.modelValue, (newValue) => {
  isOpen.value = newValue
})
watch(isOpen, (newValue) => {
  emit('update:modelValue', newValue)
})
const closeModal = () => {
  isOpen.value = false
}
const applyFilters = () => {
  emit('apply', { ...filters })
  closeModal()
}
const resetFilters = () => {
  Object.assign(filters, {
    name: '',
    type: '',
    status: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc'
  })
  emit('reset')
}
const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeModal()
  }
}
document.addEventListener('keydown', handleKeydown)
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

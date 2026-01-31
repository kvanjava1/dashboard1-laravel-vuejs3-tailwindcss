<template>
  <AdminLayout>
    <PageHeader>
      <template #title>
        <PageHeaderTitle title="Example Management" />
      </template>
      <template #actions>
        <PageHeaderActions>
          <ActionButton icon="add" @click="openAddModal">Add New</ActionButton>
          <ActionDropdown>
            <ActionDropdownItem icon="filter_list" @click="openAdvancedFilter">Advanced Filter</ActionDropdownItem>
          </ActionDropdown>
        </PageHeaderActions>
      </template>
    </PageHeader>
    <ContentBox>
      <ContentBoxHeader>
        <template #title>
          <ContentBoxTitle title="Example List" subtitle="Manage examples" />
        </template>
      </ContentBoxHeader>
      <ContentBoxBody>
        <!-- Loading State -->
        <LoadingState v-if="loading" message="Loading examples..." />

        <!-- Error State -->
        <ErrorState
            v-else-if="error"
            :message="error"
            @retry="fetchExamples"
        />

        <!-- Active Filters Indicator -->
        <ActiveFiltersIndicator
            v-else
            :has-active-filters="hasActiveFilters"
            @reset="handleResetFilters"
        />

        <!-- Empty State -->
        <EmptyState
            v-if="!loading && !error && examples.length === 0"
            icon="inventory_2"
            message="No examples found"
            subtitle="Try adjusting your filters or add a new example"
        />

        <!-- Data Table -->
        <div v-else-if="!loading && !error && examples.length > 0">
          <SimpleUserTable>
          <SimpleUserTableHead>
            <SimpleUserTableHeadRow>
              <SimpleUserTableHeadCol>
                <div class="flex items-center gap-2">
                  <span class="text-base font-semibold text-slate-700">Name</span>
                  <span class="material-symbols-outlined text-slate-400 text-base">arrow_drop_down</span>
                </div>
              </SimpleUserTableHeadCol>
              <SimpleUserTableHeadCol>
                <span class="text-base font-semibold text-slate-700">Type</span>
              </SimpleUserTableHeadCol>
              <SimpleUserTableHeadCol>
                <span class="text-base font-semibold text-slate-700">Status</span>
              </SimpleUserTableHeadCol>
              <SimpleUserTableHeadCol>
                <span class="text-base font-semibold text-slate-700">Created At</span>
              </SimpleUserTableHeadCol>
              <SimpleUserTableHeadCol>
                <span class="text-base font-semibold text-slate-700">Updated At</span>
              </SimpleUserTableHeadCol>
              <SimpleUserTableHeadCol>
                <span class="text-base font-semibold text-slate-700">Actions</span>
              </SimpleUserTableHeadCol>
            </SimpleUserTableHeadRow>
          </SimpleUserTableHead>
          <SimpleUserTableBody>
            <SimpleUserTableBodyRow v-for="example in examples" :key="example.id">
              <SimpleUserTableBodyCol>
                <span class="text-base text-slate-800 font-medium">{{ example.name }}</span>
              </SimpleUserTableBodyCol>
              <SimpleUserTableBodyCol>
                <span class="text-sm text-slate-700">{{ example.type }}</span>
              </SimpleUserTableBodyCol>
              <SimpleUserTableBodyCol>
                <span class="text-sm text-slate-700">{{ example.status }}</span>
              </SimpleUserTableBodyCol>
              <SimpleUserTableBodyCol>
                <span class="text-sm text-slate-700">{{ example.created_at }}</span>
              </SimpleUserTableBodyCol>
              <SimpleUserTableBodyCol>
                <span class="text-sm text-slate-700">{{ example.updated_at }}</span>
              </SimpleUserTableBodyCol>
              <SimpleUserTableBodyCol>
                <CellActions
                  @edit="openEditModal(example)"
                  @delete="softDeleteExample(example)"
                  @view="openDetailModal(example)"
                  :show-delete="true"
                />
              </SimpleUserTableBodyCol>
            </SimpleUserTableBodyRow>
          </SimpleUserTableBody>
        </SimpleUserTable>
        </div>
        <Pagination
          :current-start="currentStart"
          :current-end="currentEnd"
          :total="pagination.total"
          :current-page="pagination.current_page"
          :total-pages="pagination.total_pages"
          :rows-per-page="pagination.per_page"
          @prev="prevPage"
          @next="nextPage"
          @goto="goToPage"
        />
      </ContentBoxBody>
    </ContentBox>
    <ExampleDetailModal v-model="showDetailModal" :example="selectedExample" />
    <ExampleAdvancedFilterModal
      v-model="showAdvancedFilter"
      :initial-filters="advancedFilters"
      @apply="handleApplyFilters"
      @reset="handleResetFilters"
    />
  </AdminLayout>
</template>
<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import ActionDropdown from '../../../components/ui/ActionDropdown.vue'
import ActionDropdownItem from '../../../components/ui/ActionDropdownItem.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import SimpleUserTable from '../../../components/ui/SimpleUserTable.vue'
import SimpleUserTableHead from '../../../components/ui/SimpleUserTableHead.vue'
import SimpleUserTableHeadRow from '../../../components/ui/SimpleUserTableHeadRow.vue'
import SimpleUserTableHeadCol from '../../../components/ui/SimpleUserTableHeadCol.vue'
import SimpleUserTableBody from '../../../components/ui/SimpleUserTableBody.vue'
import SimpleUserTableBodyRow from '../../../components/ui/SimpleUserTableBodyRow.vue'
import SimpleUserTableBodyCol from '../../../components/ui/SimpleUserTableBodyCol.vue'
import CellActions from '../../../components/ui/CellActions.vue'
import Pagination from '../../../components/ui/Pagination.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'
import ErrorState from '../../../components/ui/ErrorState.vue'
import ActiveFiltersIndicator from '../../../components/ui/ActiveFiltersIndicator.vue'
import EmptyState from '../../../components/ui/EmptyState.vue'
import ExampleEditModal from './ExampleEditModal.vue'
import ExampleDetailModal from './ExampleDetailModal.vue'
import ExampleAdvancedFilterModal from './ExampleAdvancedFilterModal.vue'
import router from '@/router/routes'
import { showConfirm, showToast } from '@/composables/useSweetAlert'

const examples = ref<any[]>([
  {
    id: 1,
    name: 'Example One',
    type: 'A',
    status: 'active',
    created_at: '2026-01-01',
    updated_at: '2026-01-10'
  },
  {
    id: 2,
    name: 'Example Two',
    type: 'B',
    status: 'inactive',
    created_at: '2026-01-02',
    updated_at: '2026-01-11'
  },
  {
    id: 3,
    name: 'Example Three',
    type: 'C',
    status: 'active',
    created_at: '2026-01-03',
    updated_at: '2026-01-12'
  },
  {
    id: 4,
    name: 'Example Four',
    type: 'A',
    status: 'inactive',
    created_at: '2026-01-04',
    updated_at: '2026-01-13'
  },
  {
    id: 5,
    name: 'Example Five',
    type: 'B',
    status: 'active',
    created_at: '2026-01-05',
    updated_at: '2026-01-14'
  }
])

// Dummy pagination for 5 records
const pagination = reactive({
  total: examples.value.length,
  total_pages: 1,
  current_page: 1,
  per_page: 10,
  from: 1,
  to: examples.value.length
})
const currentStart = computed(() => {
  if (examples.value.length === 0) return 0
  return (pagination.current_page - 1) * pagination.per_page + 1
})
const currentEnd = computed(() => {
  const end = pagination.current_page * pagination.per_page
  return Math.min(end, pagination.total)
})
const showAdvancedSearch = ref(false)
const advanced = reactive({
  type: '',
  status: ''
})
const showAdvancedFilter = ref(false)
const advancedFilters = reactive({
  name: '',
  type: '',
  status: '',
  date_from: '',
  date_to: '',
  sort_by: 'created_at',
  sort_order: 'desc'
})
const showEditModal = ref(false)
const showDetailModal = ref(false)
const selectedExample = ref<any>(null)

// UI State
const loading = ref(false)
const error = ref<string | null>(null)

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return advancedFilters.name.trim() !== '' ||
         advancedFilters.type !== '' ||
         advancedFilters.status !== '' ||
         advancedFilters.date_from !== '' ||
         advancedFilters.date_to !== '' ||
         advancedFilters.sort_by !== 'created_at' ||
         advancedFilters.sort_order !== 'desc'
})
function fetchExamples() {
  loading.value = true
  error.value = null

  try {
    // TODO: API call to backend for search & pagination, include advanced.type and advanced.status
    // For now, just simulate loading
    setTimeout(() => {
      loading.value = false
    }, 1000)
  } catch (err: any) {
    error.value = err.message || 'Failed to load examples'
    loading.value = false
  }
}
function openAddModal() {
  router.push({ name: 'admin-example-management-add' })
}
function openEditModal(example: any) {
  selectedExample.value = example
  showEditModal.value = true
}
function openDetailModal(example: any) {
  selectedExample.value = example
  showDetailModal.value = true
}
function softDeleteExample(example: any) {
  showConfirm({
    title: `Delete example "${example.name}"?`,
    text: 'This action cannot be undone.',
    icon: 'warning',
    confirmButtonText: 'Yes, delete',
    cancelButtonText: 'Cancel'
  }).then((confirmed: boolean) => {
    if (!confirmed) return
    // TODO: API call to backend for soft delete
    showToast({ icon: 'success', title: 'Example deleted successfully', timer: 0 })
    // Optionally remove from local list for demo
    examples.value = examples.value.filter(e => e.id !== example.id)
    pagination.total = examples.value.length
    pagination.to = examples.value.length
  })
}
function prevPage() {
  if (pagination.current_page > 1) {
    pagination.current_page--
    fetchExamples()
  }
}
function nextPage() {
  if (pagination.current_page < pagination.total_pages) {
    pagination.current_page++
    fetchExamples()
  }
}
function goToPage(page: number) {
  if (page >= 1 && page <= pagination.total_pages) {
    pagination.current_page = page
    fetchExamples()
  }
}
function toggleAdvancedSearch() {
  showAdvancedSearch.value = !showAdvancedSearch.value
}
function openAdvancedFilter() {
  showAdvancedFilter.value = true
}
function handleApplyFilters(filters: any) {
  Object.assign(advancedFilters, filters)
  pagination.current_page = 1
  fetchExamples()
}
function handleResetFilters() {
  Object.assign(advancedFilters, {
    name: '',
    type: '',
    status: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc'
  })
  pagination.current_page = 1
  fetchExamples()
}
onMounted(fetchExamples)
</script>
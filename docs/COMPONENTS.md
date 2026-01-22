# Vue 3 Dashboard Components Documentation

This document outlines all the Vue 3 components used in the admin dashboard, based on the HTML template structure.

## Component Architecture

The dashboard follows a component-based architecture with reusable UI components. Components are organized into logical groups:

- **Layout Components**: AdminLayout, ContentBox, PageHeader
- **UI Components**: Buttons, Dropdowns, Tables
- **Data Display Components**: User cells, Status indicators
- **Navigation Components**: Pagination, Actions

## Layout Components

### AdminLayout
The main layout wrapper for admin pages.

```vue
<template>
  <AdminLayout>
    <!-- Page content goes here -->
  </AdminLayout>
</template>
```

### PageHeader
Header section for pages with title and actions.

```vue
<PageHeader>
  <template #title>
    <PageHeaderTitle title="Page Title" />
  </template>
  <template #actions>
    <PageHeaderActions>
      <!-- Action buttons go here -->
    </PageHeaderActions>
  </template>
</PageHeader>
```

#### PageHeaderTitle
**Props:**
- `title` (String): The page title text

#### PageHeaderActions
Container for page-level action buttons.

### ContentBox
Main content container with header and body sections.

```vue
<ContentBox>
  <ContentBoxHeader>
    <template #title>
      <ContentBoxTitle title="Section Title" subtitle="Optional subtitle" />
    </template>
  </ContentBoxHeader>

  <ContentBoxBody>
    <!-- Content goes here -->
  </ContentBoxBody>
</ContentBox>
```

#### ContentBoxHeader
Header section for content boxes.

#### ContentBoxTitle
**Props:**
- `title` (String): Main title
- `subtitle` (String, optional): Subtitle text

#### ContentBoxBody
Body container for content box content.

## Action Components

### ActionButton
Primary action button with icon support.

```vue
<ActionButton icon="add">
  Button Text
</ActionButton>
```

**Props:**
- `icon` (String): Material Symbol icon name

### ActionDropdown
Dropdown menu for additional actions with mobile support.

```vue
<ActionDropdown>
  <ActionDropdownItem icon="download" @click="handleExport">
    Export CSV
  </ActionDropdownItem>
  <ActionDropdownItem icon="upload" @click="handleImport">
    Import Data
  </ActionDropdownItem>
</ActionDropdown>
```

**Features:**
- **Desktop**: Hover to open dropdown
- **Mobile**: Click to open/close dropdown
- **Auto-close**: Closes when clicking outside or selecting an item
- **Keyboard support**: ESC key to close

#### ActionDropdownItem
Individual dropdown menu items.

**Props:**
- `icon` (String): Material Symbol icon name

**Events:**
- `@click`: Emitted when item is clicked

## Table Components

### SimpleUserTable
Main table container for user data.

```vue
<SimpleUserTable>
  <SimpleUserTableHead>
    <!-- Table header -->
  </SimpleUserTableHead>
  <SimpleUserTableBody>
    <!-- Table body -->
  </SimpleUserTableBody>
</SimpleUserTable>
```

#### SimpleUserTableHead
Table header section.

#### SimpleUserTableHeadRow
Table header row.

#### SimpleUserTableHeadCol
Table header column.

#### SimpleUserTableBody
Table body section.

#### SimpleUserTableBodyRow
Table body row.

#### SimpleUserTableBodyCol
Table body column.

## User Cell Components

### UserCellUser
Displays user avatar and name information.

```vue
<UserCellUser :name="user.name" :email="user.email" :avatar="user.avatar" />
```

**Props:**
- `name` (String): User's full name
- `email` (String): User's email address
- `avatar` (String): Avatar image URL

### UserCellRole
Displays user role with appropriate styling.

```vue
<UserCellRole :role="user.role" />
```

**Props:**
- `role` (String): User role ('Administrator' | 'Editor' | 'Viewer')

### UserCellStatus
Displays user account status with indicator.

```vue
<UserCellStatus :status="user.status" />
```

**Props:**
- `status` (String): Account status ('Active' | 'Pending' | 'Inactive')

### UserCellActions
Action buttons for user management (edit, delete, view).

```vue
<UserCellActions @edit="handleEdit(user)" @delete="handleDelete(user)" @view="handleView(user)" />
```

**Events:**
- `@edit`: Triggered when edit button is clicked
- `@delete`: Triggered when delete button is clicked
- `@view`: Triggered when view button is clicked

## Navigation Components

### Pagination
Pagination component for data tables.

```vue
<Pagination
  :current-start="1"
  :current-end="10"
  :total="50"
  :current-page="1"
  :total-pages="5"
  :rows-per-page="10"
  @prev="prevPage"
  @next="nextPage"
  @goto="goToPage"
/>
```

**Props:**
- `current-start` (Number): Starting item number for current page
- `current-end` (Number): Ending item number for current page
- `total` (Number): Total number of items
- `current-page` (Number): Current page number
- `total-pages` (Number): Total number of pages
- `rows-per-page` (Number): Items per page

**Events:**
- `@prev`: Previous page button clicked
- `@next`: Next page button clicked
- `@goto`: Go to specific page

## Complete Example Usage

```vue
<template>
  <AdminLayout>
    <!-- Header Section -->
    <PageHeader>
      <template #title>
        <PageHeaderTitle title="Users" />
      </template>
      <template #actions>
        <PageHeaderActions>
          <ActionButton icon="add">
            Add New
          </ActionButton>
          <ActionDropdown>
            <ActionDropdownItem icon="download">
              Export CSV
            </ActionDropdownItem>
            <ActionDropdownItem icon="upload">
              Import Data
            </ActionDropdownItem>
          </ActionDropdown>
        </PageHeaderActions>
      </template>
    </PageHeader>

    <ContentBox>
      <!-- Card Header -->
      <ContentBoxHeader>
        <template #title>
          <ContentBoxTitle title="User Management" subtitle="Manage and review all registered users" />
        </template>
      </ContentBoxHeader>

      <!-- Data Table -->
      <ContentBoxBody>
        <SimpleUserTable>
          <SimpleUserTableHead>
            <SimpleUserTableHeadRow>
              <SimpleUserTableHeadCol>User</SimpleUserTableHeadCol>
              <SimpleUserTableHeadCol>Role</SimpleUserTableHeadCol>
              <SimpleUserTableHeadCol>Status</SimpleUserTableHeadCol>
              <SimpleUserTableHeadCol>Actions</SimpleUserTableHeadCol>
            </SimpleUserTableHeadRow>
          </SimpleUserTableHead>

          <SimpleUserTableBody>
            <SimpleUserTableBodyRow v-for="user in users" :key="user.id">
              <SimpleUserTableBodyCol>
                <UserCellUser :name="user.name" :email="user.email" :avatar="user.avatar" />
              </SimpleUserTableBodyCol>
              <SimpleUserTableBodyCol>
                <UserCellRole :role="user.role" />
              </SimpleUserTableBodyCol>
              <SimpleUserTableBodyCol>
                <UserCellStatus :status="user.status" />
              </SimpleUserTableBodyCol>
              <SimpleUserTableBodyCol>
                <UserCellActions @edit="handleEdit(user)" @delete="handleDelete(user)" @view="handleView(user)" />
              </SimpleUserTableBodyCol>
            </SimpleUserTableBodyRow>
          </SimpleUserTableBody>
        </SimpleUserTable>

        <!-- Pagination -->
        <Pagination
          :current-start="1"
          :current-end="10"
          :total="50"
          :current-page="1"
          :total-pages="5"
          :rows-per-page="10"
          @prev="prevPage"
          @next="nextPage"
          @goto="goToPage"
        />
      </ContentBoxBody>
    </ContentBox>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
// Import all components as shown in Index.vue
// ... component imports

interface User {
  id: number
  name: string
  email: string
  avatar: string
  role: 'Administrator' | 'Editor' | 'Viewer'
  status: 'Active' | 'Pending' | 'Inactive'
}

const users = ref<User[]>([])

// Event handlers
const handleEdit = (user: User) => { /* ... */ }
const handleDelete = (user: User) => { /* ... */ }
const handleView = (user: User) => { /* ... */ }
const prevPage = () => { /* ... */ }
const nextPage = () => { /* ... */ }
const goToPage = (page: number) => { /* ... */ }
</script>
```

## Page Components

### NoPermissions Page Component

A secure access denied page shown when users don't have sufficient permissions to access restricted routes.

```vue
<template>
  <!-- Generic access denied message - no sensitive permission details shown -->
  <NoPermissions />
</template>
```

**Security Features:**
- **No permission disclosure**: Does not reveal specific permission or role requirements
- **Generic messaging**: Prevents information leakage about system permissions
- **User-friendly design**: Clear error messaging with visual indicators
- **Navigation options**: Go back or return to dashboard
- **Contact information**: Direct link to contact administrator

**Route Configuration:**
```typescript
{
  path: '/no-permissions',
  name: 'no-permissions',
  component: NoPermissions,
  meta: {
    title: 'Access Denied'
  }
}
```

**Router Guard Integration:**
```typescript
// In router guard - redirects without exposing permission details
if (!hasPermission) {
  next({
    path: '/no-permissions',
    query: {
      redirect: to.fullPath  // Optional: for potential future redirect after auth
    }
  })
}
```

### UserAdvancedFilterModal Component

A modal dialog for advanced user filtering with multiple search criteria.

```vue
<template>
  <UserAdvancedFilterModal
    v-model="showAdvancedFilter"
    :initial-filters="currentFilters"
    @apply="handleApplyFilters"
    @reset="handleResetFilters"
  />
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import UserAdvancedFilterModal from './UserAdvancedFilterModal.vue'

const showAdvancedFilter = ref(false)

const currentFilters = reactive({
  search: '',
  name: '',
  email: '',
  role: '',
  status: '',
  date_from: '',
  date_to: '',
  sort_by: 'created_at',
  sort_order: 'desc'
})

const handleApplyFilters = (filters: any) => {
  Object.assign(currentFilters, filters)
  console.log('Applied filters:', filters)
  // Implement filtering logic
}

const handleResetFilters = () => {
  // Reset filters logic
}
</script>
```

**Features:**
- **Search Fields**: Basic search, name, email, role, status
- **Date Range**: Filter by registration date range
- **Sorting**: Sort by various fields in ascending/descending order
- **Modal Controls**: Apply filters, reset filters, close modal
- **Keyboard Support**: ESC key to close modal
- **Responsive Design**: Works on mobile and desktop

### UserAdd Page Component

A complete form page for adding new users with validation and proper form handling.

```vue
<template>
  <AdminLayout>
    <!-- Header Section -->
    <PageHeader>
      <template #title>
        <PageHeaderTitle title="Add New User" />
      </template>
      <template #actions>
        <PageHeaderActions>
          <ActionButton icon="arrow_back" @click="goBack">
            Back
          </ActionButton>
        </PageHeaderActions>
      </template>
    </PageHeader>

    <!-- Form Container -->
    <div class="space-y-6">
      <!-- Personal Information Card -->
      <ContentBox>
        <ContentBoxHeader>
          <template #title>
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-xl">person</span>
              <ContentBoxTitle title="Personal Information" />
            </div>
          </template>
        </ContentBoxHeader>
        <ContentBoxBody>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                First Name <span class="text-danger">*</span>
              </label>
              <input
                v-model="form.first_name"
                type="text"
                placeholder="John"
                required
                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Last Name <span class="text-danger">*</span>
              </label>
              <input
                v-model="form.last_name"
                type="text"
                placeholder="Doe"
                required
                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              />
            </div>
          </div>
        </ContentBoxBody>
      </ContentBox>

      <!-- Additional form cards for Contact, Security, Role & Permissions, etc. -->
      <!-- ... -->

      <!-- Form Actions -->
      <ContentBox>
        <ContentBoxBody>
          <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
            <button @click="goBack" class="...">Cancel</button>
            <button @click="handleSubmit" :disabled="isSubmitting" class="...">
              <span v-if="isSubmitting" class="animate-spin">refresh</span>
              {{ isSubmitting ? 'Creating...' : 'Create User' }}
            </button>
          </div>
        </ContentBoxBody>
      </ContentBox>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
// ... component imports

interface UserForm {
  first_name: string
  last_name: string
  email: string
  phone: string
  password: string
  password_confirmation: string
  role: string
  status: string
  bio: string
}

const router = useRouter()
const isSubmitting = ref(false)

const form = reactive<UserForm>({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  role: '',
  status: 'active',
  bio: ''
})

const goBack = () => {
  router.push('/admin/users')
}

const validateForm = (): boolean => {
  // Validation logic
  return true
}

const handleSubmit = async () => {
  if (!validateForm()) return

  isSubmitting.value = true
  try {
    // API call logic
    router.push('/admin/users')
  } catch (error) {
    console.error('Error:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>
```

## Component Organization

Components are organized in the following directory structure:

```
resources/js/vue3_dashboard_admin/components/
├── ui/
│   ├── AdminLayout.vue
│   ├── PageHeader.vue
│   ├── PageHeaderTitle.vue
│   ├── PageHeaderActions.vue
│   ├── ActionButton.vue
│   ├── ActionDropdown.vue
│   ├── ActionDropdownItem.vue
│   ├── ContentBox.vue
│   ├── ContentBoxHeader.vue
│   ├── ContentBoxTitle.vue
│   ├── ContentBoxBody.vue
│   ├── SimpleUserTable.vue
│   ├── SimpleUserTableHead.vue
│   ├── SimpleUserTableHeadRow.vue
│   ├── SimpleUserTableHeadCol.vue
│   ├── SimpleUserTableBody.vue
│   ├── SimpleUserTableBodyRow.vue
│   ├── SimpleUserTableBodyCol.vue
│   ├── UserCellUser.vue
│   ├── UserCellRole.vue
│   ├── UserCellStatus.vue
│   ├── UserCellActions.vue
│   └── Pagination.vue
```

## Styling Guidelines

All components follow these design principles:

- **Colors**: Primary (#3b82f6), Secondary (#8b5cf6), Success (#10b981), Warning (#f59e0b), Danger (#ef4444)
- **Typography**: Manrope font family
- **Icons**: Material Symbols Outlined
- **Spacing**: Consistent padding and margins using Tailwind utilities
- **Borders**: Rounded corners (1rem default), subtle shadows
- **Responsive**: Mobile-first approach with responsive breakpoints

## Notes

- All components are designed to work with Vue 3 Composition API
- TypeScript interfaces are used for type safety
- Components follow the single responsibility principle
- Event handling uses Vue's event emission system
- Styling uses Tailwind CSS with custom design tokens
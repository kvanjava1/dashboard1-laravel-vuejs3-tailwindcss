<template>
  <AdminLayout>
    <!-- Header Section -->
    <PageHeader>
      <template #title>
        <PageHeaderTitle title="Add New Example" />
      </template>
      <template #actions>
        <PageHeaderActions>
          <ActionButton icon="arrow_back" @click="goBack">Back</ActionButton>
        </PageHeaderActions>
      </template>
    </PageHeader>
    <div class="space-y-6">
      <!-- Example Information Card -->
      <ContentBox>
        <ContentBoxHeader>
          <template #title>
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-xl">info</span>
              <ContentBoxTitle title="Example Information" />
            </div>
          </template>
        </ContentBoxHeader>
        <ContentBoxBody>
          <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Name <span class="text-danger">*</span></label>
              <input v-model="form.name" type="text" placeholder="Example Name" required class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
            </div>
            <!-- Description -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
              <input v-model="form.description" type="text" placeholder="Description" class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
            </div>
            <!-- Type -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Type</label>
              <select v-model="form.type" class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer">
                <option value="">Select type</option>
                <option value="A">Type A</option>
                <option value="B">Type B</option>
                <option value="C">Type C</option>
              </select>
            </div>
            <!-- Status -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <!-- Actions -->
            <div class="md:col-span-2 flex justify-end mt-8">
              <ActionButton type="submit" icon="add" class="btn btn-primary">Add Example</ActionButton>
            </div>
          </form>
        </ContentBoxBody>
      </ContentBox>
    </div>
  </AdminLayout>
</template>
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'

const router = useRouter()
const form = ref({
  name: '',
  description: '',
  type: '',
  status: 'active'
})
async function submitForm() {
  // TODO: API call to add example
  // On success:
  router.push({ name: 'admin-example-management' })
}
function goBack() {
  router.push({ name: 'admin-example-management' })
}
</script>

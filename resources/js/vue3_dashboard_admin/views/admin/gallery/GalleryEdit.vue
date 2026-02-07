<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle :title="`Edit Gallery: ${gallery?.title || 'Loading...'}`" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="secondary" icon="arrow_back" @click="goBack">
                        Back to Galleries
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Loading State -->
        <LoadingState v-if="loading" message="Loading gallery data..." />

        <!-- Content Box -->
        <ContentBox v-else>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="Gallery Information" subtitle="Update the details of your gallery" />
                </template>
            </ContentBoxHeader>

            <!-- Form -->
            <ContentBoxBody>
                <form @submit.prevent="updateGallery" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Gallery Title *</label>
                            <input v-model="form.title" type="text"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                placeholder="Enter gallery title" required />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                            <select v-model="form.category"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer">
                                <option value="">Select category</option>
                                <option value="Photography">Photography</option>
                                <option value="Architecture">Architecture</option>
                                <option value="Food">Food</option>
                                <option value="Travel">Travel</option>
                                <option value="Art">Art</option>
                                <option value="Wildlife">Wildlife</option>
                                <option value="Events">Events</option>
                                <option value="Products">Products</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                        <textarea v-model="form.description" rows="4"
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            placeholder="Describe your gallery..."></textarea>
                    </div>

                    <!-- Current Cover Image -->
                    <div v-if="gallery?.coverImage">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Current Cover Image</label>
                        <div class="border-2 border-gray-200 rounded-lg p-4 bg-gray-50">
                            <img :src="gallery.coverImage" :alt="gallery.title"
                                class="max-w-md max-h-48 mx-auto rounded-lg shadow-md" />
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                            <select v-model="form.status"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Item Count</label>
                            <input v-model.number="form.itemCount" type="number" min="0"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                readonly />
                            <p class="text-xs text-gray-500 mt-1">This is automatically calculated based on gallery
                                items</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                        <ActionButton variant="secondary" @click="goBack">
                            Cancel
                        </ActionButton>
                        <ActionButton variant="primary" type="submit" :disabled="saving">
                            <span v-if="saving"
                                class="material-symbols-outlined animate-spin text-sm mr-2">refresh</span>
                            Save Changes
                        </ActionButton>
                    </div>
                </form>
            </ContentBoxBody>
        </ContentBox>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { showToast } from '@/composables/useSweetAlert'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxHeader from '../../../components/ui/ContentBoxHeader.vue'
import ContentBoxTitle from '../../../components/ui/ContentBoxTitle.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import LoadingState from '../../../components/ui/LoadingState.vue'

const router = useRouter()
const route = useRoute()

// State
const loading = ref(true)
const saving = ref(false)
const gallery = ref<any>(null)

// Form data
const form = reactive({
    title: '',
    description: '',
    category: '',
    status: 'active',
    itemCount: 0
})

// Dummy galleries data (same as Index.vue)
const dummyGalleries = [
    {
        id: 1,
        title: 'Nature Photography Collection',
        description: 'Beautiful landscapes and natural scenes captured around the world. From majestic mountains to serene beaches.',
        coverImage: 'https://picsum.photos/seed/nature1/400/300',
        category: 'Photography',
        itemCount: 24,
        status: 'active',
        createdAt: '2024-01-15'
    },
    {
        id: 2,
        title: 'Urban Architecture',
        description: 'Modern and classic architectural designs from city landscapes. Showcasing the beauty of urban structures.',
        coverImage: 'https://picsum.photos/seed/arch1/400/300',
        category: 'Architecture',
        itemCount: 18,
        status: 'active',
        createdAt: '2024-01-20'
    },
    {
        id: 3,
        title: 'Food & Cuisine Masterpieces',
        description: 'Delicious food photography showcasing culinary arts from around the globe.',
        coverImage: 'https://picsum.photos/seed/food1/400/300',
        category: 'Food',
        itemCount: 32,
        status: 'active',
        createdAt: '2024-02-01'
    },
    {
        id: 4,
        title: 'Travel Adventures 2024',
        description: 'Memorable moments from travel destinations worldwide. Capturing the essence of different cultures.',
        coverImage: 'https://picsum.photos/seed/travel1/400/300',
        category: 'Travel',
        itemCount: 45,
        status: 'active',
        createdAt: '2024-02-10'
    },
    {
        id: 5,
        title: 'Contemporary Art Collection',
        description: 'Contemporary and traditional art pieces from various artists. A curated selection of modern masterpieces.',
        coverImage: 'https://picsum.photos/seed/art1/400/300',
        category: 'Art',
        itemCount: 16,
        status: 'inactive',
        createdAt: '2024-01-05'
    }
]

// Methods
const fetchGallery = async () => {
    loading.value = true

    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 500))

        const galleryId = parseInt(route.params.id as string)
        const foundGallery = dummyGalleries.find(g => g.id === galleryId)

        if (foundGallery) {
            gallery.value = foundGallery
            // Populate form
            form.title = foundGallery.title
            form.description = foundGallery.description
            form.category = foundGallery.category
            form.status = foundGallery.status
            form.itemCount = foundGallery.itemCount
        } else {
            await showToast({
                icon: 'error',
                title: 'Gallery not found',
                text: 'The requested gallery could not be found.'
            })
            goBack()
        }
    } catch (error) {
        console.error('Error fetching gallery:', error)
        await showToast({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load gallery data.'
        })
    } finally {
        loading.value = false
    }
}

const updateGallery = async () => {
    if (!form.title.trim()) {
        await showToast({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Gallery title is required.'
        })
        return
    }

    saving.value = true

    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000))

        console.log('Updating gallery:', {
            id: gallery.value.id,
            ...form
        })

        await showToast({
            icon: 'success',
            title: 'Success!',
            text: 'Gallery updated successfully.',
            timer: 2000
        })

        // Redirect back to galleries
        router.push({ name: 'gallery_management.index' })

    } catch (error) {
        console.error('Error updating gallery:', error)
        await showToast({
            icon: 'error',
            title: 'Error',
            text: 'Failed to update gallery. Please try again.'
        })
    } finally {
        saving.value = false
    }
}

const goBack = () => {
    router.push({ name: 'gallery_management.index' })
}

onMounted(() => {
    fetchGallery()
})
</script>

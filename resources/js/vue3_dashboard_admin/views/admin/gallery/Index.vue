<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Gallery" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton variant="primary" icon="add" @click="goToCreateGallery">
                        Add Gallery
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Content Box -->
        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle title="Galleries" subtitle="Manage your image galleries and collections" />
                </template>
            </ContentBoxHeader>

            <!-- Gallery Grid -->
            <ContentBoxBody>
                <!-- Gallery Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="gallery in galleries"
                        :key="gallery.id"
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 border border-gray-200"
                    >
                        <!-- Gallery Image -->
                        <div class="aspect-video bg-gray-200 relative">
                            <img
                                :src="gallery.coverImage"
                                :alt="gallery.title"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute top-2 right-2">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        gallery.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ gallery.status }}
                                </span>
                            </div>
                        </div>

                        <!-- Gallery Info -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ gallery.title }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ gallery.description }}</p>

                            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span>{{ gallery.category }}</span>
                                <span>{{ gallery.itemCount }} items</span>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <ActionButton
                                    variant="secondary"
                                    size="sm"
                                    icon="visibility"
                                    @click="viewGallery(gallery)"
                                >
                                    View
                                </ActionButton>
                                <ActionButton
                                    variant="secondary"
                                    size="sm"
                                    icon="edit"
                                    @click="editGallery(gallery)"
                                >
                                    Edit
                                </ActionButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="galleries.length === 0"
                    icon="photo_library"
                    message="No Galleries Found"
                    subtitle="Get started by creating your first gallery to showcase your images and media."
                />
            </ContentBoxBody>
        </ContentBox>
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
import EmptyState from '../../../components/ui/EmptyState.vue'

// Router
const router = useRouter()

// Dummy data
const galleries = ref([
    {
        id: 1,
        title: 'Nature Photography',
        description: 'Beautiful landscapes and natural scenes captured around the world.',
        coverImage: 'https://picsum.photos/400/300?random=1',
        category: 'Photography',
        itemCount: 24,
        status: 'active'
    },
    {
        id: 2,
        title: 'Urban Architecture',
        description: 'Modern and classic architectural designs from city landscapes.',
        coverImage: 'https://picsum.photos/400/300?random=2',
        category: 'Architecture',
        itemCount: 18,
        status: 'active'
    },
    {
        id: 3,
        title: 'Food & Cuisine',
        description: 'Delicious food photography showcasing culinary arts.',
        coverImage: 'https://picsum.photos/400/300?random=3',
        category: 'Food',
        itemCount: 32,
        status: 'active'
    },
    {
        id: 4,
        title: 'Travel Adventures',
        description: 'Memorable moments from travel destinations worldwide.',
        coverImage: 'https://picsum.photos/400/300?random=4',
        category: 'Travel',
        itemCount: 45,
        status: 'active'
    },
    {
        id: 5,
        title: 'Art Collection',
        description: 'Contemporary and traditional art pieces from various artists.',
        coverImage: 'https://picsum.photos/400/300?random=5',
        category: 'Art',
        itemCount: 16,
        status: 'inactive'
    },
    {
        id: 6,
        title: 'Wildlife',
        description: 'Amazing wildlife photography capturing animals in their natural habitat.',
        coverImage: 'https://picsum.photos/400/300?random=6',
        category: 'Wildlife',
        itemCount: 28,
        status: 'active'
    }
])

// Methods
const goToCreateGallery = () => {
    router.push('/gallery_management/create')
}

const viewGallery = (gallery: any) => {
    console.log('View gallery:', gallery)
    // TODO: Implement view gallery functionality
}

const editGallery = (gallery: any) => {
    console.log('Edit gallery:', gallery)
    // TODO: Implement edit gallery functionality
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 2;
}
</style>

<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Image" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton
                        variant="primary"
                        icon="add"
                        @click="goToCreateImage"
                    >
                        Add Image
                    </ActionButton>
                    <ActionDropdown>
                        <ActionDropdownItem
                            icon="filter_list"
                            @click="showAdvancedFilter = true"
                        >
                            Advanced Filter
                        </ActionDropdownItem>
                    </ActionDropdown>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <!-- Content Box -->
        <ContentBox>
            <!-- Card Header -->
            <ContentBoxHeader>
                <template #title>
                    <ContentBoxTitle
                        title="Images"
                        subtitle="Manage and organize your image files"
                    />
                </template>
            </ContentBoxHeader>

            <!-- Image Grid -->
            <ContentBoxBody>
                <!-- Filters -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <!-- Search Bar -->
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <input
                                    v-model="currentFilters.search"
                                    type="text"
                                    placeholder="Search images..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    @keydown.enter="loadImages"
                                />
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"
                                >
                                    search
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                >
                    <div
                        v-for="image in images"
                        :key="image.id"
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 border border-gray-200 group cursor-pointer"
                        @click="viewImage(image)"
                    >
                        <!-- Image -->
                        <div class="aspect-video bg-gray-200 relative">
                            <img
                                :src="image.url"
                                :alt="image.altText || image.name"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute top-2 right-2">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-black bg-opacity-50 text-white"
                                >
                                    {{ image.size }}
                                </span>
                            </div>
                        </div>

                        <!-- Image Info -->
                        <div class="p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">
                                {{ image.name }}
                            </h4>
                            <p class="text-sm text-gray-600 mb-2">
                                {{ image.gallery || "No gallery" }}
                            </p>
                            <div
                                class="flex items-center justify-between text-xs text-gray-500"
                            >
                                <span>{{ image.uploadDate }}</span>
                                <div class="flex gap-2">
                                    <Button
                                        @click.stop="editImage(image)"
                                        variant="outline"
                                        size="sm"
                                        left-icon="edit"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        @click.stop="deleteImage(image)"
                                        variant="danger"
                                        size="sm"
                                        left-icon="delete"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <Pagination
                    v-if="totalPages > 1"
                    :current-start="currentStart"
                    :current-end="currentEnd"
                    :total="serverTotal"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :rows-per-page="itemsPerPage"
                    @prev="prevPage"
                    @next="nextPage"
                    @goto="goToPage"
                />

                <!-- Empty State -->
                <EmptyState
                    v-if="images.length === 0"
                    icon="image"
                    message="No Images Found"
                    subtitle="Upload your first image to get started."
                />
            </ContentBoxBody>
        </ContentBox>

        <!-- Image View Modal -->
        <BaseModal v-model="showViewModal" title="View Image" size="xl">
            <div v-if="selectedImage" class="text-center">
                <img
                    :src="selectedImage.url"
                    :alt="selectedImage.altText || selectedImage.name"
                    class="max-w-full max-h-96 mx-auto rounded-lg shadow-lg"
                />
                <div class="mt-4 space-y-2">
                    <h3 class="text-lg font-semibold">
                        {{ selectedImage.name }}
                    </h3>
                    <p class="text-sm text-gray-600">
                        Gallery: {{ selectedImage.gallery || "No gallery" }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Uploaded: {{ selectedImage.uploadDate }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Size: {{ selectedImage.size }}
                    </p>
                </div>
            </div>
        </BaseModal>

        <!-- Advanced Filter Modal -->
        <ImageAdvancedFilterModal
            v-model="showAdvancedFilter"
            :initial-filters="currentFilters"
            :categories="categories"
            :galleries="galleries"
            @apply="handleApplyFilters"
            @reset="handleResetFilters"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import AdminLayout from "../../../layouts/AdminLayout.vue";
import PageHeader from "../../../components/ui/PageHeader.vue";
import PageHeaderTitle from "../../../components/ui/PageHeaderTitle.vue";
import PageHeaderActions from "../../../components/ui/PageHeaderActions.vue";
import ContentBox from "../../../components/ui/ContentBox.vue";
import ContentBoxHeader from "../../../components/ui/ContentBoxHeader.vue";
import ContentBoxTitle from "../../../components/ui/ContentBoxTitle.vue";
import ContentBoxBody from "../../../components/ui/ContentBoxBody.vue";
import ActionButton from "../../../components/ui/ActionButton.vue";
import Button from "../../../components/ui/Button.vue";
import ActionDropdown from "../../../components/ui/ActionDropdown.vue";
import ActionDropdownItem from "../../../components/ui/ActionDropdownItem.vue";
import EmptyState from "../../../components/ui/EmptyState.vue";
import BaseModal from "../../../components/ui/BaseModal.vue";
import ImageAdvancedFilterModal from "../../../components/image/ImageAdvancedFilterModal.vue";
import Pagination from "../../../components/ui/Pagination.vue";
import { useMediaData } from "@/composables/image/useMediaData";
import { useGalleryData } from "@/composables/gallery/useGalleryData";
import { useCategoryData } from "@/composables/category/useCategoryData";
import { showConfirm, showToast } from "@/composables/useSweetAlert";

const router = useRouter();
// Reactive data
const showViewModal = ref(false);
const showAdvancedFilter = ref(false);
const selectedImage = ref<any>(null);

const { fetchMedia, deleteMedia } = useMediaData();
const { fetchGalleries } = useGalleryData();
const { fetchCategoryOptions } = useCategoryData();

// Current filters
const currentFilters = reactive({
    search: "",
    category: "",
    gallery: "",
    date_from: "",
    date_to: "",
    file_type: "",
    size_range: "",
});

const categories = ref<any[]>([]);
const galleries = ref<any[]>([]);
const images = ref<any[]>([]);

const currentPage = ref(1);
const itemsPerPage = ref(12);
const serverTotal = ref(0);
const serverPerPage = ref(itemsPerPage.value);
const serverTotalPages = ref(1);

// Computed
const totalPages = computed(() => serverTotalPages.value);
const currentStart = computed(() => {
    if (serverTotal.value === 0) return 0;
    return (currentPage.value - 1) * serverPerPage.value + 1;
});
const currentEnd = computed(() =>
    Math.min(currentPage.value * serverPerPage.value, serverTotal.value),
);

// Methods
const handleApplyFilters = (filters: typeof currentFilters) => {
    Object.assign(currentFilters, filters);
    currentPage.value = 1;
    loadImages();
};

const handleResetFilters = () => {
    Object.assign(currentFilters, {
        search: "",
        category: "",
        gallery: "",
        date_from: "",
        date_to: "",
        file_type: "",
        size_range: "",
    });
    currentPage.value = 1;
    loadImages();
};

const goToCreateImage = () => {
    router.push({ name: "image_management.create" });
};

const formatSize = (bytes: number) => {
    if (!bytes && bytes !== 0) return "";
    const mb = bytes / (1024 * 1024);
    return `${mb.toFixed(mb < 1 ? 2 : 1)} MB`;
};

const loadImages = async () => {
    const params: Record<string, any> = {
        page: currentPage.value,
        per_page: itemsPerPage.value,
    };
    if (currentFilters.search) params.search = currentFilters.search;
    if (currentFilters.gallery) params.gallery_id = currentFilters.gallery;
    if (currentFilters.category) params.category_id = currentFilters.category;
    if (currentFilters.date_from) params.date_from = currentFilters.date_from;
    if (currentFilters.date_to) params.date_to = currentFilters.date_to;
    if (currentFilters.file_type) params.file_type = currentFilters.file_type;
    if (currentFilters.size_range)
        params.size_range = currentFilters.size_range;

    try {
        const result = await fetchMedia(params);

        serverTotal.value = result.total || 0;
        serverPerPage.value = result.per_page || itemsPerPage.value;
        serverTotalPages.value = result.total_pages || 1;

        images.value = (result.media || []).map((m: any) => ({
            id: m.id,
            name: m.name,
            url: m.url,
            altText: m.alt_text || "",
            gallery: m.gallery?.title || "",
            galleryId: m.gallery_id || null,
            uploadDate: m.uploaded_at ? String(m.uploaded_at).slice(0, 10) : "",
            size: formatSize(m.size || 0),
        }));
    } catch (err: any) {
        await showToast({
            icon: "error",
            title: "Error",
            text: err.message || "Failed to load images",
        });
    }
};

const viewImage = (image: any) => {
    selectedImage.value = image;
    showViewModal.value = true;
};

const editImage = (image: any) => {
    router.push({ name: "image_management.edit", params: { id: image.id } });
};

const deleteImage = (image: any) => {
    handleDelete(image);
};

const handleDelete = async (image: any) => {
    const confirmed = await showConfirm({
        title: "Delete Image?",
        text: `Are you sure you want to delete "${image.name}"? This action cannot be undone.`,
        icon: "warning",
        confirmButtonText: "Yes, delete it",
        cancelButtonText: "Cancel",
    });

    if (!confirmed) return;

    try {
        await deleteMedia(image.id);
        await showToast({
            icon: "success",
            title: "Deleted!",
            text: "Image deleted successfully.",
            timer: 1500,
        });
        await loadImages();
    } catch (err: any) {
        await showToast({
            icon: "error",
            title: "Error",
            text: err.message || "Failed to delete image",
        });
    }
};

const prevPage = async () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        await loadImages();
    }
};

const nextPage = async () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        await loadImages();
    }
};

const goToPage = async (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        await loadImages();
    }
};

onMounted(async () => {
    categories.value = await fetchCategoryOptions({ type: "gallery" });
    const galleryResult = await fetchGalleries({ per_page: 200 });
    galleries.value = (galleryResult?.galleries || []).map((g: any) => ({
        id: g.id,
        title: g.title,
        categoryId: g.category_id || null,
    }));
    await loadImages();
});
</script>

<style scoped></style>

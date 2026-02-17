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
                        <!-- Search Bar (gallery-style quick search) -->
                        <div class="flex-1 max-w-md">
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">search</span>
                                </div>
                                <input
                                    v-model="searchInput"
                                    type="text"
                                    placeholder="Search images by name, gallery, or description..."
                                    class="block w-full pl-11 pr-4 py-3 bg-white border border-border-light text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary/50 transition-all shadow-inner-light"
                                    :style="{ borderRadius: 'var(--radius-input)' }"
                                    @keydown.enter="handleSearch"
                                />
                            </div>

                            <div class="mt-4">
                                <ActiveFiltersIndicator :has-active-filters="hasActiveFilters" @reset="handleResetFilters" />
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
                        <div class="aspect-video bg-gray-200 relative overflow-hidden">
                            <img
                                :src="image.url"
                                :alt="image.altText || image.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300 will-change-transform"
                            />
                            <div class="absolute top-2 right-2">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-black bg-opacity-50 text-white"
                                >
                                    {{ image.size }}
                                </span>
                            </div>

                            <!-- Hover overlay (matches gallery behavior) -->
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <Button variant="primary" size="sm" left-icon="visibility" @click.stop="viewImage(image)" class="transform scale-90 group-hover:scale-100 transition-transform">
                                    View
                                </Button>
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

        <!-- Image Quick View (gallery-style) -->
        <ImageDetailModal v-model="showViewModal" :image="selectedImage" @deleted="loadImages" />

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
import ImageAdvancedFilterModal from "@/components/image/ImageAdvancedFilterModal.vue";
import ImageDetailModal from "@/components/image/ImageDetailModal.vue";
import Pagination from "../../../components/ui/Pagination.vue";
import ActiveFiltersIndicator from "../../../components/ui/ActiveFiltersIndicator.vue";
import { useMediaData } from "@/composables/image/useMediaData";
import { useGalleryData } from "@/composables/gallery/useGalleryData";
import { useCategoryData } from "@/composables/category/useCategoryData";
import { showConfirm, showToast } from "@/composables/useSweetAlert";

const router = useRouter();
// Reactive data
const showViewModal = ref(false);
const showAdvancedFilter = ref(false);
const selectedImage = ref<any>(null);

const { fetchMedia, fetchMediaById, deleteMedia } = useMediaData();
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

// Quick-search input (separate from currentFilters.search until user triggers search)
const searchInput = ref(currentFilters.search);

const hasActiveFilters = computed(() => {
    return (
        (currentFilters.search || '').toString().trim() !== '' ||
        currentFilters.category !== '' ||
        currentFilters.gallery !== '' ||
        currentFilters.date_from !== '' ||
        currentFilters.date_to !== '' ||
        currentFilters.file_type !== '' ||
        currentFilters.size_range !== ''
    );
});

const handleSearch = async () => {
    currentFilters.search = (searchInput.value || '').toString();
    currentPage.value = 1;
    await loadImages();
};

// currentFilters declared earlier (moved up)
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
    searchInput.value = '';
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

const viewImage = async (image: any) => {
    try {
        // fetch full media record for the detail modal (modal expects full media shape)
        const full = await fetchMediaById(image.id);
        selectedImage.value = full || image;
        showViewModal.value = true;
    } catch (err: any) {
        await showToast({ icon: 'error', title: 'Error', text: err.message || 'Failed to load image details' });
    }
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

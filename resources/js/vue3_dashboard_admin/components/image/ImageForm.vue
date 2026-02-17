<template>
    <ContentBox>
        <ContentBoxHeader>
            <template #title>
                <ContentBoxTitle
                    :title="mode === 'edit' ? 'Edit Image' : 'Add Image'"
                    :subtitle="
                        mode === 'edit'
                            ? 'Update image details and file'
                            : 'Upload a new image to the media library'
                    "
                />
            </template>
        </ContentBoxHeader>

        <ContentBoxBody>
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-2"
                            >Alt Text</label
                        >
                        <input
                            v-model="form.alt_text"
                            type="text"
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            placeholder="Describe the image..."
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-2"
                            >Gallery (Optional)</label
                        >
                        <select
                            v-model="form.gallery_id"
                            class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer"
                        >
                            <option value="">No Gallery</option>
                            <option
                                v-for="gallery in galleries"
                                :key="gallery.id"
                                :value="String(gallery.id)"
                            >
                                {{ gallery.title }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                        >Image File {{ mode === "create" ? "*" : "" }}</label
                    >

                    <div
                        v-if="showCropper"
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
                    >
                        <div
                            class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4"
                        >
                            <h3 class="text-lg font-semibold mb-4 text-center">
                                Crop Image
                            </h3>
                            <div class="mb-4">
                                <Cropper
                                    ref="cropperRef"
                                    :src="cropperImage"
                                    :stencil-props="{ aspectRatio: 4 / 3 }"
                                    :canvas="{ height: 600, width: 800 }"
                                />
                            </div>
                            <div class="flex justify-center gap-3">
                                <ActionButton
                                    type="button"
                                    variant="secondary"
                                    @click="cancelCrop"
                                    >Cancel</ActionButton
                                >
                                <ActionButton
                                    type="button"
                                    variant="primary"
                                    @click="applyCrop"
                                >
                                    <span class="material-symbols-outlined mr-2"
                                        >crop</span
                                    >
                                    Apply Crop
                                </ActionButton>
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors"
                    >
                        <input
                            type="file"
                            ref="imageInput"
                            @change="handleImageSelect"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                        />

                        <div
                            v-if="imagePreview || image?.url"
                            class="space-y-4"
                        >
                            <img
                                :src="imagePreview || image?.url"
                                alt="Preview"
                                class="max-w-full max-h-48 mx-auto rounded-lg shadow-md transform transition-transform duration-300 hover:scale-105 will-change-transform"
                            />
                            <div class="flex justify-center gap-2">
                                <ActionButton
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    @click="editImage"
                                >
                                    <span class="material-symbols-outlined mr-1"
                                        >crop</span
                                    >
                                    Crop
                                </ActionButton>
                                <ActionButton
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    @click="() => imageInput?.click()"
                                >
                                    Change Image
                                </ActionButton>
                                <Button
                                    v-if="imagePreview"
                                    type="button"
                                    variant="danger"
                                    size="sm"
                                    left-icon="close"
                                    @click="removeNewImage"
                                >
                                    {{
                                        mode === "edit"
                                            ? "Reset to Original"
                                            : "Remove"
                                    }}
                                </Button>
                            </div>
                        </div>

                        <div
                            v-else
                            @click="() => imageInput?.click()"
                            class="cursor-pointer"
                        >
                            <span
                                class="material-symbols-outlined text-gray-400 text-3xl"
                                >cloud_upload</span
                            >
                            <p class="mt-2 text-sm text-gray-600">
                                Click to upload an image
                            </p>
                            <p class="text-xs text-gray-400">
                                JPG, PNG, WebP up to 5MB
                            </p>
                            <p class="text-xs text-gray-400">
                                Will be cropped to 4:3 ratio
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-4 pt-6 border-t border-gray-200"
                >
                    <ActionButton variant="secondary" @click="$emit('cancel')"
                        >Cancel</ActionButton
                    >
                    <ActionButton
                        variant="primary"
                        type="submit"
                        :loading="isSaving"
                    >
                        {{ mode === "edit" ? "Save Changes" : "Upload Image" }}
                    </ActionButton>
                </div>
            </form>
        </ContentBoxBody>
    </ContentBox>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from "vue";
import ContentBox from "../ui/ContentBox.vue";
import ContentBoxHeader from "../ui/ContentBoxHeader.vue";
import ContentBoxTitle from "../ui/ContentBoxTitle.vue";
import ContentBoxBody from "../ui/ContentBoxBody.vue";
import Button from "../ui/Button.vue";
import ActionButton from "../ui/ActionButton.vue";
import { Cropper } from "vue-advanced-cropper";
import "vue-advanced-cropper/dist/style.css";
import { showToast } from "@/composables/useSweetAlert";

interface Props {
    mode: "create" | "edit";
    image?: any;
    galleries: any[];
}

const props = defineProps<Props>();
const emit = defineEmits(["cancel", "submit"]);

const resetLoading = () => {
    isSaving.value = false;
};
defineExpose({ resetLoading });

const isSaving = ref(false);
const imagePreview = ref("");
const imageFile = ref<File | null>(null);
const imageInput = ref<HTMLInputElement>();
const showCropper = ref(false);
const cropperImage = ref("");
const cropperRef = ref();

const originalImageSize = ref<{ width: number; height: number } | null>(null);
const cropSelection = ref<{
    x: number;
    y: number;
    width: number;
    height: number;
    canvasWidth: number;
    canvasHeight: number;
} | null>(null);

const form = reactive({
    alt_text: "",
    gallery_id: "",
});

watch(
    () => props.image,
    (newVal) => {
        if (props.mode === "edit" && newVal) {
            form.alt_text = newVal.alt_text || "";
            form.gallery_id = newVal.gallery_id
                ? String(newVal.gallery_id)
                : "";
        }
    },
    { immediate: true },
);

const handleImageSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        if (file.size > 5 * 1024 * 1024) {
            showToast({
                icon: "warning",
                title: "File too large",
                text: "File size must be less than 5MB.",
            });
            return;
        }
        if (!file.type.match("image/(jpeg|png|webp)")) {
            showToast({
                icon: "warning",
                title: "Invalid file",
                text: "Please select a JPG, PNG, or WebP image.",
            });
            return;
        }

        try {
            imagePreview.value = URL.createObjectURL(file);
        } catch (e) {
            const fr = new FileReader();
            fr.onload = (ev) => {
                imagePreview.value = ev.target?.result as string;
            };
            fr.readAsDataURL(file);
        }
        imageFile.value = file;

        const img = new Image();
        img.onload = () => {
            originalImageSize.value = {
                width: img.naturalWidth,
                height: img.naturalHeight,
            };
        };
        img.src = imagePreview.value || URL.createObjectURL(file);

        const reader = new FileReader();
        reader.onload = (e) => {
            cropperImage.value = e.target?.result as string;
            showCropper.value = true;
        };
        reader.readAsDataURL(file);
    }
};

const removeNewImage = () => {
    if (
        imagePreview.value &&
        imagePreview.value.startsWith &&
        imagePreview.value.startsWith("blob:")
    ) {
        try {
            URL.revokeObjectURL(imagePreview.value);
        } catch (e) {
            /* ignore */
        }
    }

    imagePreview.value = "";
    imageFile.value = null;
    if (imageInput.value) {
        imageInput.value.value = "";
    }
};

const editImage = () => {
    const src = imagePreview.value || props.image?.url;
    if (src) {
        cropperImage.value = src;
        showCropper.value = true;
    }
};

const applyCrop = () => {
    if (cropperRef.value) {
        const result = cropperRef.value.getResult();
        if (result && result.canvas) {
            const canvas = result.canvas;
            const coords = (result as any).coordinates || null;
            cropSelection.value = {
                x: coords ? coords.left : 0,
                y: coords ? coords.top : 0,
                width: coords ? coords.width : canvas.width,
                height: coords ? coords.height : canvas.height,
                canvasWidth: canvas.width,
                canvasHeight: canvas.height,
            };

            const targetW = 1200;
            const targetH = 900;
            const tmp = document.createElement("canvas");
            tmp.width = targetW;
            tmp.height = targetH;
            const ctx = tmp.getContext("2d");
            if (ctx) {
                ctx.drawImage(canvas, 0, 0, targetW, targetH);
                try {
                    imagePreview.value = tmp.toDataURL("image/jpeg", 0.9);
                } catch (e) {
                    imagePreview.value = canvas.toDataURL();
                }
                tmp.toBlob(
                    (blob: Blob | null) => {
                        if (blob) {
                            imageFile.value = new File(
                                [blob],
                                "cropped-image.jpg",
                                { type: "image/jpeg" },
                            );
                        }
                    },
                    "image/jpeg",
                    0.9,
                );
            } else {
                imagePreview.value = canvas.toDataURL();
                result.canvas.toBlob((blob: Blob) => {
                    if (blob) {
                        imageFile.value = new File(
                            [blob],
                            "cropped-image.jpg",
                            { type: "image/jpeg" },
                        );
                    }
                });
            }
        }
    }
    showCropper.value = false;
};

const cancelCrop = () => {
    showCropper.value = false;
    cropperImage.value = "";
};

const handleSubmit = async () => {
    if (props.mode === "create" && !imageFile.value) {
        await showToast({
            icon: "warning",
            title: "Validation Error",
            text: "Image file is required.",
        });
        return;
    }

    isSaving.value = true;

    try {
        const payload =
            typeof globalThis !== "undefined" && globalThis.FormData
                ? new globalThis.FormData()
                : new FormData();
        if (!(payload instanceof FormData)) {
            throw new Error("Cannot construct FormData in this environment");
        }

        let formDataToSend: FormData | null = null;
        if (
            payload instanceof FormData &&
            typeof (payload as any).append === "function"
        ) {
            formDataToSend = payload as FormData;
        } else {
            formDataToSend = new FormData();
        }

        if (form.alt_text) formDataToSend.append("alt_text", form.alt_text);
        if (form.gallery_id)
            formDataToSend.append("gallery_id", form.gallery_id);
        if (imageFile.value) formDataToSend.append("file", imageFile.value);

        if (cropSelection.value) {
            formDataToSend.append(
                "crop_canvas_width",
                String(cropSelection.value.canvasWidth),
            );
            formDataToSend.append(
                "crop_canvas_height",
                String(cropSelection.value.canvasHeight),
            );
            formDataToSend.append("crop_x", String(cropSelection.value.x));
            formDataToSend.append("crop_y", String(cropSelection.value.y));
            formDataToSend.append(
                "crop_width",
                String(cropSelection.value.width),
            );
            formDataToSend.append(
                "crop_height",
                String(cropSelection.value.height),
            );
        }
        if (originalImageSize.value) {
            formDataToSend.append(
                "orig_width",
                String(originalImageSize.value.width),
            );
            formDataToSend.append(
                "orig_height",
                String(originalImageSize.value.height),
            );
        }

        emit("submit", formDataToSend);
    } catch (error: unknown) {
        let message = "Failed to process image.";
        if (typeof error === "string") {
            message = error;
        } else if (error && typeof error === "object") {
            const errObj = error as any;
            if (typeof errObj.message === "string") {
                message = errObj.message;
            }
        }
        await showToast({ icon: "error", title: "Error", text: message });
    } finally {
        isSaving.value = false;
    }
};
</script>

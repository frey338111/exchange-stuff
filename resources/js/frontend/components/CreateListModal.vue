<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 px-4 py-6"
        @click.self="emit('close')"
    >
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-900">Create list</h2>

                <button type="button" class="text-gray-400 hover:text-gray-600" @click="emit('close')">
                    <span class="sr-only">Close</span>
                    &times;
                </button>
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="submitCreateList()">
                <div>
                    <label for="listing_product_name" class="block text-sm font-medium text-gray-700">Product name</label>
                    <input
                        id="listing_product_name"
                        v-model="form.product_name"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                </div>

                <div>
                    <label for="listing_product_description" class="block text-sm font-medium text-gray-700">Product description</label>
                    <textarea
                        id="listing_product_description"
                        v-model="form.product_description"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    ></textarea>
                </div>

                <div>
                    <label for="listing_parent_category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select
                        id="listing_parent_category"
                        v-model="form.parent_category_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :disabled="listOptionsLoading"
                        @change="selectParentCategory()"
                        required
                    >
                        <option value="">Select category</option>
                        <option v-for="category in topNavCategories" :key="category.category_id" :value="category.category_id">
                            {{ category.title }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="listing_child_category" class="block text-sm font-medium text-gray-700">Subcategory</label>
                    <select
                        id="listing_child_category"
                        v-model="form.category_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :disabled="listOptionsLoading || childCategories.length === 0"
                        required
                    >
                        <option value="">{{ childCategories.length === 0 ? 'No subcategories available' : 'Select subcategory' }}</option>
                        <option v-for="category in childCategories" :key="category.category_id" :value="category.category_id">
                            {{ category.title }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="listing_condition" class="block text-sm font-medium text-gray-700">Condition</label>
                    <select
                        id="listing_condition"
                        v-model="form.condition_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :disabled="listOptionsLoading"
                        required
                    >
                        <option value="">Select condition</option>
                        <option v-for="condition in productConditions" :key="condition.condition_id" :value="condition.condition_id">
                            {{ condition.condition_title }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="listing_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea
                        id="listing_notes"
                        v-model="form.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                </div>

                <div>
                    <label for="listing_images" class="block text-sm font-medium text-gray-700">List images</label>
                    <input
                        id="listing_images"
                        type="file"
                        class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-gray-700"
                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                        multiple
                        :disabled="submitting"
                        @change="selectImages"
                        required
                    >
                    <p class="mt-1 text-xs text-gray-500">Upload 1 to 5 images. JPG, PNG, GIF, or WebP. Max 2 MB each.</p>

                    <div v-if="selectedImages.length" class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div
                            v-for="(image, index) in selectedImages"
                            :key="image.previewUrl"
                            class="relative rounded-md border border-gray-200 p-2"
                        >
                            <img
                                :src="image.previewUrl"
                                :alt="image.file.name"
                                class="h-24 w-full rounded object-cover"
                            >
                            <label class="mt-2 flex items-center gap-2 text-xs font-medium text-gray-700">
                                <input
                                    v-model="mainImageIndex"
                                    type="radio"
                                    class="border-gray-300 text-gray-900 focus:ring-gray-900"
                                    :value="index"
                                >
                                Main image
                            </label>
                            <p class="mt-1 truncate text-xs text-gray-500">{{ image.file.name }}</p>
                            <button
                                type="button"
                                class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-sm leading-none text-gray-700 shadow hover:bg-gray-100"
                                :disabled="submitting"
                                @click="removeImage(index)"
                            >
                                <span class="sr-only">Remove image</span>
                                &times;
                            </button>
                        </div>
                    </div>
                </div>

                <p v-if="listOptionsError" class="text-sm text-red-600">
                    {{ listOptionsError }}
                </p>

                <p v-if="submitError" class="text-sm text-red-600">
                    {{ submitError }}
                </p>

                <p v-if="successMessage" class="text-sm text-green-700">
                    {{ successMessage }}
                </p>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        :disabled="submitting"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-70"
                        :disabled="submitting || listOptionsLoading"
                    >
                        <span class="inline-flex items-center gap-2">
                            <span
                                v-if="submitting"
                                class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                            ></span>
                            {{ submitting ? 'Creating...' : 'Create' }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useTopNav } from '../composables/useTopNav';

const emit = defineEmits(['close', 'created']);
const { categories: topNavCategories, error: topNavError, loadTopNav } = useTopNav();
const maxImageCount = 5;
const maxImageSize = 2 * 1024 * 1024;
const allowedImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
const allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

const listOptionsLoading = ref(false);
const listOptionsLoaded = ref(false);
const listOptionsError = ref('');
const productConditions = ref([]);
const submitting = ref(false);
const submitError = ref('');
const successMessage = ref('');
const selectedImages = ref([]);
const mainImageIndex = ref(0);
const form = ref({
    product_name: '',
    product_description: '',
    parent_category_id: '',
    category_id: '',
    condition_id: '',
    notes: '',
});
const childCategories = computed(() => {
    const parentCategory = topNavCategories.value.find((category) => String(category.category_id) === String(form.value.parent_category_id));

    return parentCategory?.children ?? [];
});

onMounted(() => {
    loadListOptions();
});

onBeforeUnmount(() => {
    clearSelectedImages();
});

async function loadListOptions() {
    if (listOptionsLoaded.value || listOptionsLoading.value) {
        return;
    }

    listOptionsLoading.value = true;
    listOptionsError.value = '';

    try {
        await loadTopNav();

        if (topNavError.value) {
            listOptionsError.value = topNavError.value;
        }

        const response = await window.axios.post('/graphql', {
            query: `{
                productConditions {
                    condition_id
                    condition_title
                }
            }`,
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            listOptionsError.value = firstError;

            return;
        }

        productConditions.value = response.data?.data?.productConditions ?? [];
        listOptionsLoaded.value = true;
    } catch (requestError) {
        listOptionsError.value = requestError.response?.data?.errors?.[0]?.message ?? 'The list options could not be loaded.';
    } finally {
        listOptionsLoading.value = false;
    }
}

function selectParentCategory() {
    form.value.category_id = '';
}

async function submitCreateList() {
    if (! validateSelectedImages()) {
        return;
    }

    submitting.value = true;
    submitError.value = '';
    successMessage.value = '';

    try {
        const payload = createMultipartCreateListPayload();
        const [response] = await Promise.all([
            window.axios.post('/graphql', payload, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }),
            delay(450),
        ]);

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            submitError.value = firstError;

            return;
        }

        const result = response.data?.data?.createList;

        if (! result?.success) {
            submitError.value = result?.error ?? result?.message ?? 'The list could not be created.';

            return;
        }

        successMessage.value = result.message;
        resetForm();
        emit('created', result.message);
    } catch (requestError) {
        submitError.value = requestError.response?.data?.errors?.[0]?.message ?? 'The list could not be created.';
    } finally {
        submitting.value = false;
    }
}

function resetForm() {
    form.value.product_name = '';
    form.value.product_description = '';
    form.value.parent_category_id = '';
    form.value.category_id = '';
    form.value.condition_id = '';
    form.value.notes = '';
    clearSelectedImages();
    mainImageIndex.value = 0;
    resetImageInput();
}

function selectImages(event) {
    submitError.value = '';
    successMessage.value = '';

    const files = Array.from(event.target.files ?? []);

    if (! files.length) {
        clearSelectedImages();

        return;
    }

    const validationError = getImageValidationError(files);

    if (validationError) {
        event.target.value = '';
        clearSelectedImages();
        submitError.value = validationError;

        return;
    }

    clearSelectedImages();
    selectedImages.value = files.map((file) => ({
        file,
        previewUrl: URL.createObjectURL(file),
    }));
    mainImageIndex.value = 0;
}

function removeImage(index) {
    const [removedImage] = selectedImages.value.splice(index, 1);

    if (removedImage) {
        URL.revokeObjectURL(removedImage.previewUrl);
    }

    if (selectedImages.value.length === 0) {
        mainImageIndex.value = 0;
        resetImageInput();

        return;
    }

    if (mainImageIndex.value === index) {
        mainImageIndex.value = 0;
    } else if (mainImageIndex.value > index) {
        mainImageIndex.value -= 1;
    }
}

function validateSelectedImages() {
    const files = selectedImages.value.map((image) => image.file);
    const validationError = getImageValidationError(files);

    if (validationError) {
        submitError.value = validationError;

        return false;
    }

    if (! selectedImages.value[mainImageIndex.value]) {
        submitError.value = 'Select a main image.';

        return false;
    }

    return true;
}

function getImageValidationError(files) {
    if (files.length === 0) {
        return 'Upload at least one list image.';
    }

    if (files.length > maxImageCount) {
        return `Upload no more than ${maxImageCount} images.`;
    }

    const invalidFile = files.find((file) => {
        const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

        return ! allowedImageExtensions.includes(extension) || ! allowedImageTypes.includes(file.type);
    });

    if (invalidFile) {
        return 'Images must be JPG, PNG, GIF, or WebP files.';
    }

    const oversizedFile = files.find((file) => file.size > maxImageSize);

    if (oversizedFile) {
        return 'Each image must be 2 MB or smaller.';
    }

    return '';
}

function createMultipartCreateListPayload() {
    const operations = {
        query: `mutation CreateList($input: CreateListInput!) {
            createList(input: $input) {
                success
                message
                error
            }
        }`,
        variables: {
            input: {
                product_name: form.value.product_name,
                product_description: form.value.product_description,
                category_id: form.value.category_id,
                condition_id: form.value.condition_id,
                notes: form.value.notes,
                images: selectedImages.value.map(() => null),
                main_image_index: mainImageIndex.value,
            },
        },
    };
    const map = {};
    const formData = new FormData();

    selectedImages.value.forEach((image, index) => {
        map[String(index)] = [`variables.input.images.${index}`];
    });

    formData.append('operations', JSON.stringify(operations));
    formData.append('map', JSON.stringify(map));
    selectedImages.value.forEach((image, index) => {
        formData.append(String(index), image.file);
    });

    return formData;
}

function clearSelectedImages() {
    selectedImages.value.forEach((image) => {
        URL.revokeObjectURL(image.previewUrl);
    });
    selectedImages.value = [];
}

function resetImageInput() {
    const imageInput = document.getElementById('listing_images');

    if (imageInput) {
        imageInput.value = '';
    }
}

function delay(milliseconds) {
    return new Promise((resolve) => {
        window.setTimeout(resolve, milliseconds);
    });
}
</script>

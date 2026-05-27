<template>
    <div>
        <div class="overflow-hidden rounded-md border border-gray-200 bg-gray-100">
            <img
                :src="currentGalleryImage.image_url"
                :alt="altText"
                class="aspect-[4/3] w-full object-cover"
            >
        </div>

        <div v-if="galleryImages.length > 1" class="mt-3 flex items-center justify-between gap-3">
            <button
                type="button"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="previousImage"
            >
                Previous
            </button>
            <p class="text-sm text-gray-600">
                {{ currentGalleryIndex + 1 }} / {{ galleryImages.length }}
            </p>
            <button
                type="button"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="nextImage"
            >
                Next
            </button>
        </div>

        <div v-if="galleryImages.length > 1" class="mt-4 flex gap-2 overflow-x-auto pb-1">
            <button
                v-for="(image, index) in galleryImages"
                :key="image.gallery_id"
                type="button"
                :class="thumbnailButtonClass(index)"
                @click="currentGalleryIndex = index"
            >
                <img
                    :src="image.image_url"
                    :alt="`${altText} image ${index + 1}`"
                    class="h-16 w-16 rounded object-cover"
                    loading="lazy"
                >
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    images: {
        type: Array,
        default: () => [],
    },
    altText: {
        type: String,
        default: 'Product image',
    },
});

const currentGalleryIndex = ref(0);
const productPlaceholderImage = '/images/product-placeholder.svg';
const galleryImages = computed(() => {
    return props.images.length > 0
        ? props.images
        : [{ gallery_id: 'placeholder', image_url: productPlaceholderImage, image_type: 'placeholder' }];
});
const currentGalleryImage = computed(() => galleryImages.value[currentGalleryIndex.value] ?? galleryImages.value[0]);

watch(() => props.images, () => {
    currentGalleryIndex.value = 0;
});

function previousImage() {
    currentGalleryIndex.value = currentGalleryIndex.value === 0
        ? galleryImages.value.length - 1
        : currentGalleryIndex.value - 1;
}

function nextImage() {
    currentGalleryIndex.value = currentGalleryIndex.value === galleryImages.value.length - 1
        ? 0
        : currentGalleryIndex.value + 1;
}

function thumbnailButtonClass(index) {
    return [
        'rounded-md border p-1',
        currentGalleryIndex.value === index ? 'border-gray-900' : 'border-gray-200 hover:border-gray-400',
    ];
}
</script>

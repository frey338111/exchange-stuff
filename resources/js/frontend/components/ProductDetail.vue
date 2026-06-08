<template>
    <section class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div v-if="loading" class="rounded-md border border-gray-200 bg-white p-8 shadow-sm">
            <div class="h-7 w-2/3 animate-pulse rounded bg-gray-100"></div>
            <div class="mt-6 space-y-3">
                <div class="h-4 animate-pulse rounded bg-gray-100"></div>
                <div class="h-4 w-5/6 animate-pulse rounded bg-gray-100"></div>
            </div>
        </div>

        <p v-else-if="error" class="rounded-md border border-red-200 bg-white p-6 text-sm text-red-600 shadow-sm">
            {{ error }}
        </p>

        <article v-else-if="product" class="rounded-md border border-gray-200 bg-white p-8 shadow-sm">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                <ProductGalleryCarousel
                    :images="product.gallery_images"
                    :alt-text="product.product_name"
                />

                <div>
                    <p class="text-sm font-medium text-gray-600">{{ product.category_title ?? 'Product' }}</p>
                    <h1 class="mt-2 text-3xl font-semibold text-gray-950">
                        {{ product.product_name }}
                    </h1>

                    <dl class="mt-8 space-y-3 text-sm">
                        <div class="grid grid-cols-[auto_minmax(0,1fr)] gap-x-2">
                            <dt class="shrink-0 font-medium text-gray-600">Condition:</dt>
                            <dd>
                                <div :class="conditionClass(product.condition_title)">
                                    {{ product.condition_title ?? '-' }}
                                </div>
                            </dd>
                            <dd class="col-span-2 mt-1 text-sm italic leading-5 text-gray-500">
                                {{ product.condition_description ?? '-' }}
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="shrink-0 font-medium text-gray-600">Reference No:</dt>
                            <dd class="font-bold text-gray-950">{{ product.sku ?? '-' }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="shrink-0 font-medium text-gray-600">Added:</dt>
                            <dd :class="dateBucketClass(product.date_added)">{{ dateBucketLabel(product.date_added) }}</dd>
                        </div>
                    </dl>

                    <ClaimForm :product="product" @claimed="markProductClaimed" />

                    <div class="mt-8 sm:hidden">
                        <div class="grid grid-cols-2 rounded-md bg-gray-100 p-1">
                            <button
                                type="button"
                                :class="mobileTabClass('description')"
                                @click="activeMobileSection = 'description'"
                            >
                                About
                            </button>
                            <button
                                type="button"
                                :class="mobileTabClass('notes')"
                                @click="activeMobileSection = 'notes'"
                            >
                                Notes
                            </button>
                        </div>

                        <div v-if="activeMobileSection === 'description'" class="mt-5">
                            <h2 class="text-base font-semibold text-gray-950">About this item</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">
                                {{ product.description || 'No product details provided.' }}
                            </p>
                        </div>

                        <div v-else class="mt-5">
                            <h2 class="text-base font-semibold text-gray-950">Seller notes</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">
                                {{ product.listing_notes || 'No seller notes provided.' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 hidden sm:block">
                        <h2 class="text-base font-semibold text-gray-950">About this item</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">
                            {{ product.description || 'No product details provided.' }}
                        </p>
                    </div>

                    <div v-if="product.listing_notes" class="mt-8 hidden sm:block">
                        <h2 class="text-base font-semibold text-gray-950">Seller notes</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">
                            {{ product.listing_notes }}
                        </p>
                    </div>
                </div>
            </div>
        </article>

        <p v-else class="rounded-md border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm">
            Product could not be found.
        </p>
    </section>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import ClaimForm from './product-detail/claimForm.vue';
import ProductGalleryCarousel from './product-detail/ProductGalleryCarousel.vue';

const props = defineProps({
    urlKey: {
        type: String,
        required: true,
    },
});

const product = ref(null);
const loading = ref(false);
const error = ref('');
const activeMobileSection = ref('description');

onMounted(() => {
    loadProduct();
});

watch(() => props.urlKey, () => {
    activeMobileSection.value = 'description';
    loadProduct();
});

async function loadProduct() {
    loading.value = true;
    error.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `query GetProduct($url_key: String!) {
                getProduct(url_key: $url_key) {
                    listing_id
                    listing_customer_id
                    product_id
                    product_name
                    url_key
                    sku
                    description
                    category_title
                    condition_title
                    condition_description
                    date_added
                    listing_notes
                    has_claim_request
                    gallery_images {
                        gallery_id
                        image_url
                        image_type
                    }
                }
            }`,
            variables: {
                url_key: props.urlKey,
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            error.value = firstError;

            return;
        }

        product.value = response.data?.data?.getProduct ?? null;
        activeMobileSection.value = 'description';
    } catch (requestError) {
        error.value = requestError.response?.data?.errors?.[0]?.message ?? 'Product could not be loaded.';
    } finally {
        loading.value = false;
    }
}

function markProductClaimed() {
    if (product.value) {
        product.value.has_claim_request = true;
    }
}

function mobileTabClass(section) {
    return [
        'rounded px-3 py-2 text-sm font-semibold',
        activeMobileSection.value === section ? 'bg-white text-gray-950 shadow-sm' : 'text-gray-600',
    ];
}

function conditionClass(conditionTitle) {
    const normalizedCondition = String(conditionTitle ?? '').toLowerCase();
    const conditionColors = [
        {
            match: ['new', 'excellent', 'mint'],
            className: 'font-bold text-emerald-700',
        },
        {
            match: ['good', 'very good'],
            className: 'font-bold text-sky-700',
        },
        {
            match: ['fair', 'used'],
            className: 'font-bold text-amber-700',
        },
        {
            match: ['poor', 'damaged', 'faulty'],
            className: 'font-bold text-red-700',
        },
    ];
    const matchedColor = conditionColors.find((color) => color.match.some((value) => normalizedCondition.includes(value)));

    return matchedColor?.className ?? 'font-bold text-gray-950';
}

function dateBucketLabel(value) {
    const labels = {
        '24h': 'In 24hrs',
        week: 'Last week',
        month: 'Last month',
        older: '1 month +',
    };

    return labels[dateBucketId(value)];
}

function dateBucketClass(value) {
    const classes = {
        '24h': 'font-bold text-emerald-700',
        week: 'font-bold text-sky-700',
        month: 'font-bold text-amber-700',
        older: 'font-bold text-gray-700',
    };

    return classes[dateBucketId(value)];
}

function dateBucketId(value) {
    if (! value) {
        return 'older';
    }

    const date = new Date(String(value).replace(' ', 'T'));
    const ageMs = Date.now() - date.getTime();
    const dayMs = 24 * 60 * 60 * 1000;

    if (ageMs <= dayMs) {
        return '24h';
    }

    if (ageMs <= 7 * dayMs) {
        return 'week';
    }

    if (ageMs <= 30 * dayMs) {
        return 'month';
    }

    return 'older';
}
</script>

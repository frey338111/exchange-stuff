<template>
    <section class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <picture v-if="category?.category_image">
                <source
                    v-if="category.category_image_mobile"
                    media="(max-width: 768px)"
                    :srcset="category.category_image_mobile"
                >
                <img
                    :src="category.category_image"
                    :alt="categoryTitle"
                    class="mb-6 aspect-[16/5] w-full rounded-lg object-cover shadow-sm"
                >
            </picture>
            <h1 class="text-2xl font-semibold text-gray-950">
                {{ categoryTitle }}
            </h1>
            <p v-if="category?.description" class="mt-3 max-w-3xl text-sm leading-6 text-gray-700">
                {{ category.description }}
            </p>
        </div>

        <div v-if="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="index in 6" :key="index" class="h-36 animate-pulse rounded-md bg-white/70"></div>
        </div>

        <p v-else-if="error" class="text-sm text-red-600">
            {{ error }}
        </p>

        <div v-else-if="category" class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <ProductFilter
                :total-count="filters.total"
                :show-child-category-filter="showChildCategoryFilter"
                :selected-category-id="selectedCategoryId"
                :selected-condition-id="selectedConditionId"
                :selected-date-added="selectedDateAdded"
                :child-category-filters="filters.child_categories"
                :category-filtered-count="filters.total"
                :condition-filtered-count="filters.total"
                :condition-filters="filters.conditions"
                :date-filters="filters.date_added"
                :filter-url="filterUrl"
                @set-filter="setFilter"
            />

            <div>
                <div class="mb-4 flex flex-col gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-700">
                        Showing {{ pageStart }}-{{ pageEnd }} of {{ filteredProducts.length }} products
                    </p>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="font-medium text-gray-700">Per page</span>
                        <a
                            v-for="option in perPageOptions"
                            :key="option"
                            :href="filterUrl({ perPage: option, page: 1 })"
                            :class="perPageLinkClass(option)"
                            @click.prevent="setFilter({ perPage: option, page: 1 })"
                        >
                            {{ option }}
                        </a>
                    </div>
                </div>

                <div v-if="products.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="product in products"
                        :key="`${product.listing_id}-${product.product_id}`"
                        class="rounded-md border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md"
                    >
                        <a :href="productHref(product)" class="block overflow-hidden rounded-md">
                            <img
                                :src="product.thumbnail_url ?? productPlaceholderImage"
                                :alt="product.product_name"
                                class="aspect-[4/3] w-full object-cover"
                                loading="lazy"
                            >

                            <div class="p-5">
                                <h2 class="text-base font-semibold text-gray-950">
                                    {{ product.product_name }}
                                </h2>

                                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <dt class="font-medium text-gray-600">Condition</dt>
                                        <dd :class="['mt-1', conditionClass(product.condition_title)]">
                                            {{ product.condition_title ?? '-' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Added in</dt>
                                        <dd :class="['mt-1', dateBucketClass(product.date_added)]">
                                            {{ dateBucketLabel(product.date_added) }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </a>
                    </article>
                </div>

                <p v-else class="rounded-md border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm">
                    No products match the selected filters.
                </p>

                <nav v-if="pagination.total_pages > 1" class="mt-6 flex flex-wrap items-center justify-center gap-2" aria-label="Product pagination">
                    <a
                        v-for="pageNumber in pagination.total_pages"
                        :key="pageNumber"
                        :href="filterUrl({ page: pageNumber })"
                        :class="pageLinkClass(pageNumber)"
                        @click.prevent="setFilter({ page: pageNumber })"
                    >
                        {{ pageNumber }}
                    </a>
                </nav>
            </div>
        </div>

        <p v-else class="text-sm text-gray-600">
            No live products found in this category.
        </p>
    </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import ProductFilter from './listing/ProductFilter.vue';

const props = defineProps({
    urlKey: {
        type: String,
        required: true,
    },
});

const category = ref(null);
const products = ref([]);
const filters = ref({
    total: 0,
    child_categories: [],
    conditions: [],
    date_added: [],
});
const pagination = ref({
    page: 1,
    per_page: 12,
    total: 0,
    total_pages: 1,
    from: 0,
    to: 0,
});
const loading = ref(false);
const error = ref('');
const selectedCategoryId = ref('');
const selectedConditionId = ref('');
const selectedDateAdded = ref('');
const page = ref(1);
const perPage = ref(12);
const perPageOptions = [12, 24, 36];
const productPlaceholderImage = '/images/product-placeholder.svg';
const categoryTitle = computed(() => category.value?.category_title ?? 'Category');
const showChildCategoryFilter = computed(() => Number(category.value?.parent_id) === 0 && (category.value?.children?.length ?? 0) > 0);
const filteredProducts = computed(() => ({ length: pagination.value.total }));
const pageStart = computed(() => pagination.value.from);
const pageEnd = computed(() => pagination.value.to);
const normalizedPage = computed(() => pagination.value.page);

onMounted(() => {
    syncFiltersFromUrl();
    loadCategoryProducts();
});

watch(() => props.urlKey, () => {
    selectedCategoryId.value = '';
    selectedConditionId.value = '';
    selectedDateAdded.value = '';
    page.value = 1;
    loadCategoryProducts();
});

async function loadCategoryProducts() {
    loading.value = true;
    error.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `query GetCategoryProducts(
                $url_key: String!
                $child_category_id: ID
                $condition_id: ID
                $date_added: String
                $page: Int
                $per_page: Int
            ) {
                getCategoryProducts(
                    url_key: $url_key
                    child_category_id: $child_category_id
                    condition_id: $condition_id
                    date_added: $date_added
                    page: $page
                    per_page: $per_page
                ) {
                    category {
                        category_id
                        category_title
                        url_key
                        description
                        category_image
                        category_image_mobile
                        parent_id
                        children {
                            category_id
                            category_title
                            url_key
                            category_image
                            category_image_mobile
                        }
                    }
                    products {
                        listing_id
                        product_id
                        product_name
                        url_key
                        category_id
                        thumbnail_url
                        condition_title
                        condition_id
                        date_added
                    }
                    filters {
                        total
                        child_categories {
                            id
                            title
                            count
                        }
                        conditions {
                            id
                            title
                            count
                        }
                        date_added {
                            id
                            title
                            count
                        }
                    }
                    pagination {
                        page
                        per_page
                        total
                        total_pages
                        from
                        to
                    }
                }
            }`,
            variables: {
                url_key: props.urlKey,
                child_category_id: selectedCategoryId.value || null,
                condition_id: selectedConditionId.value || null,
                date_added: selectedDateAdded.value || null,
                page: page.value,
                per_page: perPage.value,
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            error.value = firstError;

            return;
        }

        const payload = response.data?.data?.getCategoryProducts;

        category.value = payload?.category ?? null;
        products.value = payload?.products ?? [];
        filters.value = payload?.filters ?? filters.value;
        pagination.value = payload?.pagination ?? pagination.value;
        page.value = pagination.value.page;
        perPage.value = pagination.value.per_page;
    } catch (requestError) {
        error.value = requestError.response?.data?.errors?.[0]?.message ?? 'Products could not be loaded.';
    } finally {
        loading.value = false;
    }
}

function syncFiltersFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const requestedPerPage = Number(params.get('per_page') ?? 12);

    selectedCategoryId.value = params.get('child_category') ?? '';
    selectedConditionId.value = params.get('condition') ?? '';
    selectedDateAdded.value = params.get('date_added') ?? '';
    page.value = Math.max(1, Number(params.get('page') ?? 1));
    perPage.value = perPageOptions.includes(requestedPerPage) ? requestedPerPage : 12;
}

function setFilter(filters) {
    if (Object.hasOwn(filters, 'categoryId')) {
        selectedCategoryId.value = filters.categoryId;
    }

    if (Object.hasOwn(filters, 'conditionId')) {
        selectedConditionId.value = filters.conditionId;
    }

    if (Object.hasOwn(filters, 'dateAdded')) {
        selectedDateAdded.value = filters.dateAdded;
    }

    if (Object.hasOwn(filters, 'perPage')) {
        perPage.value = Number(filters.perPage);
    }

    if (Object.hasOwn(filters, 'page')) {
        page.value = Number(filters.page);
    }

    window.history.pushState({}, '', filterUrl());
    loadCategoryProducts();
}

function filterUrl(overrides = {}) {
    const params = new URLSearchParams();
    const categoryId = Object.hasOwn(overrides, 'categoryId') ? overrides.categoryId : selectedCategoryId.value;
    const conditionId = Object.hasOwn(overrides, 'conditionId') ? overrides.conditionId : selectedConditionId.value;
    const dateAdded = Object.hasOwn(overrides, 'dateAdded') ? overrides.dateAdded : selectedDateAdded.value;
    const requestedPerPage = Object.hasOwn(overrides, 'perPage') ? Number(overrides.perPage) : perPage.value;
    const requestedPage = Object.hasOwn(overrides, 'page') ? Number(overrides.page) : page.value;

    if (categoryId) {
        params.set('child_category', categoryId);
    }

    if (conditionId) {
        params.set('condition', conditionId);
    }

    if (dateAdded) {
        params.set('date_added', dateAdded);
    }

    if (requestedPerPage !== 12) {
        params.set('per_page', String(requestedPerPage));
    }

    if (requestedPage > 1) {
        params.set('page', String(requestedPage));
    }

    const queryString = params.toString();

    return `${window.location.pathname}${queryString ? `?${queryString}` : ''}`;
}

function perPageLinkClass(option) {
    return [
        'rounded-md px-2.5 py-1 text-sm font-medium',
        perPage.value === option ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    ];
}

function pageLinkClass(pageNumber) {
    return [
        'rounded-md px-3 py-2 text-sm font-medium',
        normalizedPage.value === pageNumber ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
    ];
}

function productHref(product) {
    return `/product/${product.url_key}`;
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

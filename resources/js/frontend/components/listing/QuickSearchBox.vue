<template>
    <div class="relative">
        <label for="top_nav_search" class="sr-only">Search stuff</label>
        <input
            id="top_nav_search"
            v-model="searchText"
            type="search"
            autocomplete="off"
            class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-9 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Search stuff"
            @focus="searchFocused = true"
            @blur="hideSearchResults()"
        >
        <svg class="pointer-events-none absolute right-3 top-2.5 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l3.131 3.131a.75.75 0 0 0 1.06-1.06l-3.131-3.131A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
        </svg>

        <div
            v-if="showSearchResults"
            class="absolute left-0 top-full z-30 mt-2 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg"
        >
            <div v-if="searchLoading" class="space-y-2 p-3">
                <div class="h-10 animate-pulse rounded-md bg-gray-100"></div>
                <div class="h-10 animate-pulse rounded-md bg-gray-100"></div>
            </div>

            <p v-else-if="searchError" class="p-3 text-sm text-red-600">
                {{ searchError }}
            </p>

            <div v-else-if="searchResults.length" class="max-h-96 overflow-y-auto py-1">
                <a
                    v-for="product in searchResults"
                    :key="product.url_key"
                    :href="productHref(product)"
                    class="flex items-center gap-3 px-3 py-2 text-sm text-gray-800 hover:bg-gray-50"
                    @mousedown.prevent="openProduct(product)"
                    @click.prevent="openProduct(product)"
                >
                    <img
                        :src="product.thumbnail_url ?? productPlaceholderImage"
                        :alt="product.product_name"
                        class="h-10 w-10 shrink-0 rounded-md object-cover"
                    >
                    <span class="min-w-0 truncate font-medium">{{ product.product_name }}</span>
                </a>
            </div>

            <p v-else class="p-3 text-sm text-gray-600">
                No products found.
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';

const productPlaceholderImage = '/images/product-placeholder.svg';
const searchText = ref('');
const searchResults = ref([]);
const searchLoading = ref(false);
const searchError = ref('');
const searchFocused = ref(false);
let searchTimer = null;
let searchRequestId = 0;

const trimmedSearchText = computed(() => searchText.value.trim());
const showSearchResults = computed(() => (
    searchFocused.value
    && trimmedSearchText.value.length > 3
    && (searchLoading.value || searchError.value || searchResults.value.length || searchText.value)
));

onUnmounted(() => {
    clearTimeout(searchTimer);
});

function productHref(product) {
    return `/product/${product.url_key}`;
}

function openProduct(product) {
    window.history.pushState({}, '', productHref(product));
    window.dispatchEvent(new CustomEvent('frontend:navigate'));
    searchFocused.value = false;
}

function hideSearchResults() {
    window.setTimeout(() => {
        searchFocused.value = false;
    }, 150);
}

async function quickSearch(searchItem, requestId) {
    searchLoading.value = true;
    searchError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `query QuickSearch($searchItem: String!) {
                quickSearch(searchItem: $searchItem) {
                    product_name
                    url_key
                    thumbnail_url
                }
            }`,
            variables: {
                searchItem,
            },
        });

        if (requestId !== searchRequestId) {
            return;
        }

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            searchError.value = firstError;
            searchResults.value = [];

            return;
        }

        searchResults.value = response.data?.data?.quickSearch ?? [];
    } catch (requestError) {
        if (requestId !== searchRequestId) {
            return;
        }

        searchError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Search could not be completed.';
        searchResults.value = [];
    } finally {
        if (requestId === searchRequestId) {
            searchLoading.value = false;
        }
    }
}

watch(trimmedSearchText, (value) => {
    clearTimeout(searchTimer);
    searchRequestId += 1;
    searchError.value = '';

    if (value.length <= 3) {
        searchLoading.value = false;
        searchResults.value = [];

        return;
    }

    searchLoading.value = true;
    const requestId = searchRequestId;
    searchTimer = window.setTimeout(() => {
        quickSearch(value, requestId);
    }, 500);
});
</script>

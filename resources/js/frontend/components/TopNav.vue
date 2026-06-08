<template>
    <header class="border-b border-gray-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="/" class="text-base font-semibold text-gray-900">
                    {{ appName }}
                </a>

                <div class="hidden items-center gap-1 md:flex">
                    <template v-if="loading">
                        <div class="h-9 w-24 animate-pulse rounded-md bg-gray-100"></div>
                        <div class="h-9 w-24 animate-pulse rounded-md bg-gray-100"></div>
                    </template>

                    <template v-else>
                        <div
                            v-for="category in categories"
                            :key="category.url_key"
                            class="relative"
                            @mouseenter="openDropdown = category.url_key"
                            @mouseleave="openDropdown = null"
                        >
                            <a
                                :href="categoryHref(category)"
                                class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-950"
                                @focus="openDropdown = category.url_key"
                            >
                                {{ category.title }}
                                <svg v-if="hasChildren(category)" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </a>

                            <div
                                v-if="hasChildren(category) && openDropdown === category.url_key"
                                class="absolute left-0 top-full z-20 min-w-52 rounded-md border border-gray-200 bg-white py-2 shadow-lg"
                            >
                                <a
                                    v-for="child in category.children"
                                    :key="child.url_key"
                                    :href="categoryHref(child)"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-950"
                                >
                                    {{ child.title }}
                                </a>
                            </div>
                        </div>
                    </template>
                </div>

                <QuickSearchBox class="hidden w-72 md:block" />
            </div>

            <div class="flex items-center gap-2">
                <CustomerSessionNav include-modal />

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md border border-gray-300 p-2 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 md:hidden"
                    :aria-expanded="mobileOpen"
                    aria-controls="mobile-navigation"
                    @click="mobileOpen = !mobileOpen"
                >
                    <span class="sr-only">Toggle navigation</span>
                    <svg v-if="!mobileOpen" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>
        </nav>

        <div
            v-if="mobileOpen"
            id="mobile-navigation"
            class="border-t border-gray-200 px-4 py-3 md:hidden"
        >
            <template v-if="loading">
                <div class="space-y-2">
                    <div class="h-10 animate-pulse rounded-md bg-gray-100"></div>
                    <div class="h-10 animate-pulse rounded-md bg-gray-100"></div>
                </div>
            </template>

            <template v-else>
                <CustomerSessionNav layout="mobile" @close-mobile="mobileOpen = false" />

                <div v-for="category in categories" :key="category.url_key" class="border-b border-gray-100 py-1 last:border-b-0">
                    <button
                        v-if="hasChildren(category)"
                        type="button"
                        class="flex w-full items-center justify-between rounded-md px-2 py-3 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
                        @click="toggleMobileCategory(category.url_key)"
                    >
                        <span>{{ category.title }}</span>
                        <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openMobileCategory === category.url_key }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <a
                        v-else
                        :href="categoryHref(category)"
                        class="block rounded-md px-2 py-3 text-sm font-medium text-gray-800 hover:bg-gray-50"
                    >
                        {{ category.title }}
                    </a>

                    <div v-if="hasChildren(category) && openMobileCategory === category.url_key" class="pb-2 ps-4">
                        <a
                            :href="categoryHref(category)"
                            class="block rounded-md px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-950"
                        >
                            View all {{ category.title }}
                        </a>
                        <a
                            v-for="child in category.children"
                            :key="child.url_key"
                            :href="categoryHref(child)"
                            class="block rounded-md px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-950"
                        >
                            {{ child.title }}
                        </a>
                    </div>
                </div>
            </template>

            <p v-if="error" class="px-2 py-3 text-sm text-red-600">
                {{ error }}
            </p>
        </div>

    </header>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import CustomerSessionNav from './CustomerSessionNav.vue';
import QuickSearchBox from './listing/QuickSearchBox.vue';
import { useTopNav } from '../composables/useTopNav';

const appName = 'Exchange Stuff';
const { categories, error, loading, loadTopNav } = useTopNav();
const mobileOpen = ref(false);
const openDropdown = ref(null);
const openMobileCategory = ref(null);

onMounted(() => {
    loadTopNav();
});

function categoryHref(category) {
    return `/category/${category.url_key}`;
}

function hasChildren(category) {
    return Array.isArray(category.children) && category.children.length > 0;
}

function toggleMobileCategory(urlKey) {
    openMobileCategory.value = openMobileCategory.value === urlKey ? null : urlKey;
}
</script>

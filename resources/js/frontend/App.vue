<template>
    <div
        class="flex min-h-screen flex-col text-gray-900"
        style="background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #ffffff 74%), linear-gradient(to right, #8fc9ec 0%, #b8dff3 42%, #b9dd87 100%);"
    >
        <TopNav />
        <div class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900" role="status">
            This is a demo website. No real items are listed here.
        </div>
        <main class="flex-1">
            <CustomerAccount v-if="currentPath === '/account' || currentPath.startsWith('/account/')" />
            <CategoryProducts v-else-if="currentPath.startsWith('/category/')" :url-key="categoryUrlKey" />
            <ProductDetail v-else-if="currentPath.startsWith('/product/')" :url-key="productUrlKey" />
            <CmsPage v-else :slug="cmsSlug" />
        </main>
        <footer class="border-t border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-4 text-center text-sm text-gray-700 sm:px-6 lg:px-8">
                copyright@ Hmh Network Solutions 2026
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import CategoryProducts from './components/CategoryProducts.vue';
import CmsPage from './components/CmsPage.vue';
import CustomerAccount from './components/CustomerAccount.vue';
import ProductDetail from './components/ProductDetail.vue';
import TopNav from './components/TopNav.vue';

const currentPath = ref(window.location.pathname);
const categoryUrlKey = computed(() => decodeURIComponent(currentPath.value.replace('/category/', '').split('/')[0] ?? ''));
const productUrlKey = computed(() => decodeURIComponent(currentPath.value.replace('/product/', '').split('/')[0] ?? ''));
const cmsSlug = computed(() => {
    const slug = currentPath.value.replace(/^\/+|\/+$/g, '');

    return slug === '' ? 'home' : decodeURIComponent(slug);
});

function syncPath() {
    currentPath.value = window.location.pathname;
}

onMounted(() => {
    window.addEventListener('popstate', syncPath);
    window.addEventListener('frontend:navigate', syncPath);
});

onUnmounted(() => {
    window.removeEventListener('popstate', syncPath);
    window.removeEventListener('frontend:navigate', syncPath);
});
</script>

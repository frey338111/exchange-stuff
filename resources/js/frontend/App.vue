<template>
    <div
        class="min-h-screen text-gray-900"
        style="background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #ffffff 70%), linear-gradient(to right, #dcfce7 0%, #dbeafe 100%);"
    >
        <TopNav />
        <main>
            <CustomerAccount v-if="currentPath === '/account' || currentPath.startsWith('/account/')" />
            <CategoryProducts v-else-if="currentPath.startsWith('/category/')" :url-key="categoryUrlKey" />
            <ProductDetail v-else-if="currentPath.startsWith('/product/')" :url-key="productUrlKey" />
            <CmsPage v-else :slug="cmsSlug" />
        </main>
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

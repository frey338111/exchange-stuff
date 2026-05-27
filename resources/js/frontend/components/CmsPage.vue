<template>
    <section class="mx-auto w-full max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div v-if="loading" class="rounded-md border border-gray-200 bg-white p-8 shadow-sm">
            <div class="h-8 w-2/3 animate-pulse rounded bg-gray-100"></div>
            <div class="mt-6 space-y-3">
                <div class="h-4 animate-pulse rounded bg-gray-100"></div>
                <div class="h-4 w-5/6 animate-pulse rounded bg-gray-100"></div>
                <div class="h-4 w-3/4 animate-pulse rounded bg-gray-100"></div>
            </div>
        </div>

        <article v-else-if="page" class="rounded-md border border-gray-200 bg-white p-8 shadow-sm">
            <h1 class="text-3xl font-semibold text-gray-950">
                {{ page.title }}
            </h1>
            <div class="cms-content mt-6 text-sm leading-7 text-gray-700" v-html="page.content"></div>
        </article>

        <div v-else class="rounded-md border border-gray-200 bg-white p-8 text-sm text-gray-600 shadow-sm">
            Page could not be found.
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    slug: {
        type: String,
        required: true,
    },
});

const page = ref(null);
const loading = ref(false);

onMounted(() => {
    loadPage();
});

watch(() => props.slug, () => {
    loadPage();
});

async function loadPage() {
    loading.value = true;
    page.value = null;

    try {
        const response = await window.axios.post('/graphql', {
            query: `query PageBySlug($slug: String!) {
                pageBySlug(slug: $slug) {
                    id
                    title
                    slug
                    content
                }
            }`,
            variables: {
                slug: props.slug,
            },
        });
        const firstError = response.data?.errors?.[0]?.message;

        page.value = firstError ? null : response.data?.data?.pageBySlug ?? null;
    } catch {
        page.value = null;
    } finally {
        loading.value = false;
    }
}
</script>

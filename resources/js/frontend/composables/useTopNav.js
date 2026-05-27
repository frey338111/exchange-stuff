import { ref } from 'vue';

const categories = ref([]);
const loading = ref(false);
const loaded = ref(false);
const error = ref('');
let topNavRequest = null;

async function loadTopNav() {
    if (loaded.value) {
        return categories.value;
    }

    if (topNavRequest) {
        return topNavRequest;
    }

    loading.value = true;
    error.value = '';
    topNavRequest = (async () => {
        const response = await window.axios.post('/graphql', {
            query: `{
                topNav {
                    category_id
                    title
                    url_key
                    children {
                        category_id
                        title
                        url_key
                    }
                }
            }`,
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            error.value = firstError;

            return categories.value;
        }

        categories.value = response.data?.data?.topNav ?? [];
        loaded.value = true;

        return categories.value;
    })();

    try {
        return await topNavRequest;
    } catch {
        error.value = 'Navigation could not be loaded.';

        return categories.value;
    } finally {
        loading.value = false;
        topNavRequest = null;
    }
}

export function useTopNav() {
    return {
        categories,
        error,
        loading,
        loaded,
        loadTopNav,
    };
}

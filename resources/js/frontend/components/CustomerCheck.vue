<template>
    <slot
        :customer="customer"
        :loading="loading"
        :loaded="loaded"
        :refresh="loadCurrentCustomer"
        :setCustomer="setCustomer"
        :clearCustomer="clearCustomer"
    />
</template>

<script>
import { ref } from 'vue';

const customer = ref(null);
const loading = ref(false);
const loaded = ref(false);
</script>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
    loadCurrentCustomer();
});

async function loadCurrentCustomer() {
    if (loaded.value || loading.value) {
        return customer.value;
    }

    loading.value = true;

    try {
        const response = await window.axios.post('/graphql', {
            query: `{
                currentCustomer {
                    customer_id
                    name
                    email
                }
            }`,
        });

        customer.value = response.data?.data?.currentCustomer ?? null;
        loaded.value = true;

        return customer.value;
    } finally {
        loading.value = false;
    }
}

function setCustomer(nextCustomer) {
    customer.value = nextCustomer;
    loaded.value = true;
}

function clearCustomer() {
    customer.value = null;
    loaded.value = true;
}
</script>

<template>
    <div class="max-w-5xl">
        <h2 class="text-lg font-semibold text-gray-950">Request send</h2>

        <div v-if="loading" class="mt-6 space-y-3">
            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
        </div>

        <p v-else-if="error" class="mt-6 text-sm text-red-600">
            {{ error }}
        </p>

        <div v-else-if="requests.length" class="mt-6 overflow-x-auto border-y border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead>
                    <tr>
                        <th class="py-3 pe-4 text-xs font-semibold uppercase text-gray-500">Request ID</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Listing</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Product</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Pickup</th>
                        <th class="py-3 ps-4 text-xs font-semibold uppercase text-gray-500">Status</th>
                        <th class="py-3 ps-4 text-right text-xs font-semibold uppercase text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="request in requests" :key="request.request_id">
                        <td class="py-4 pe-4 text-sm font-medium text-gray-950">#{{ request.request_id }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">#{{ request.listing_id }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ request.product?.product_name ?? '-' }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            {{ formatDate(request.pickup_date) }} <span v-if="request.timeslot">- {{ request.timeslot }}</span>
                        </td>
                        <td class="py-4 ps-4 text-sm text-gray-700">{{ requestStatusLabel(request.status) }}</td>
                        <td class="py-4 ps-4 text-right text-sm">
                            <a
                                :href="`/account/request-send/${request.request_id}`"
                                class="font-medium text-gray-900 hover:text-gray-600"
                                @click.prevent="$emit('view', request.request_id)"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-else class="mt-6 text-sm text-gray-600">
            No sent requests yet.
        </p>
    </div>
</template>

<script setup>
defineProps({
    requests: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
});

defineEmits(['view']);

function requestStatusLabel(status) {
    const labels = {
        0: 'Pending',
        1: 'Accepted',
        2: 'Rejected',
        3: 'Amended',
    };

    return labels[Number(status)] ?? 'Pending';
}

function formatDate(value) {
    if (! value) {
        return '-';
    }

    const date = new Date(String(value).replace(' ', 'T'));

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}
</script>

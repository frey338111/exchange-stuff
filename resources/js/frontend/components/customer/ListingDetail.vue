<template>
    <div class="max-w-5xl">
        <button
            type="button"
            class="text-sm font-medium text-gray-700 hover:text-gray-950"
            @click="$emit('back')"
        >
            Back to my listing
        </button>

        <div v-if="loading" class="mt-6 space-y-3">
            <div class="h-16 animate-pulse rounded-md bg-gray-100"></div>
            <div class="h-32 animate-pulse rounded-md bg-gray-100"></div>
        </div>

        <p v-else-if="error" class="mt-6 text-sm text-red-600">
            {{ error }}
        </p>

        <div v-else-if="listing" class="mt-6 space-y-8">
            <header class="border-y border-gray-200 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-950">Listing #{{ listing.listing_id }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ productNames(listing) }}</p>
                    </div>
                    <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        {{ listingStatusLabel(listing.status) }}
                    </span>
                </div>
                <p v-if="listing.notes" class="mt-4 whitespace-pre-line text-sm text-gray-700">
                    {{ listing.notes }}
                </p>
            </header>

            <section>
                <h3 class="text-base font-semibold text-gray-950">Product info</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div v-for="product in listing.products" :key="product.product_id" class="rounded-md border border-gray-200 bg-white p-4">
                        <p class="text-sm font-medium text-gray-600">{{ product.category?.category_title ?? 'Product' }}</p>
                        <h4 class="mt-1 text-lg font-semibold text-gray-950">{{ product.product_name }}</h4>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex gap-2">
                                <dt class="font-medium text-gray-600">Condition:</dt>
                                <dd class="text-gray-900">{{ product.productCondition?.condition_title ?? '-' }}</dd>
                            </div>
                            <div class="flex gap-2">
                                <dt class="font-medium text-gray-600">Description:</dt>
                                <dd class="text-gray-900">{{ product.description || '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <section>
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-gray-950">Requests</h3>
                    <p v-if="actionMessage" class="text-sm text-green-700">{{ actionMessage }}</p>
                </div>

                <div v-if="listing.claimRequests.length" class="mt-4 divide-y divide-gray-200 border-y border-gray-200">
                    <div v-for="request in listing.claimRequests" :key="request.request_id" class="py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-950">
                                    Request #{{ request.request_id }} from {{ request.customer?.name ?? 'Customer' }}
                                </p>
                                <p class="mt-1 text-sm text-gray-700">{{ request.product?.product_name ?? '-' }}</p>
                                <p v-if="request.pickup_date || request.timeslot" class="mt-1 text-sm text-gray-600">
                                    {{ formatDate(request.pickup_date) }} <span v-if="request.timeslot">- {{ request.timeslot }}</span>
                                </p>
                                <p v-if="request.notes" class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ request.notes }}</p>
                                <button
                                    v-if="request.messages?.length"
                                    type="button"
                                    class="mt-3 text-sm font-medium text-gray-900 hover:text-gray-600"
                                    @click="toggleConversation(request.request_id)"
                                >
                                    {{ conversationExpanded(request.request_id) ? 'Hide conversation' : 'Show conversation' }}
                                </button>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                    {{ requestStatusLabel(request.status) }}
                                </span>
                                <button
                                    v-if="canRespondToRequest(request)"
                                    type="button"
                                    class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                                    :disabled="approvingRequestId === Number(request.request_id)"
                                    @click="$emit('respond', request)"
                                >
                                    Respond
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="conversationExpanded(request.request_id)"
                            class="mt-4 space-y-3 rounded-md bg-gray-50 p-4"
                        >
                            <div
                                v-for="message in request.messages"
                                :key="message.id"
                                :class="[
                                    'flex',
                                    isSellerMessage(request, message) ? 'justify-end' : 'justify-start',
                                ]"
                            >
                                <div
                                    :class="[
                                        'max-w-[90%] rounded-md px-3 py-2 text-sm',
                                        isSellerMessage(request, message)
                                            ? 'bg-gray-100 text-gray-800'
                                            : 'bg-white text-gray-800 ring-1 ring-gray-200',
                                    ]"
                                >
                                    <p class="whitespace-pre-line">
                                        <span class="font-semibold">{{ messageSenderName(request, message) }}:</span>
                                        {{ message.message }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ formatDate(message.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <p v-else class="mt-4 text-sm text-gray-600">
                    No requests yet.
                </p>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    listing: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    actionMessage: {
        type: String,
        default: '',
    },
    approvingRequestId: {
        type: Number,
        default: null,
    },
});

defineEmits(['back', 'respond']);

const expandedConversations = ref({});

function productNames(listing) {
    return listing.products?.map((product) => product.product_name).join(', ') || '-';
}

function listingStatusLabel(status) {
    const labels = {
        0: 'Pending',
        1: 'Live',
        2: 'Rejected',
        3: 'Completed',
    };

    return labels[Number(status)] ?? 'Pending';
}

function requestStatusLabel(status) {
    const labels = {
        0: 'Pending',
        1: 'Accepted',
        2: 'Rejected',
        3: 'Amended',
    };

    return labels[Number(status)] ?? 'Pending';
}

function canRespondToRequest(request) {
    return Number(request.status) === 0;
}

function conversationExpanded(requestId) {
    return Boolean(expandedConversations.value[Number(requestId)]);
}

function toggleConversation(requestId) {
    const normalizedRequestId = Number(requestId);

    expandedConversations.value = {
        ...expandedConversations.value,
        [normalizedRequestId]: ! expandedConversations.value[normalizedRequestId],
    };
}

function isSellerMessage(request, message) {
    return Number(message.customer_id) !== Number(request.customer_id);
}

function messageSenderName(request, message) {
    if (isSellerMessage(request, message)) {
        return 'Me';
    }

    return message.customer?.name ?? 'Customer';
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

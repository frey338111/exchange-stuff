<template>
    <div class="max-w-5xl">
        <button
            type="button"
            class="text-sm font-medium text-gray-700 hover:text-gray-950"
            @click="$emit('back')"
        >
            Back to sent requests
        </button>

        <div v-if="loading" class="mt-6 space-y-3">
            <div class="h-16 animate-pulse rounded-md bg-gray-100"></div>
            <div class="h-32 animate-pulse rounded-md bg-gray-100"></div>
        </div>

        <p v-else-if="error" class="mt-6 text-sm text-red-600">
            {{ error }}
        </p>

        <div v-else-if="request" class="mt-6 space-y-8">
            <header class="border-y border-gray-200 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-950">Request #{{ request.request_id }}</h2>
                        <p class="mt-1 text-sm text-gray-600">Listing #{{ request.listing_id }}</p>
                    </div>
                    <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        {{ requestStatusLabel(request.status) }}
                    </span>
                </div>

                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="font-medium text-gray-600">Pickup</dt>
                        <dd class="mt-1 text-gray-950">
                            {{ formatDate(request.pickup_date) }} <span v-if="request.timeslot">- {{ request.timeslot }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">Notes</dt>
                        <dd class="mt-1 whitespace-pre-line text-gray-950">{{ request.notes || '-' }}</dd>
                    </div>
                </dl>
            </header>

            <section>
                <h3 class="text-base font-semibold text-gray-950">Product info</h3>
                <div v-if="request.product" class="mt-4 rounded-md border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-600">{{ request.product.category?.category_title ?? 'Product' }}</p>
                    <h4 class="mt-1 text-lg font-semibold text-gray-950">{{ request.product.product_name }}</h4>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex gap-2">
                            <dt class="font-medium text-gray-600">Condition:</dt>
                            <dd class="text-gray-900">{{ request.product.productCondition?.condition_title ?? '-' }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="font-medium text-gray-600">Description:</dt>
                            <dd class="text-gray-900">{{ request.product.description || '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section>
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-gray-950">Conversation</h3>
                    <button
                        v-if="canReply(request)"
                        type="button"
                        class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700"
                        @click="$emit('reply', request)"
                    >
                        Reply
                    </button>
                </div>
                <div class="mt-4 space-y-3 rounded-md bg-gray-50 p-4">
                    <div
                        v-if="! request.messages?.length"
                        class="text-sm text-gray-600"
                    >
                        No conversation yet.
                    </div>
                    <div
                        v-for="message in request.messages ?? []"
                        :key="message.id"
                        :class="[
                            'flex',
                            Number(message.customer_id) === Number(request.customer_id) ? 'justify-end' : 'justify-start',
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-[90%] rounded-md px-3 py-2 text-sm',
                                Number(message.customer_id) === Number(request.customer_id)
                                    ? 'bg-gray-100 text-gray-800'
                                    : 'bg-white text-gray-800 ring-1 ring-gray-200',
                            ]"
                        >
                            <p class="whitespace-pre-line">
                                <span class="font-semibold">{{ Number(message.customer_id) === Number(request.customer_id) ? 'Me' : (message.customer?.name ?? 'Owner') }}:</span>
                                {{ message.message }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ formatDate(message.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
defineProps({
    request: {
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
});

defineEmits(['back', 'reply']);

function requestStatusLabel(status) {
    const labels = {
        0: 'Pending',
        1: 'Accepted',
        2: 'Rejected',
        3: 'Amended',
    };

    return labels[Number(status)] ?? 'Pending';
}

function canReply(request) {
    return Number(request.status) === 3;
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

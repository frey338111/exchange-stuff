<template>
    <CustomerCheck v-slot="{ customer, loading, loaded }">
        <section class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-gray-950">My account</h1>
            </div>

            <div v-if="loading || ! loaded" class="grid gap-8 lg:grid-cols-[220px_minmax(0,1fr)]">
                <div class="h-36 animate-pulse rounded-md bg-white/60"></div>
                <div class="space-y-3">
                    <div class="h-6 w-48 animate-pulse rounded-md bg-gray-100"></div>
                    <div class="h-6 w-72 animate-pulse rounded-md bg-gray-100"></div>
                    <div class="h-6 w-56 animate-pulse rounded-md bg-gray-100"></div>
                </div>
            </div>

            <div v-else-if="customer" class="grid gap-8 lg:grid-cols-[220px_minmax(0,1fr)]">
                <nav class="border-gray-200 lg:border-r lg:pr-6">
                    <div class="grid gap-1">
                        <a
                            v-for="item in navItems"
                            :key="item.path"
                            :href="item.path"
                            :class="navLinkClass(item.path)"
                            @click.prevent="navigateTo(item.path)"
                        >
                            {{ item.label }}
                        </a>
                    </div>
                </nav>

                <section class="min-w-0">
                    <div v-if="activeSection === 'info'" class="max-w-2xl">
                        <h2 class="text-lg font-semibold text-gray-950">My info</h2>

                        <dl class="mt-6 divide-y divide-gray-200 border-y border-gray-200">
                            <div class="grid gap-1 py-4 sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-600">Customer ID</dt>
                                <dd class="text-sm text-gray-950 sm:col-span-2">{{ customer.customer_id }}</dd>
                            </div>
                            <div class="grid gap-1 py-4 sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-600">Name</dt>
                                <dd class="text-sm text-gray-950 sm:col-span-2">{{ customer.name }}</dd>
                            </div>
                            <div class="grid gap-1 py-4 sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-600">Email</dt>
                                <dd class="text-sm text-gray-950 sm:col-span-2">{{ customer.email }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div v-else-if="activeSection === 'listing'" class="max-w-5xl">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-lg font-semibold text-gray-950">My listing</h2>

                            <button
                                type="button"
                                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                                @click="openCreateListModal()"
                            >
                                Create list
                            </button>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <button
                                v-for="filter in listingStatusFilters"
                                :key="filter.value"
                                type="button"
                                :class="filterButtonClass(filter.value)"
                                @click="setListingStatusFilter(filter.value)"
                            >
                                {{ filter.label }}
                            </button>
                        </div>

                        <p v-if="listingSuccess" class="mt-4 text-sm text-green-700">
                            {{ listingSuccess }}
                        </p>

                        <div v-if="listingsLoading" class="mt-6 space-y-3">
                            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
                            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
                        </div>

                        <p v-else-if="listingsError" class="mt-6 text-sm text-red-600">
                            {{ listingsError }}
                        </p>

                        <div v-else-if="myListings.length" class="mt-6 overflow-x-auto border-y border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-left">
                                <thead>
                                    <tr>
                                        <th class="py-3 pe-4 text-xs font-semibold uppercase text-gray-500">Listing ID</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Product</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Created</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Notes</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Status</th>
                                        <th class="py-3 ps-4 text-right text-xs font-semibold uppercase text-gray-500">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="listing in myListings" :key="listing.listing_id">
                                        <td class="py-4 pe-4 text-sm font-medium text-gray-950">#{{ listing.listing_id }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ productNames(listing) }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ formatDate(listing.created_at) }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ listing.notes || '-' }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ listingStatusLabel(listing.status) }}</td>
                                        <td class="py-4 ps-4 text-right text-sm">
                                            <a
                                                :href="`/account/listing/${listing.listing_id}`"
                                                class="font-medium text-gray-900 hover:text-gray-600"
                                                @click.prevent="navigateTo(`/account/listing/${listing.listing_id}`)"
                                            >
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p v-else class="mt-6 text-sm text-gray-600">
                            No listings found.
                        </p>
                    </div>

                    <div v-else-if="activeSection === 'listingDetail'" class="max-w-5xl">
                        <button
                            type="button"
                            class="text-sm font-medium text-gray-700 hover:text-gray-950"
                            @click="navigateTo('/account/listing')"
                        >
                            Back to my listing
                        </button>

                        <div v-if="listingDetailLoading" class="mt-6 space-y-3">
                            <div class="h-16 animate-pulse rounded-md bg-gray-100"></div>
                            <div class="h-32 animate-pulse rounded-md bg-gray-100"></div>
                        </div>

                        <p v-else-if="listingDetailError" class="mt-6 text-sm text-red-600">
                            {{ listingDetailError }}
                        </p>

                        <div v-else-if="listingDetail" class="mt-6 space-y-8">
                            <header class="border-y border-gray-200 py-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-950">Listing #{{ listingDetail.listing_id }}</h2>
                                        <p class="mt-1 text-sm text-gray-600">{{ productNames(listingDetail) }}</p>
                                    </div>
                                    <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ listingStatusLabel(listingDetail.status) }}
                                    </span>
                                </div>
                                <p v-if="listingDetail.notes" class="mt-4 whitespace-pre-line text-sm text-gray-700">
                                    {{ listingDetail.notes }}
                                </p>
                            </header>

                            <section>
                                <h3 class="text-base font-semibold text-gray-950">Product info</h3>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <div v-for="product in listingDetail.products" :key="product.product_id" class="rounded-md border border-gray-200 bg-white p-4">
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
                                    <p v-if="requestActionMessage" class="text-sm text-green-700">{{ requestActionMessage }}</p>
                                </div>

                                <div v-if="listingDetail.claimRequests.length" class="mt-4 divide-y divide-gray-200 border-y border-gray-200">
                                    <div v-for="request in listingDetail.claimRequests" :key="request.request_id" class="py-4">
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
                                            </div>
                                            <div class="flex shrink-0 items-center gap-3">
                                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                    {{ requestStatusLabel(request.status) }}
                                                </span>
                                                <button
                                                    v-if="canApproveRequests"
                                                    type="button"
                                                    class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                                                    :disabled="approvingRequestId === Number(request.request_id)"
                                                    @click="approveRequest(request.request_id)"
                                                >
                                                    Approve
                                                </button>
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

                    <div v-else-if="activeSection === 'pickupAddresses'" class="max-w-5xl">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-lg font-semibold text-gray-950">My pickup address</h2>

                            <button
                                type="button"
                                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                                @click="openAddressModal()"
                            >
                                Add New
                            </button>
                        </div>

                        <p v-if="addressSuccess" class="mt-4 text-sm text-green-700">
                            {{ addressSuccess }}
                        </p>

                        <div v-if="addressesLoading" class="mt-6 space-y-3">
                            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
                            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
                        </div>

                        <p v-else-if="addressesError" class="mt-6 text-sm text-red-600">
                            {{ addressesError }}
                        </p>

                        <div v-else-if="pickupAddresses.length" class="mt-6 overflow-x-auto border-y border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-left">
                                <thead>
                                    <tr>
                                        <th class="py-3 pe-4 text-xs font-semibold uppercase text-gray-500">Name</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Address</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Phone</th>
                                        <th class="py-3 ps-4 text-right text-xs font-semibold uppercase text-gray-500">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="address in pickupAddresses" :key="address.pickup_address_id">
                                        <td class="py-4 pe-4 text-sm font-medium text-gray-950">{{ address.name || '-' }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ formatAddress(address) }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ address.phone || '-' }}</td>
                                        <td class="py-4 ps-4 text-right text-sm">
                                            <div class="flex justify-end gap-3">
                                                <button
                                                    type="button"
                                                    class="font-medium text-gray-900 hover:text-gray-600"
                                                    @click="openAddressModal(address)"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    class="font-medium text-red-700 hover:text-red-600 disabled:cursor-not-allowed disabled:text-red-300"
                                                    :disabled="deletingAddressId === Number(address.pickup_address_id)"
                                                    @click="deletePickupAddress(address.pickup_address_id)"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p v-else class="mt-6 text-sm text-gray-600">
                            No pickup addresses found.
                        </p>
                    </div>

                    <div v-else-if="activeSection === 'claimRequests'" class="max-w-5xl">
                        <h2 class="text-lg font-semibold text-gray-950">My Claim Requests</h2>

                        <div v-if="claimRequestsLoading" class="mt-6 space-y-3">
                            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
                            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
                        </div>

                        <p v-else-if="claimRequestsError" class="mt-6 text-sm text-red-600">
                            {{ claimRequestsError }}
                        </p>

                        <div v-else-if="myClaimRequests.length" class="mt-6 overflow-x-auto border-y border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-left">
                                <thead>
                                    <tr>
                                        <th class="py-3 pe-4 text-xs font-semibold uppercase text-gray-500">Request ID</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Listing</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Product</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500">Pickup</th>
                                        <th class="py-3 ps-4 text-xs font-semibold uppercase text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="request in myClaimRequests" :key="request.request_id">
                                        <td class="py-4 pe-4 text-sm font-medium text-gray-950">#{{ request.request_id }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">#{{ request.listing_id }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ request.product?.product_name ?? '-' }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">
                                            {{ formatDate(request.pickup_date) }} <span v-if="request.timeslot">- {{ request.timeslot }}</span>
                                        </td>
                                        <td class="py-4 ps-4 text-sm text-gray-700">{{ requestStatusLabel(request.status) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p v-else class="mt-6 text-sm text-gray-600">
                            No claim requests yet.
                        </p>
                    </div>
                </section>
            </div>

            <RedirectToLogin v-else />
        </section>

        <CreateListModal
            v-if="customer && createListOpen"
            @close="closeCreateListModal()"
            @created="handleListCreated"
        />

        <AddressForm
            v-if="customer && addressModalOpen"
            :address="selectedAddress"
            :country-options="countryOptions"
            :error="addressFormError"
            :saving="addressSaving"
            @close="closeAddressModal()"
            @save="saveAddress"
        />
    </CustomerCheck>
</template>

<script setup>
import { computed, h, onMounted, onUnmounted, ref, watch } from 'vue';
import CreateListModal from './CreateListModal.vue';
import CustomerCheck from './CustomerCheck.vue';
import AddressForm from './customer/AddressForm.vue';

const createListOpen = ref(false);
const currentPath = ref(window.location.pathname);
const myListings = ref([]);
const listingsLoading = ref(false);
const listingsLoaded = ref(false);
const listingsError = ref('');
const listingSuccess = ref('');
const listingStatusFilter = ref('');
const listingDetail = ref(null);
const listingDetailLoading = ref(false);
const listingDetailError = ref('');
const myClaimRequests = ref([]);
const claimRequestsLoading = ref(false);
const claimRequestsLoaded = ref(false);
const claimRequestsError = ref('');
const approvingRequestId = ref(null);
const requestActionMessage = ref('');
const pickupAddresses = ref([]);
const addressesLoading = ref(false);
const addressesLoaded = ref(false);
const addressesError = ref('');
const addressSuccess = ref('');
const addressModalOpen = ref(false);
const addressSaving = ref(false);
const addressFormError = ref('');
const deletingAddressId = ref(null);
const selectedAddress = ref(null);
const countryOptions = [
    { code: 'GB', name: 'United Kingdom' },
];

const navItems = [
    { label: 'My info', path: '/account/info' },
    { label: 'My listing', path: '/account/listing' },
    { label: 'My pickup address', path: '/account/pickup-address' },
    { label: 'My Claim Requests', path: '/account/claim-requests' },
];
const listingStatusFilters = [
    { label: 'All', value: '' },
    { label: 'Pending', value: 0 },
    { label: 'Live', value: 1 },
    { label: 'Rejected', value: 2 },
    { label: 'Requested', value: 3 },
    { label: 'Accepted', value: 4 },
];
const activeSection = computed(() => {
    if (currentPath.value.startsWith('/account/listing/')) {
        return 'listingDetail';
    }

    if (currentPath.value === '/account/listing') {
        return 'listing';
    }

    if (currentPath.value === '/account/claim-requests') {
        return 'claimRequests';
    }

    if (currentPath.value === '/account/pickup-address') {
        return 'pickupAddresses';
    }

    return 'info';
});
const activeListingId = computed(() => {
    const match = currentPath.value.match(/^\/account\/listing\/(\d+)/);

    return match ? Number(match[1]) : null;
});
const canApproveRequests = computed(() => {
    const requests = listingDetail.value?.claimRequests ?? [];

    return requests.length > 0 && requests.every((request) => Number(request.status) === 0);
});

function openCreateListModal() {
    createListOpen.value = true;
}

function closeCreateListModal() {
    createListOpen.value = false;
}

async function handleListCreated(message) {
    createListOpen.value = false;
    listingSuccess.value = message;
    listingsLoaded.value = false;
    await loadMyListings(true);
}

function navigateTo(path) {
    window.history.pushState({}, '', path);
    currentPath.value = path;
    window.dispatchEvent(new CustomEvent('frontend:navigate'));
}

function isActivePath(path) {
    if (path === '/account/info') {
        return currentPath.value === '/account' || currentPath.value === '/account/info';
    }

    if (path === '/account/listing') {
        return currentPath.value === '/account/listing' || currentPath.value.startsWith('/account/listing/');
    }

    return currentPath.value === path;
}

function navLinkClass(path) {
    return [
        'rounded-md px-3 py-2 text-sm font-medium',
        isActivePath(path)
            ? 'bg-gray-900 text-white'
            : 'text-gray-700 hover:bg-white hover:text-gray-950',
    ];
}

function filterButtonClass(value) {
    return [
        'rounded-md px-3 py-2 text-sm font-medium',
        listingStatusFilter.value === value
            ? 'bg-gray-900 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100',
    ];
}

function setListingStatusFilter(value) {
    listingStatusFilter.value = value;
    listingsLoaded.value = false;
    loadMyListings(true);
}

function syncPath() {
    currentPath.value = window.location.pathname;
}

async function loadMyListings(force = false) {
    if (listingsLoading.value || (! force && listingsLoaded.value)) {
        return;
    }

    listingsLoading.value = true;
    listingsError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `query GetMyListing($status: Int) {
                getMyListing(status: $status) {
                    listing_id
                    created_at
                    notes
                    status
                    products {
                        product_id
                        product_name
                    }
                }
            }`,
            variables: {
                status: listingStatusFilter.value === '' ? null : Number(listingStatusFilter.value),
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            listingsError.value = firstError;

            return;
        }

        myListings.value = response.data?.data?.getMyListing ?? [];
        listingsLoaded.value = true;
    } catch (requestError) {
        listingsError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Listings could not be loaded.';
    } finally {
        listingsLoading.value = false;
    }
}

async function loadListingDetail() {
    if (! activeListingId.value) {
        return;
    }

    listingDetailLoading.value = true;
    listingDetailError.value = '';
    requestActionMessage.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `query GetMyListingDetail($listing_id: Int!) {
                getMyListingDetail(listing_id: $listing_id) {
                    listing_id
                    created_at
                    notes
                    status
                    products {
                        product_id
                        product_name
                        description
                        category {
                            category_title
                        }
                        productCondition {
                            condition_title
                        }
                    }
                    claimRequests {
                        request_id
                        customer_id
                        listing_id
                        product_id
                        notes
                        pickup_date
                        timeslot
                        status
                        customer {
                            name
                            email
                        }
                        product {
                            product_name
                        }
                    }
                }
            }`,
            variables: {
                listing_id: activeListingId.value,
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            listingDetailError.value = firstError;

            return;
        }

        listingDetail.value = response.data?.data?.getMyListingDetail ?? null;
    } catch (requestError) {
        listingDetailError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Listing could not be loaded.';
    } finally {
        listingDetailLoading.value = false;
    }
}

async function loadMyClaimRequests(force = false) {
    if (claimRequestsLoading.value || (! force && claimRequestsLoaded.value)) {
        return;
    }

    claimRequestsLoading.value = true;
    claimRequestsError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `{
                getMyClaimRequests {
                    request_id
                    listing_id
                    product_id
                    pickup_date
                    timeslot
                    status
                    product {
                        product_name
                    }
                }
            }`,
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            claimRequestsError.value = firstError;

            return;
        }

        myClaimRequests.value = response.data?.data?.getMyClaimRequests ?? [];
        claimRequestsLoaded.value = true;
    } catch (requestError) {
        claimRequestsError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Claim requests could not be loaded.';
    } finally {
        claimRequestsLoading.value = false;
    }
}

async function approveRequest(requestId) {
    if (! window.confirm('Approve this claim request? Other pending requests for this listing will be rejected.')) {
        return;
    }

    approvingRequestId.value = Number(requestId);
    requestActionMessage.value = '';
    listingDetailError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `mutation ApproveClaimRequest($request_id: Int!) {
                approveClaimRequest(request_id: $request_id) {
                    success
                    message
                    claim_request {
                        request_id
                        status
                    }
                }
            }`,
            variables: {
                request_id: Number(requestId),
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            listingDetailError.value = firstError;

            return;
        }

        requestActionMessage.value = response.data?.data?.approveClaimRequest?.message ?? 'Claim request approved.';
        await loadListingDetail();
        claimRequestsLoaded.value = false;
    } catch (requestError) {
        listingDetailError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Claim request could not be approved.';
    } finally {
        approvingRequestId.value = null;
    }
}

async function loadPickupAddresses(force = false) {
    if (addressesLoading.value || (! force && addressesLoaded.value)) {
        return;
    }

    addressesLoading.value = true;
    addressesError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `{
                getMyAddress {
                    pickup_address_id
                    customer_id
                    name
                    phone
                    address_line_1
                    address_line_2
                    city
                    county
                    postcode
                    country
                }
            }`,
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            addressesError.value = firstError;

            return;
        }

        pickupAddresses.value = response.data?.data?.getMyAddress ?? [];
        addressesLoaded.value = true;
    } catch (requestError) {
        addressesError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Pickup addresses could not be loaded.';
    } finally {
        addressesLoading.value = false;
    }
}

function openAddressModal(address = null) {
    addressFormError.value = '';
    selectedAddress.value = address;
    addressModalOpen.value = true;
}

function closeAddressModal() {
    if (addressSaving.value) {
        return;
    }

    addressModalOpen.value = false;
    addressFormError.value = '';
    selectedAddress.value = null;
}

async function saveAddress(addressInput) {
    addressSaving.value = true;
    addressFormError.value = '';
    addressesError.value = '';
    addressSuccess.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `mutation AddAddress($input: AddAddressInput!) {
                addAddress(input: $input) {
                    success
                    message
                    address {
                        pickup_address_id
                    }
                }
            }`,
            variables: {
                input: addressInput,
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            addressFormError.value = firstError;

            return;
        }

        addressSuccess.value = response.data?.data?.addAddress?.message ?? 'Pickup address saved.';
        addressModalOpen.value = false;
        selectedAddress.value = null;
        addressesLoaded.value = false;
        await loadPickupAddresses(true);
    } catch (requestError) {
        addressFormError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Pickup address could not be saved.';
    } finally {
        addressSaving.value = false;
    }
}

async function deletePickupAddress(addressId) {
    if (! window.confirm('Delete this pickup address?')) {
        return;
    }

    deletingAddressId.value = Number(addressId);
    addressesError.value = '';
    addressSuccess.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `mutation DeleteAddress($pickup_address_id: Int!) {
                deleteAddress(pickup_address_id: $pickup_address_id) {
                    success
                    message
                }
            }`,
            variables: {
                pickup_address_id: Number(addressId),
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            addressesError.value = firstError;

            return;
        }

        addressSuccess.value = response.data?.data?.deleteAddress?.message ?? 'Pickup address deleted.';
        addressesLoaded.value = false;
        await loadPickupAddresses(true);
    } catch (requestError) {
        addressesError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Pickup address could not be deleted.';
    } finally {
        deletingAddressId.value = null;
    }
}

function productNames(listing) {
    return listing.products?.map((product) => product.product_name).join(', ') || '-';
}

function formatAddress(address) {
    return [
        address.address_line_1,
        address.address_line_2,
        address.city,
        address.county,
        address.postcode,
        formatCountry(address.country),
    ].filter(Boolean).join(', ');
}

function normalizeCountryCode(country) {
    if (! country) {
        return 'GB';
    }

    if (country === 'United Kingdom') {
        return 'GB';
    }

    const code = String(country).toUpperCase();

    return countryOptions.some((option) => option.code === code) ? code : 'GB';
}

function formatCountry(country) {
    const code = normalizeCountryCode(country);
    const countryOption = countryOptions.find((option) => option.code === code);

    return countryOption ? `${countryOption.name} (${countryOption.code})` : code;
}

function listingStatusLabel(status) {
    const labels = {
        0: 'Pending',
        1: 'Live',
        2: 'Rejected',
        3: 'Requested',
        4: 'Accepted',
    };

    return labels[Number(status)] ?? 'Pending';
}

function requestStatusLabel(status) {
    const labels = {
        0: 'Pending',
        1: 'Accepted',
        2: 'Rejected',
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

watch(activeSection, (section) => {
    if (section === 'listing') {
        loadMyListings();
    }

    if (section === 'listingDetail') {
        loadListingDetail();
    }

    if (section === 'claimRequests') {
        loadMyClaimRequests();
    }

    if (section === 'pickupAddresses') {
        loadPickupAddresses();
    }
}, { immediate: true });

watch(activeListingId, () => {
    if (activeSection.value === 'listingDetail') {
        loadListingDetail();
    }
});

onMounted(() => {
    if (currentPath.value === '/account') {
        window.history.replaceState({}, '', '/account/info');
        currentPath.value = '/account/info';
        window.dispatchEvent(new CustomEvent('frontend:navigate'));
    }

    window.addEventListener('popstate', syncPath);
    window.addEventListener('frontend:navigate', syncPath);
});

onUnmounted(() => {
    window.removeEventListener('popstate', syncPath);
    window.removeEventListener('frontend:navigate', syncPath);
});

const RedirectToLogin = {
    setup() {
        onMounted(() => {
            window.history.replaceState({}, '', '/login');
            window.dispatchEvent(new CustomEvent('frontend:navigate'));
        });

        return () => h('span', { class: 'sr-only' }, 'Redirecting to login');
    },
};
</script>

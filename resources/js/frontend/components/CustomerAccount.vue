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

                    <ListingDetail
                        v-else-if="activeSection === 'listingDetail'"
                        :listing="listingDetail"
                        :loading="listingDetailLoading"
                        :error="listingDetailError"
                        :action-message="requestActionMessage"
                        :approving-request-id="approvingRequestId"
                        @back="navigateTo('/account/listing')"
                        @respond="openResponseModal"
                    />

                    <MyAddress
                        v-else-if="activeSection === 'pickupAddresses'"
                        :addresses="pickupAddresses"
                        :loading="addressesLoading"
                        :error="addressesError"
                        :success="addressSuccess"
                        :deleting-address-id="deletingAddressId"
                        :country-options="countryOptions"
                        @add="openAddressModal()"
                        @edit="openAddressModal"
                        @delete="deletePickupAddress"
                    />

                    <ReceivedRequests
                        v-else-if="activeSection === 'claimRequests'"
                        :requests="myClaimRequests"
                        :loading="claimRequestsLoading"
                        :error="claimRequestsError"
                        @view="(listingId) => navigateTo(`/account/listing/${listingId}`)"
                    />

                    <SentClaimRequests
                        v-else-if="activeSection === 'sentClaimRequests'"
                        :requests="sentClaimRequests"
                        :loading="sentClaimRequestsLoading"
                        :error="sentClaimRequestsError"
                        @view="(requestId) => navigateTo(`/account/request-send/${requestId}`)"
                    />

                    <SentClaimRequestDetail
                        v-else-if="activeSection === 'sentClaimRequestDetail'"
                        :request="sentClaimRequestDetail"
                        :loading="sentClaimRequestDetailLoading"
                        :error="sentClaimRequestDetailError"
                        @back="navigateTo('/account/request-send')"
                        @reply="openReplyModal"
                    />
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

        <ApproveForm
            v-if="customer && responseModalOpen && selectedClaimRequest"
            :request="selectedClaimRequest"
            :addresses="pickupAddresses"
            :error="responseFormError"
            :saving="approvingRequestId === Number(selectedClaimRequest.request_id)"
            @close="closeResponseModal()"
            @send="processClaimRequest"
        />

        <ClaimRequestReplyForm
            v-if="customer && replyModalOpen && sentClaimRequestDetail"
            :request="sentClaimRequestDetail"
            :error="replyFormError"
            :saving="replySaving"
            @close="closeReplyModal()"
            @send="replyToAmendedClaimRequest"
        />
    </CustomerCheck>
</template>

<script setup>
import { computed, h, onMounted, onUnmounted, ref, watch } from 'vue';
import ClaimRequestReplyForm from './customer/ClaimRequestReplyForm.vue';
import CreateListModal from './CreateListModal.vue';
import CustomerCheck from './CustomerCheck.vue';
import AddressForm from './customer/AddressForm.vue';
import ApproveForm from './customer/approveForm.vue';
import ListingDetail from './customer/ListingDetail.vue';
import MyAddress from './customer/MyAddress.vue';
import SentClaimRequestDetail from './customer/SentClaimRequestDetail.vue';
import ReceivedRequests from './customer/ReceivedRequests.vue';
import SentClaimRequests from './customer/SentClaimRequests.vue';

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
const sentClaimRequests = ref([]);
const sentClaimRequestsLoading = ref(false);
const sentClaimRequestsLoaded = ref(false);
const sentClaimRequestsError = ref('');
const sentClaimRequestDetail = ref(null);
const sentClaimRequestDetailLoading = ref(false);
const sentClaimRequestDetailError = ref('');
const replyModalOpen = ref(false);
const replySaving = ref(false);
const replyFormError = ref('');
const approvingRequestId = ref(null);
const requestActionMessage = ref('');
const responseModalOpen = ref(false);
const responseFormError = ref('');
const selectedClaimRequest = ref(null);
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
    { label: 'Request Received ', path: '/account/claim-requests' },
    { label: 'Request send', path: '/account/request-send' },
];
const listingStatusFilters = [
    { label: 'All', value: '' },
    { label: 'Pending', value: 0 },
    { label: 'Live', value: 1 },
    { label: 'Rejected', value: 2 },
    { label: 'Completed', value: 3 },
];
const activeSection = computed(() => {
    if (currentPath.value.startsWith('/account/request-send/')) {
        return 'sentClaimRequestDetail';
    }

    if (currentPath.value.startsWith('/account/listing/')) {
        return 'listingDetail';
    }

    if (currentPath.value === '/account/listing') {
        return 'listing';
    }

    if (currentPath.value === '/account/claim-requests') {
        return 'claimRequests';
    }

    if (currentPath.value === '/account/request-send') {
        return 'sentClaimRequests';
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
const activeSentClaimRequestId = computed(() => {
    const match = currentPath.value.match(/^\/account\/request-send\/(\d+)/);

    return match ? Number(match[1]) : null;
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
                        messages {
                            id
                            customer_id
                            message
                            created_at
                            customer {
                                name
                            }
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
                        url_key
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

async function loadSentClaimRequests(force = false) {
    if (sentClaimRequestsLoading.value || (! force && sentClaimRequestsLoaded.value)) {
        return;
    }

    sentClaimRequestsLoading.value = true;
    sentClaimRequestsError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `{
                getMySentClaimRequests {
                    request_id
                    listing_id
                    product_id
                    pickup_date
                    timeslot
                    status
                    product {
                        product_name
                        url_key
                    }
                }
            }`,
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            sentClaimRequestsError.value = firstError;

            return;
        }

        sentClaimRequests.value = response.data?.data?.getMySentClaimRequests ?? [];
        sentClaimRequestsLoaded.value = true;
    } catch (requestError) {
        sentClaimRequestsError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Sent requests could not be loaded.';
    } finally {
        sentClaimRequestsLoading.value = false;
    }
}

async function loadSentClaimRequestDetail() {
    if (! activeSentClaimRequestId.value) {
        return;
    }

    sentClaimRequestDetailLoading.value = true;
    sentClaimRequestDetailError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `query GetMySentClaimRequestDetail($request_id: Int!) {
                getMySentClaimRequestDetail(request_id: $request_id) {
                    request_id
                    customer_id
                    listing_id
                    product_id
                    notes
                    pickup_date
                    timeslot
                    status
                    product {
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
                    messages {
                        id
                        customer_id
                        message
                        created_at
                        customer {
                            name
                        }
                    }
                }
            }`,
            variables: {
                request_id: activeSentClaimRequestId.value,
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            sentClaimRequestDetailError.value = firstError;

            return;
        }

        sentClaimRequestDetail.value = response.data?.data?.getMySentClaimRequestDetail ?? null;
    } catch (requestError) {
        sentClaimRequestDetailError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Sent request could not be loaded.';
    } finally {
        sentClaimRequestDetailLoading.value = false;
    }
}

function openReplyModal() {
    replyFormError.value = '';
    replyModalOpen.value = true;
}

function closeReplyModal() {
    if (replySaving.value) {
        return;
    }

    replyModalOpen.value = false;
    replyFormError.value = '';
}

async function replyToAmendedClaimRequest(input) {
    replySaving.value = true;
    replyFormError.value = '';
    sentClaimRequestDetailError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `mutation ReplyToAmendedClaimRequest($input: RespondToAmendedClaimRequestInput!) {
                respondToAmendedClaimRequest(input: $input) {
                    success
                    message
                    claim_request {
                        request_id
                        status
                    }
                }
            }`,
            variables: {
                input,
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            replyFormError.value = firstError;

            return;
        }

        replyModalOpen.value = false;
        sentClaimRequestsLoaded.value = false;
        await loadSentClaimRequestDetail();
    } catch (requestError) {
        replyFormError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Reply could not be sent.';
    } finally {
        replySaving.value = false;
    }
}

async function openResponseModal(request) {
    if (addressesLoading.value) {
        return;
    }

    listingDetailError.value = '';
    responseFormError.value = '';
    requestActionMessage.value = '';
    selectedClaimRequest.value = request;

    try {
        await loadPickupAddresses(true);
        responseModalOpen.value = true;
    } catch (requestError) {
        listingDetailError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Pickup addresses could not be loaded.';
        selectedClaimRequest.value = null;
    }
}

function closeResponseModal() {
    if (approvingRequestId.value !== null) {
        return;
    }

    responseModalOpen.value = false;
    responseFormError.value = '';
    selectedClaimRequest.value = null;
}

async function processClaimRequest(input) {
    approvingRequestId.value = Number(input.request_id);
    requestActionMessage.value = '';
    listingDetailError.value = '';
    responseFormError.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `mutation ProcessClaimRequest($input: ProcessClaimRequestInput!) {
                processClaimRequest(input: $input) {
                    success
                    message
                    claim_request {
                        request_id
                        status
                    }
                }
            }`,
            variables: {
                input,
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            responseFormError.value = firstError;

            return;
        }

        requestActionMessage.value = response.data?.data?.processClaimRequest?.message ?? 'Claim request processed.';
        responseModalOpen.value = false;
        selectedClaimRequest.value = null;
        await loadListingDetail();
        claimRequestsLoaded.value = false;
    } catch (requestError) {
        responseFormError.value = requestError.response?.data?.errors?.[0]?.message ?? 'Claim request could not be processed.';
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

function listingStatusLabel(status) {
    const labels = {
        0: 'Pending',
        1: 'Live',
        2: 'Rejected',
        3: 'Completed',
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

    if (section === 'sentClaimRequests') {
        loadSentClaimRequests();
    }

    if (section === 'sentClaimRequestDetail') {
        loadSentClaimRequestDetail();
    }

    if (section === 'pickupAddresses') {
        loadPickupAddresses();
    }
}, { immediate: true });

watch(activeSentClaimRequestId, () => {
    if (activeSection.value === 'sentClaimRequestDetail') {
        loadSentClaimRequestDetail();
    }
});

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

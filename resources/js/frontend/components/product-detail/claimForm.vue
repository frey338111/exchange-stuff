<template>
    <CustomerCheck v-slot="{ customer }">
        <div v-if="customer" class="mt-8 border-t border-gray-200 pt-6">
            <p
                v-if="isOwner(customer)"
                class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700"
            >
                You cannot claim your own item.
            </p>

            <p
                v-else-if="product.has_claim_request"
                class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800"
            >
                claim request is waiting for seller response
            </p>

            <button
                type="button"
                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-70"
                :disabled="claimDisabled(customer)"
                @click="openForm"
            >
                Claim this item
            </button>

            <form
                v-if="formOpen && ! claimDisabled(customer)"
                class="mt-5 space-y-4 rounded-md border border-gray-200 bg-gray-50 p-4"
                @submit.prevent="submitClaim(customer)"
            >
                <div>
                    <label for="claim_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea
                        id="claim_notes"
                        v-model="form.notes"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                </div>

                <div>
                    <label for="pickup_option" class="block text-sm font-medium text-gray-700">Pick up time</label>
                    <select
                        id="pickup_option"
                        v-model="form.pickupOption"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        @change="syncPickupOption"
                    >
                        <option value="seller">decide by seller</option>
                        <option value="preferred">my preferred time</option>
                    </select>
                </div>

                <div v-if="form.pickupOption === 'preferred'" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="pickup_date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input
                            id="pickup_date"
                            v-model="form.pickupDate"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                    </div>

                    <div>
                        <label for="pickup_slot" class="block text-sm font-medium text-gray-700">Time range</label>
                        <select
                            id="pickup_slot"
                            v-model="form.pickupSlot"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option
                                v-for="slot in pickupSlots"
                                :key="slot.value"
                                :value="slot.value"
                            >
                                {{ slot.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <p v-if="error" class="text-sm text-red-600">
                    {{ error }}
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-70"
                        :disabled="submitting"
                    >
                        {{ submitting ? 'Sending...' : 'Send' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-white"
                        :disabled="submitting"
                        @click="closeForm"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </CustomerCheck>
</template>

<script setup>
import { ref, watch } from 'vue';
import CustomerCheck from '../CustomerCheck.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['claimed']);

const formOpen = ref(false);
const submitting = ref(false);
const error = ref('');
const form = ref(defaultForm());
const pickupSlots = [
    { value: '7am-9am', label: '7am - 9am' },
    { value: '9am-12pm', label: '9am - 12pm' },
    { value: '12pm-3pm', label: '12pm to 3pm' },
    { value: '3pm-6pm', label: '3pm to 6pm' },
    { value: '6pm-9pm', label: '6pm to 9pm' },
];

watch(() => props.product.product_id, () => {
    resetForm();
});

function defaultForm() {
    return {
        notes: '',
        pickupOption: 'seller',
        pickupDate: '',
        pickupSlot: '7am-9am',
    };
}

function openForm() {
    formOpen.value = true;
    error.value = '';
}

function closeForm() {
    formOpen.value = false;
    error.value = '';
}

function resetForm() {
    formOpen.value = false;
    submitting.value = false;
    error.value = '';
    form.value = defaultForm();
}

function syncPickupOption() {
    if (form.value.pickupOption === 'seller') {
        form.value.pickupDate = '';
        form.value.pickupSlot = '7am-9am';
    }
}

function isOwner(customer) {
    return Number(customer.customer_id) === Number(props.product.listing_customer_id);
}

function claimDisabled(customer) {
    return props.product.has_claim_request || isOwner(customer);
}

async function submitClaim(customer) {
    if (claimDisabled(customer)) {
        return;
    }

    submitting.value = true;
    error.value = '';

    try {
        const response = await window.axios.post('/graphql', {
            query: `mutation ClaimItem($input: ClaimItemInput!) {
                claimItem(input: $input) {
                    success
                    message
                    claim_request {
                        request_id
                    }
                }
            }`,
            variables: {
                input: {
                    customer_id: Number(customer.customer_id),
                    listing_id: Number(props.product.listing_id),
                    product_id: Number(props.product.product_id),
                    notes: form.value.notes || null,
                    pickup_date: form.value.pickupOption === 'preferred' ? form.value.pickupDate : null,
                    pickup_slot: form.value.pickupOption === 'preferred' ? form.value.pickupSlot : null,
                },
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            error.value = firstError;

            return;
        }

        emit('claimed');
        closeForm();
    } catch (requestError) {
        error.value = requestError.response?.data?.errors?.[0]?.message ?? 'The claim request could not be sent.';
    } finally {
        submitting.value = false;
    }
}
</script>

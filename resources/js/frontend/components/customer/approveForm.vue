<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 px-4 py-6"
        @click.self="emitClose()"
    >
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Respond to claim request</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Request #{{ request?.request_id }} from {{ request?.customer?.name ?? 'Customer' }}
                    </p>
                </div>

                <button type="button" class="text-gray-400 hover:text-gray-600" @click="emitClose()">
                    <span class="sr-only">Close</span>
                    &times;
                </button>
            </div>

            <form class="mt-6 space-y-5" @submit.prevent="emitSend()">
                <fieldset>
                    <legend class="block text-sm font-medium text-gray-700">Response</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-3">
                        <label
                            v-for="option in actionOptions"
                            :key="option.value"
                            class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700"
                        >
                            <input
                                v-model="form.action"
                                type="radio"
                                class="border-gray-300 text-gray-900 focus:ring-gray-900"
                                :value="option.value"
                            >
                            {{ option.label }}
                        </label>
                    </div>
                </fieldset>

                <div>
                    <label for="claim_response_message" class="block text-sm font-medium text-gray-700">Additional message</label>
                    <textarea
                        id="claim_response_message"
                        v-model="form.message"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Leave collection instruction"
                    ></textarea>
                </div>

                <div v-if="addresses.length" class="space-y-3">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input
                            v-model="form.sendStoredAddress"
                            type="checkbox"
                            class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                        >
                        or send my stored address
                    </label>

                    <select
                        v-if="form.sendStoredAddress"
                        v-model="form.storedAddressId"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option
                            v-for="address in addresses"
                            :key="address.pickup_address_id"
                            :value="Number(address.pickup_address_id)"
                        >
                            {{ addressOptionLabel(address) }}
                        </option>
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input
                            v-model="form.proposePickupTime"
                            type="checkbox"
                            class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                        >
                        propose another pick up time
                    </label>

                    <div v-if="form.proposePickupTime" class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="response_pickup_date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input
                                id="response_pickup_date"
                                v-model="form.pickupDate"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                        </div>

                        <div>
                            <label for="response_pickup_slot" class="block text-sm font-medium text-gray-700">Time range</label>
                            <select
                                id="response_pickup_slot"
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
                </div>

                <p v-if="error" class="text-sm text-red-600">
                    {{ error }}
                </p>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        :disabled="saving"
                        @click="emitClose()"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                        :disabled="saving"
                    >
                        {{ saving ? 'Sending...' : 'Send' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    request: {
        type: Object,
        required: true,
    },
    addresses: {
        type: Array,
        default: () => [],
    },
    error: {
        type: String,
        default: '',
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'send']);

const actionOptions = [
    { value: 'accept', label: 'Accept' },
    { value: 'reject', label: 'Reject' },
    { value: 'amend', label: 'Amend' },
];
const pickupSlots = [
    { value: '7am-9am', label: '7am - 9am' },
    { value: '9am-12pm', label: '9am - 12pm' },
    { value: '12pm-3pm', label: '12pm to 3pm' },
    { value: '3pm-6pm', label: '3pm to 6pm' },
    { value: '6pm-9pm', label: '6pm to 9pm' },
];

const form = ref(defaultForm());

watch(() => props.request?.request_id, () => {
    form.value = defaultForm();
});

watch(() => props.addresses, (addresses) => {
    if (! form.value.storedAddressId && addresses.length) {
        form.value.storedAddressId = Number(addresses[0].pickup_address_id);
    }
}, { immediate: true });

function defaultForm() {
    return {
        action: 'accept',
        message: '',
        sendStoredAddress: false,
        storedAddressId: props.addresses[0] ? Number(props.addresses[0].pickup_address_id) : null,
        proposePickupTime: false,
        pickupDate: '',
        pickupSlot: '7am-9am',
    };
}

function emitClose() {
    if (props.saving) {
        return;
    }

    emit('close');
}

function emitSend() {
    emit('send', {
        request_id: Number(props.request.request_id),
        action: form.value.action,
        message: form.value.message || null,
        stored_address_id: form.value.sendStoredAddress ? Number(form.value.storedAddressId) : null,
        pickup_date: form.value.proposePickupTime ? form.value.pickupDate : null,
        pickup_slot: form.value.proposePickupTime ? form.value.pickupSlot : null,
    });
}

function addressOptionLabel(address) {
    return formatAddress(address).split(/\s+/).filter(Boolean).slice(0, 5).join(' ');
}

function formatAddress(address) {
    return [
        address.address_line_1,
        address.address_line_2,
        address.city,
        address.county,
        address.postcode,
        address.country,
    ].filter(Boolean).join(', ');
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 px-4 py-6"
        @click.self="emitClose()"
    >
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ form.pickup_address_id ? 'Edit pickup address' : 'Add pickup address' }}</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        For your privacy, you do not have to use your real name or exact address.
                    </p>
                </div>

                <button type="button" class="text-gray-400 hover:text-gray-600" @click="emitClose()">
                    <span class="sr-only">Close</span>
                    &times;
                </button>
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="emitSave()">
                <div>
                    <label for="pickup_address_name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input
                        id="pickup_address_name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="pickup_address_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input
                        id="pickup_address_phone"
                        v-model="form.phone"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="pickup_address_line_1" class="block text-sm font-medium text-gray-700">Address line 1</label>
                    <input
                        id="pickup_address_line_1"
                        v-model="form.address_line_1"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                </div>

                <div>
                    <label for="pickup_address_line_2" class="block text-sm font-medium text-gray-700">Address line 2</label>
                    <input
                        id="pickup_address_line_2"
                        v-model="form.address_line_2"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="pickup_address_city" class="block text-sm font-medium text-gray-700">City</label>
                        <input
                            id="pickup_address_city"
                            v-model="form.city"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                    </div>

                    <div>
                        <label for="pickup_address_county" class="block text-sm font-medium text-gray-700">County</label>
                        <input
                            id="pickup_address_county"
                            v-model="form.county"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="pickup_address_postcode" class="block text-sm font-medium text-gray-700">Postcode</label>
                        <input
                            id="pickup_address_postcode"
                            v-model="form.postcode"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                    </div>

                    <div>
                        <label for="pickup_address_country" class="block text-sm font-medium text-gray-700">Country</label>
                        <select
                            id="pickup_address_country"
                            v-model="form.country"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option
                                v-for="country in countryOptions"
                                :key="country.code"
                                :value="country.code"
                            >
                                {{ country.name }} ({{ country.code }})
                            </option>
                        </select>
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
                        {{ saving ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    address: {
        type: Object,
        default: null,
    },
    countryOptions: {
        type: Array,
        required: true,
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

const emit = defineEmits(['close', 'save']);

const emptyAddressForm = () => ({
    pickup_address_id: null,
    name: '',
    phone: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    county: '',
    postcode: '',
    country: 'GB',
});

const form = ref(emptyAddressForm());

function normalizeCountryCode(country) {
    if (! country) {
        return 'GB';
    }

    if (country === 'United Kingdom') {
        return 'GB';
    }

    const code = String(country).toUpperCase();

    return props.countryOptions.some((option) => option.code === code) ? code : 'GB';
}

function syncForm(address) {
    if (! address) {
        form.value = emptyAddressForm();

        return;
    }

    form.value = {
        pickup_address_id: Number(address.pickup_address_id),
        name: address.name ?? '',
        phone: address.phone ?? '',
        address_line_1: address.address_line_1 ?? '',
        address_line_2: address.address_line_2 ?? '',
        city: address.city ?? '',
        county: address.county ?? '',
        postcode: address.postcode ?? '',
        country: normalizeCountryCode(address.country),
    };
}

function emitClose() {
    if (props.saving) {
        return;
    }

    emit('close');
}

function emitSave() {
    emit('save', {
        pickup_address_id: form.value.pickup_address_id,
        name: form.value.name || null,
        phone: form.value.phone || null,
        address_line_1: form.value.address_line_1,
        address_line_2: form.value.address_line_2 || null,
        city: form.value.city,
        county: form.value.county || null,
        postcode: form.value.postcode,
        country: form.value.country || 'GB',
    });
}

watch(() => props.address, syncForm, { immediate: true });
</script>

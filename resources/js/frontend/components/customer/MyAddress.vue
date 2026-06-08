<template>
    <div class="max-w-5xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-gray-950">My pickup address</h2>

            <button
                type="button"
                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                @click="$emit('add')"
            >
                Add New
            </button>
        </div>

        <p v-if="success" class="mt-4 text-sm text-green-700">
            {{ success }}
        </p>

        <div v-if="loading" class="mt-6 space-y-3">
            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
            <div class="h-12 animate-pulse rounded-md bg-gray-100"></div>
        </div>

        <p v-else-if="error" class="mt-6 text-sm text-red-600">
            {{ error }}
        </p>

        <div v-else-if="addresses.length" class="mt-6 overflow-x-auto border-y border-gray-200">
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
                    <tr v-for="address in addresses" :key="address.pickup_address_id">
                        <td class="py-4 pe-4 text-sm font-medium text-gray-950">{{ address.name || '-' }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ formatAddress(address) }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ address.phone || '-' }}</td>
                        <td class="py-4 ps-4 text-right text-sm">
                            <div class="flex justify-end gap-3">
                                <button
                                    type="button"
                                    class="font-medium text-gray-900 hover:text-gray-600"
                                    @click="$emit('edit', address)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="font-medium text-red-700 hover:text-red-600 disabled:cursor-not-allowed disabled:text-red-300"
                                    :disabled="deletingAddressId === Number(address.pickup_address_id)"
                                    @click="$emit('delete', address.pickup_address_id)"
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
</template>

<script setup>
const props = defineProps({
    addresses: {
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
    success: {
        type: String,
        default: '',
    },
    deletingAddressId: {
        type: Number,
        default: null,
    },
    countryOptions: {
        type: Array,
        required: true,
    },
});

defineEmits(['add', 'edit', 'delete']);

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

    return props.countryOptions.some((option) => option.code === code) ? code : 'GB';
}

function formatCountry(country) {
    const code = normalizeCountryCode(country);
    const countryOption = props.countryOptions.find((option) => option.code === code);

    return countryOption ? `${countryOption.name} (${countryOption.code})` : code;
}
</script>

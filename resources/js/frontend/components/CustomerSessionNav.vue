<template>
    <CustomerCheck v-slot="{ customer, setCustomer, clearCustomer }">
        <div v-if="customer && isMobile" :class="containerClass(customer)">
            <span :class="nameClass">
                {{ customer.name }}
            </span>
            <a href="/account" :class="accountClass" @click="emit('close-mobile')">
                My account
            </a>
            <button
                type="button"
                :class="logoutClass"
                :disabled="logoutSubmitting"
                @click="logoutCustomer(clearCustomer)"
            >
                {{ logoutSubmitting ? 'Logging out...' : 'Logout' }}
            </button>
        </div>

        <div
            v-else-if="customer"
            class="relative hidden md:block"
            @mouseenter="accountMenuOpen = true"
            @mouseleave="accountMenuOpen = false"
            @focusin="accountMenuOpen = true"
            @focusout="closeAccountMenuOnFocusOut"
        >
            <button
                type="button"
                class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-950"
                :aria-expanded="accountMenuOpen"
                aria-haspopup="true"
                @click="accountMenuOpen = !accountMenuOpen"
            >
                <span>{{ customer.name }}</span>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                v-if="accountMenuOpen"
                class="absolute right-0 top-full z-30 min-w-44 rounded-md border border-gray-200 bg-white py-2 shadow-lg"
            >
                <a href="/account" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-950">
                    My account
                </a>
                <button
                    type="button"
                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-950 disabled:cursor-not-allowed disabled:opacity-70"
                    :disabled="logoutSubmitting"
                    @click="logoutCustomer(clearCustomer)"
                >
                    {{ logoutSubmitting ? 'Logging out...' : 'Logout' }}
                </button>
            </div>
        </div>

        <div v-else :class="containerClass(customer)">
            <a
                href="/login"
                :class="loginClass"
                @click.prevent="openAuthForm('login')"
            >
                Login
            </a>
            <a
                href="/register"
                :class="registerClass"
                @click.prevent="openAuthForm('register')"
            >
                Register
            </a>
        </div>

        <div
            v-if="includeModal && authMode"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 px-4 py-6"
            @click.self="closeAuthForm()"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ authMode === 'register' ? 'Create account' : 'Login' }}
                        </h2>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="closeAuthForm()">
                        <span class="sr-only">Close</span>
                        &times;
                    </button>
                </div>

                <form class="mt-6 space-y-4" @submit.prevent="submitAuthForm(setCustomer, clearCustomer)">
                    <div v-if="authMode === 'register'">
                        <label for="customer_name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input
                            id="customer_name"
                            v-model="authForm.name"
                            type="text"
                            autocomplete="name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                    </div>

                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input
                            id="customer_email"
                            v-model="authForm.email"
                            type="email"
                            autocomplete="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                    </div>

                    <div>
                        <label for="customer_password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input
                            id="customer_password"
                            v-model="authForm.password"
                            type="password"
                            :autocomplete="authMode === 'register' ? 'new-password' : 'current-password'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                    </div>

                    <div v-if="authMode === 'register'">
                        <label for="customer_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
                        <input
                            id="customer_password_confirmation"
                            v-model="authForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                    </div>

                    <p v-if="authError" class="text-sm text-red-600">
                        {{ authError }}
                    </p>

                    <p v-if="authSuccess" class="text-sm text-green-700">
                        {{ authSuccess }}
                    </p>

                    <button
                        type="submit"
                        class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-70"
                        :disabled="authSubmitting"
                    >
                        {{ authSubmitting ? 'Please wait...' : authMode === 'register' ? 'Register' : 'Login' }}
                    </button>
                </form>
            </div>
        </div>
    </CustomerCheck>
</template>

<script>
import { ref } from 'vue';

const authMode = ref(null);
const authSubmitting = ref(false);
const authError = ref('');
const authSuccess = ref('');
const logoutSubmitting = ref(false);
const accountMenuOpen = ref(false);
const authForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
</script>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import CustomerCheck from './CustomerCheck.vue';

const props = defineProps({
    layout: {
        type: String,
        default: 'desktop',
        validator: (value) => ['desktop', 'mobile'].includes(value),
    },
    includeModal: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close-mobile']);

const isMobile = computed(() => props.layout === 'mobile');
const containerClass = (customer) => customer
    ? isMobile.value
        ? 'mb-3 flex items-center justify-between gap-3 rounded-md border border-gray-200 px-3 py-2'
        : 'hidden items-center gap-3 md:flex'
    : isMobile.value
        ? 'mb-3 grid grid-cols-2 gap-2'
        : 'hidden items-center gap-2 md:flex';
const nameClass = computed(() => isMobile.value
    ? 'truncate text-sm font-medium text-gray-800'
    : 'text-sm font-medium text-gray-700');
const accountClass = computed(() => isMobile.value
    ? 'shrink-0 text-sm font-medium text-gray-700 hover:text-gray-950'
    : 'rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-950');
const logoutClass = computed(() => isMobile.value
    ? 'shrink-0 text-sm font-medium text-gray-700 hover:text-gray-950 disabled:cursor-not-allowed disabled:opacity-70'
    : 'rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-950 disabled:cursor-not-allowed disabled:opacity-70');
const loginClass = computed(() => isMobile.value
    ? 'rounded-md border border-gray-200 px-3 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50'
    : 'rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-950');
const registerClass = computed(() => isMobile.value
    ? 'rounded-md bg-gray-900 px-3 py-2 text-center text-sm font-medium text-white hover:bg-gray-700'
    : 'rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700');

onMounted(() => {
    syncAuthFormWithPath();
    window.addEventListener('popstate', syncAuthFormWithPath);
});

onUnmounted(() => {
    window.removeEventListener('popstate', syncAuthFormWithPath);
});

function openAuthForm(mode) {
    authMode.value = mode;
    authError.value = '';
    authSuccess.value = '';
    emit('close-mobile');
    window.history.pushState({}, '', mode === 'register' ? '/register' : '/login');
}

function closeAuthForm() {
    authMode.value = null;
    authError.value = '';
    authSuccess.value = '';
    window.history.pushState({}, '', '/');
}

function closeAccountMenuOnFocusOut(event) {
    if (! event.currentTarget.contains(event.relatedTarget)) {
        accountMenuOpen.value = false;
    }
}

async function submitAuthForm(setCustomer, clearCustomer) {
    authSubmitting.value = true;
    authError.value = '';
    authSuccess.value = '';

    const mutationName = authMode.value === 'register' ? 'registerCustomer' : 'loginCustomer';
    const inputType = authMode.value === 'register' ? 'RegisterCustomerInput' : 'LoginCustomerInput';
    const input = {
        email: authForm.value.email,
        password: authForm.value.password,
    };

    if (authMode.value === 'register') {
        input.name = authForm.value.name;
        input.password_confirmation = authForm.value.password_confirmation;
    }

    try {
        const response = await window.axios.post('/graphql', {
            query: `mutation ${mutationName}($input: ${inputType}!) {
                ${mutationName}(input: $input) {
                    customer {
                        customer_id
                        name
                        email
                    }
                }
            }`,
            variables: {
                input,
            },
        });

        const firstError = response.data?.errors?.[0]?.message;

        if (firstError) {
            authError.value = firstError;

            return;
        }

        if (authMode.value === 'login') {
            setCustomer(response.data?.data?.loginCustomer?.customer ?? null);
        } else {
            clearCustomer();
        }

        authMode.value = null;
        authSuccess.value = '';
        authForm.value.name = '';
        authForm.value.email = '';
        authForm.value.password = '';
        authForm.value.password_confirmation = '';
        window.history.pushState({}, '', '/');
        window.dispatchEvent(new CustomEvent('frontend:navigate'));
    } catch (requestError) {
        authError.value = requestError.response?.data?.errors?.[0]?.message ?? 'The request could not be completed.';
    } finally {
        authSubmitting.value = false;
    }
}

async function logoutCustomer(clearCustomer) {
    logoutSubmitting.value = true;
    authError.value = '';
    authSuccess.value = '';

    try {
        await window.axios.post('/graphql', {
            query: `mutation {
                logoutCustomer
            }`,
        });

        clearCustomer();
        emit('close-mobile');
    } finally {
        logoutSubmitting.value = false;
    }
}

function syncAuthFormWithPath() {
    if (window.location.pathname === '/register') {
        authMode.value = 'register';
    } else if (window.location.pathname === '/login') {
        authMode.value = 'login';
    } else {
        authMode.value = null;
    }
}
</script>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Product Conditions') }}
        </h2>
    </x-slot>

    <div
        class="py-8"
        x-data="{
            editing: false,
            formAction: @js(route('product-conditions.store')),
            form: {
                condition_title: '',
                description: '',
                value_adjustment: '',
            },
            createProductCondition() {
                this.editing = false;
                this.formAction = @js(route('product-conditions.store'));
                this.form = {
                    condition_title: '',
                    description: '',
                    value_adjustment: '',
                };
                this.$dispatch('open-modal', 'product-condition-form');
            },
            editProductCondition(productCondition) {
                this.editing = true;
                this.formAction = productCondition.update_url;
                this.form = {
                    condition_title: productCondition.condition_title,
                    description: productCondition.description,
                    value_adjustment: productCondition.value_adjustment,
                };
                this.$dispatch('open-modal', 'product-condition-form');
            },
        }"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/40 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Product condition list</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Manage condition labels and value adjustments.
                        </p>
                    </div>

                    <x-primary-button type="button" x-on:click="createProductCondition()">
                        {{ __('Add Product Condition') }}
                    </x-primary-button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Value Adjustment</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($productConditions as $productCondition)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $productCondition->condition_title }}
                                    </td>
                                    <td class="max-w-lg px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $productCondition->description }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $productCondition->value_adjustment }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        @php
                                            $productConditionPayload = [
                                                'condition_id' => $productCondition->condition_id,
                                                'condition_title' => $productCondition->condition_title,
                                                'description' => $productCondition->description,
                                                'value_adjustment' => $productCondition->value_adjustment,
                                                'update_url' => route('product-conditions.update', $productCondition),
                                            ];
                                        @endphp

                                        <button
                                            type="button"
                                            class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            data-product-condition="{{ json_encode($productConditionPayload) }}"
                                            x-on:click="editProductCondition(JSON.parse($el.dataset.productCondition))"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No product conditions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-modal name="product-condition-form" maxWidth="2xl" focusable>
                <form method="POST" x-bind:action="formAction" class="p-6">
                    @csrf

                    <template x-if="editing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="editing ? 'Edit product condition' : 'Add product condition'"></h3>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" x-on:click="$dispatch('close-modal', 'product-condition-form')">
                            <span class="sr-only">Close</span>
                            &times;
                        </button>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="condition_title" :value="__('Title')" />
                            <x-text-input id="condition_title" name="condition_title" type="text" class="mt-1 block w-full" x-model="form.condition_title" required />
                            <x-input-error :messages="$errors->get('condition_title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="value_adjustment" :value="__('Value Adjustment')" />
                            <x-text-input id="value_adjustment" name="value_adjustment" type="number" min="0" max="1" step="0.01" class="mt-1 block w-full" x-model="form.value_adjustment" required />
                            <x-input-error :messages="$errors->get('value_adjustment')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                x-model="form.description"
                                required
                            ></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'product-condition-form')">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                        <x-primary-button>
                            {{ __('Save') }}
                        </x-primary-button>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>
</x-app-layout>

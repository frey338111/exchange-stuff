<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Categories') }}
        </h2>
    </x-slot>

    <div
        class="py-8"
        x-data="{
            open: false,
            editing: false,
            categoryId: null,
            formAction: @js(route('categories.store')),
            form: {
                category_title: '',
                url_key: '',
                parent_id: '0',
                base_point: '',
                meta_tag: '',
                description: '',
                category_image: '',
                status: true,
            },
            createCategory() {
                this.editing = false;
                this.categoryId = null;
                this.formAction = @js(route('categories.store'));
                this.form = {
                    category_title: '',
                    url_key: '',
                    parent_id: '0',
                    base_point: '',
                    meta_tag: '',
                    description: '',
                    category_image: '',
                    status: true,
                };
                this.$dispatch('open-modal', 'category-form');
            },
            editCategory(category) {
                this.editing = true;
                this.categoryId = category.category_id;
                this.formAction = category.update_url;
                this.form = {
                    category_title: category.category_title,
                    url_key: category.url_key,
                    parent_id: String(category.parent_id),
                    base_point: category.base_point ?? '',
                    meta_tag: category.meta_tag,
                    description: category.description ?? '',
                    category_image: category.category_image ?? '',
                    status: Boolean(category.status),
                };
                this.$dispatch('open-modal', 'category-form');
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
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Category list</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Manage top-level and child categories.
                        </p>
                    </div>

                    <x-primary-button type="button" x-on:click="createCategory()">
                        {{ __('Add Category') }}
                    </x-primary-button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">URL Key</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Parent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Base Point</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Meta Tag</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $category->category_title }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        @if ($category->category_image)
                                            <img
                                                src="{{ Storage::disk('public')->url($category->category_image) }}"
                                                alt="{{ $category->category_title }}"
                                                class="h-12 w-16 rounded-md object-cover"
                                            >
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $category->url_key }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        @if ($category->parent_id === 0)
                                            Top level
                                        @else
                                            {{ $categories->firstWhere('category_id', $category->parent_id)?->category_title ?? 'Unknown' }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $category->base_point ?? '-' }}
                                    </td>
                                    <td class="max-w-xs truncate px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $category->meta_tag }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $category->status ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ $category->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        @php
                                            $categoryPayload = [
                                                'category_id' => $category->category_id,
                                                'category_title' => $category->category_title,
                                                'url_key' => $category->url_key,
                                                'parent_id' => $category->parent_id,
                                                'base_point' => $category->base_point,
                                                'meta_tag' => $category->meta_tag,
                                                'description' => $category->description,
                                                'category_image' => $category->category_image,
                                                'status' => $category->status,
                                                'update_url' => route('categories.update', $category),
                                            ];
                                        @endphp

                                        <button
                                            type="button"
                                            class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            data-category="{{ json_encode($categoryPayload) }}"
                                            x-on:click="editCategory(JSON.parse($el.dataset.category))"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No categories found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-modal name="category-form" maxWidth="2xl" focusable>
                <form method="POST" x-bind:action="formAction" enctype="multipart/form-data" class="p-6">
                    @csrf

                    <template x-if="editing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="editing ? 'Edit category' : 'Add category'"></h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Parent options only show top-level categories.
                            </p>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" x-on:click="$dispatch('close-modal', 'category-form')">
                            <span class="sr-only">Close</span>
                            &times;
                        </button>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="category_title" :value="__('Title')" />
                            <x-text-input id="category_title" name="category_title" type="text" class="mt-1 block w-full" x-model="form.category_title" required />
                            <x-input-error :messages="$errors->get('category_title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="url_key" :value="__('URL Key')" />
                            <x-text-input id="url_key" name="url_key" type="text" class="mt-1 block w-full" x-model="form.url_key" />
                            <x-input-error :messages="$errors->get('url_key')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="parent_id" :value="__('Parent Category')" />
                            <select
                                id="parent_id"
                                name="parent_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                x-model="form.parent_id"
                                required
                            >
                                <option value="0">Top level</option>
                                @foreach ($parentCategories as $parentCategory)
                                    <option value="{{ $parentCategory->category_id }}" x-bind:disabled="categoryId === {{ $parentCategory->category_id }}">
                                        {{ $parentCategory->category_title }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="base_point" :value="__('Base Point')" />
                            <x-text-input id="base_point" name="base_point" type="number" min="10" max="100" step="10" class="mt-1 block w-full" x-model="form.base_point" />
                            <x-input-error :messages="$errors->get('base_point')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="meta_tag" :value="__('Meta Tag')" />
                            <x-text-input id="meta_tag" name="meta_tag" type="text" class="mt-1 block w-full" x-model="form.meta_tag" required />
                            <x-input-error :messages="$errors->get('meta_tag')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                x-model="form.description"
                            ></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="category_image" :value="__('Category Image')" />
                            <input
                                id="category_image"
                                name="category_image"
                                type="file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm file:me-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-200"
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                JPG, JPEG, PNG, GIF or WebP. Max 2MB.
                            </p>
                            <template x-if="editing && form.category_image">
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Current image:') }} <span x-text="form.category_image"></span>
                                </p>
                            </template>
                            <x-input-error :messages="$errors->get('category_image')" class="mt-2" />
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input
                                type="checkbox"
                                name="status"
                                value="1"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                x-model="form.status"
                            >
                            Active
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'category-form')">
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

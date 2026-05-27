<div>
    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Title') }}</label>
    <input
        id="title"
        type="text"
        name="title"
        value="{{ old('title', $page?->title) }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
        required
    >
    @error('title')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Slug') }}</label>
    <input
        id="slug"
        type="text"
        name="slug"
        value="{{ old('slug', $page?->slug) }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
        placeholder="{{ __('Leave blank to generate from title') }}"
    >
    @error('slug')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Content') }}</label>
    <textarea
        id="content"
        name="content"
        rows="14"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
    >{{ old('content', $page?->content) }}</textarea>
    @error('content')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
    <input
        type="checkbox"
        name="published"
        value="1"
        class="rounded border-gray-300"
        @checked(old('published', $page?->published))
    >
    {{ __('Publish page') }}
</label>

<div class="flex items-center gap-3">
    <x-primary-button type="submit">
        {{ $submitLabel }}
    </x-primary-button>

    <a href="{{ route('dashboard.pages.index') }}" class="text-sm text-gray-600 hover:underline dark:text-gray-300">
        {{ __('Cancel') }}
    </a>
</div>

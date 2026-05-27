<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ __('Pages') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Manage static pages and page content.') }}
                </p>
            </div>

            <a
                href="{{ route('dashboard.pages.create') }}"
                class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-gray-100 dark:text-gray-900"
            >
                {{ __('Add Page') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/40 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('All Pages') }}</h3>
                </div>

                <div class="p-6">
                    @if ($pages->isEmpty())
                        <div class="rounded-md border border-dashed border-gray-300 bg-gray-50 p-8 text-center dark:border-gray-700 dark:bg-gray-900">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('No pages available yet.') }}</p>
                        </div>
                    @else
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($pages as $page)
                                <article class="rounded-md border border-gray-200 bg-gray-50 p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                    <div class="flex items-start justify-between gap-3">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $page->title }}</h4>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $page->published ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-200' }}">
                                            {{ $page->published ? __('Published') : __('Draft') }}
                                        </span>
                                    </div>

                                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ __('Slug:') }}</span> {{ $page->slug }}
                                    </p>

                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('Created') }} {{ optional($page->created_at)->format('Y-m-d H:i') }}
                                    </p>

                                    <div class="mt-4 flex items-center gap-4">
                                        <a
                                            href="{{ route('dashboard.pages.edit', $page) }}"
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            {{ __('Edit') }}
                                        </a>

                                        <form
                                            action="{{ route('dashboard.pages.destroy', $page) }}"
                                            method="POST"
                                            onsubmit="return confirm('Delete this page? This action cannot be undone.');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $pages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
